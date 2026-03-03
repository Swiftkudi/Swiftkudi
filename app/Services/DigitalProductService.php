<?php

namespace App\Services;

use App\Models\DigitalProduct;
use App\Models\DigitalProductOrder;
use App\Models\DigitalProductReview;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletLedger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DigitalProductService
{
    protected $platformFeePercent = 10; // 10% platform fee
    protected $commissionPercent = 0; // Referral commission

    public function createProduct(array $data, int $userId): DigitalProduct
    {
        $product = new DigitalProduct();
        $product->user_id = $userId;
        $product->title = $data['title'];
        $product->description = $data['description'];
        $product->price = $data['price'];
        $product->sale_price = $data['sale_price'] ?? null;
        $product->category_id = $data['category_id'] ?? null;
        $product->tags = $data['tags'] ?? [];
        $product->license_type = $data['license_type'] ?? 1;
        $product->version = $data['version'] ?? 1;
        $product->changelog = $data['changelog'] ?? null;
        $product->requirements = $data['requirements'] ?? null;
        $product->is_free = $data['is_free'] ?? false;
        $product->is_active = true;

        if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
            $product->thumbnail = $this->uploadFile($data['thumbnail'], 'thumbnails');
        }

        if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
            $product->file_path = $this->uploadFile($data['file'], 'products');
            $product->file_size = $data['file']->getSize();
            $product->file_type = $data['file']->getMimeType();
        }

        $product->save();

        return $product;
    }

    public function updateProduct(DigitalProduct $product, array $data): DigitalProduct
    {
        $product->fill($data);

        if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
            if ($product->thumbnail) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $product->thumbnail = $this->uploadFile($data['thumbnail'], 'thumbnails');
        }

        if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
            if ($product->file_path) {
                Storage::disk('public')->delete($product->file_path);
            }
            $product->file_path = $this->uploadFile($data['file'], 'products');
            $product->file_size = $data['file']->getSize();
            $product->file_type = $data['file']->getMimeType();
        }

        $product->save();

        return $product;
    }

    public function purchaseProduct(DigitalProduct $product, int $buyerId): DigitalProductOrder
    {
        return DB::transaction(function () use ($product, $buyerId) {
            $buyer = \App\Models\User::findOrFail($buyerId);
            $wallet = $buyer->wallet ?? Wallet::firstOrCreate(
                ['user_id' => $buyer->id],
                [
                    'withdrawable_balance' => 0,
                    'promo_credit_balance' => 0,
                    'total_earned' => 0,
                    'total_spent' => 0,
                    'pending_balance' => 0,
                    'escrow_balance' => 0,
                ]
            );

            $amount = $product->is_free ? 0 : $product->current_price;
            $platformFee = $amount > 0 ? ($amount * $this->platformFeePercent / 100) : 0;
            $sellerEarnings = $amount - $platformFee;

            // Create order
            $order = new DigitalProductOrder();
            $order->product_id = $product->id;
            $order->buyer_id = $buyerId;
            $order->amount = $amount;
            $order->platform_fee = $platformFee;
            $order->seller_earnings = $sellerEarnings;
            $order->license_type = $this->getLicenseTypeName($product->license_type);
            $order->max_downloads = $this->getMaxDownloads($product->license_type);
            $order->status = $product->is_free ? 'completed' : 'pending';
            $order->save();

            if (!$product->is_free) {
                // Deduct from buyer wallet
                // Check total balance (withdrawable + promo credit)
                if ($wallet->total_balance < $amount) {
                    throw new \Exception('Insufficient balance');
                }

                // Deduct from withdrawable balance first, then promo credit
                $remaining = (float) $amount;
                $deductFromWithdrawable = min((float) $wallet->withdrawable_balance, $remaining);

                if ($deductFromWithdrawable > 0) {
                    $wallet->deductWithdrawable(
                        $deductFromWithdrawable,
                        'digital_product_purchase',
                        "Purchase: {$product->title}"
                    );
                    $remaining -= $deductFromWithdrawable;
                }

                if ($remaining > 0) {
                    $deducted = $wallet->deductPromoCredit(
                        $remaining,
                        'digital_product_purchase',
                        "Purchase: {$product->title}"
                    );

                    if (!$deducted) {
                        throw new \Exception('Insufficient balance');
                    }
                }

                // Record transaction
                Transaction::create([
                    'user_id' => $buyerId,
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => "Purchase: {$product->title}",
                    'reference' => $order->order_number,
                    'status' => 'completed',
                ]);

                // Add to seller wallet (escrow - will release when download completes)
                $seller = $product->user;
                $sellerWallet = $seller->wallet ?? Wallet::firstOrCreate(
                    ['user_id' => $seller->id],
                    [
                        'withdrawable_balance' => 0,
                        'promo_credit_balance' => 0,
                        'total_earned' => 0,
                        'total_spent' => 0,
                        'pending_balance' => 0,
                        'escrow_balance' => 0,
                    ]
                );
                $sellerWallet->increment('pending_balance', $sellerEarnings);
            }

            // Update product sales count
            $product->increment('total_sales');

            return $order;
        });
    }

    public function completePurchase(DigitalProductOrder $order): void
    {
        DB::transaction(function () use ($order) {
            if ($order->status !== 'pending') {
                return;
            }

            $order->status = 'completed';
            $order->save();

            // Release funds to seller
            $product = $order->product;
            $seller = $product->user;
            $sellerWallet = $seller->wallet ?? Wallet::firstOrCreate(
                ['user_id' => $seller->id],
                [
                    'withdrawable_balance' => 0,
                    'promo_credit_balance' => 0,
                    'total_earned' => 0,
                    'total_spent' => 0,
                    'pending_balance' => 0,
                    'escrow_balance' => 0,
                ]
            );

            $sellerWallet->pending_balance = max(0, (float) $sellerWallet->pending_balance - (float) $order->seller_earnings);
            $sellerWallet->save();
            $sellerWallet->addWithdrawable(
                (float) $order->seller_earnings,
                'digital_product_sale',
                "Sale: {$product->title} (Order: {$order->order_number})"
            );

            // Record seller earning transaction
            Transaction::create([
                'user_id' => $product->user_id,
                'type' => 'credit',
                'amount' => $order->seller_earnings,
                'description' => "Sale: {$product->title} (Order: {$order->order_number})",
                'reference' => $order->order_number,
                'status' => 'completed',
            ]);

            // Record platform fee
            Transaction::create([
                'user_id' => $product->user_id,
                'type' => 'debit',
                'amount' => $order->platform_fee,
                'description' => "Platform fee: {$product->title}",
                'reference' => $order->order_number,
                'status' => 'completed',
            ]);
        });
    }

    public function processDownload(DigitalProductOrder $order): ?string
    {
        // First download finalizes pending paid purchase and releases seller payout
        if ($order->status === 'pending') {
            $this->completePurchase($order);
            $order->refresh();
        }

        if (!$order->can_download) {
            return null;
        }

        $order->incrementDownloadCount();

        $product = $order->product;

        return Storage::url($product->file_path);
    }

    public function addReview(DigitalProduct $product, int $userId, array $data): DigitalProductReview
    {
        // Check if user purchased the product
        $hasPurchased = DigitalProductOrder::where('product_id', $product->id)
            ->where('buyer_id', $userId)
            ->where('status', 'completed')
            ->exists();

        $review = new DigitalProductReview();
        $review->product_id = $product->id;
        $review->user_id = $userId;
        $review->rating = $data['rating'];
        $review->comment = $data['comment'] ?? null;
        $review->attachments = $data['attachments'] ?? [];
        $review->is_verified_purchase = $hasPurchased;
        $review->save();

        // Update product rating
        $this->updateProductRating($product);

        return $review;
    }

    protected function updateProductRating(DigitalProduct $product): void
    {
        $reviews = $product->reviews();
        $avgRating = $reviews->avg('rating');
        $count = $reviews->count();

        $product->rating = $avgRating ?? 0;
        $product->rating_count = $count;
        $product->save();
    }

    public function featureProduct(DigitalProduct $product): DigitalProduct
    {
        $product->is_featured = true;
        $product->save();
        return $product;
    }

    public function unfeatureProduct(DigitalProduct $product): DigitalProduct
    {
        $product->is_featured = false;
        $product->save();
        return $product;
    }

    public function deleteProduct(DigitalProduct $product): void
    {
        if ($product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
        }
        if ($product->file_path) {
            Storage::disk('public')->delete($product->file_path);
        }
        $product->delete();
    }

    protected function uploadFile(UploadedFile $file, string $folder): string
    {
        $path = $file->store($folder, 'public');
        return $path;
    }

    protected function getLicenseTypeName(int $type): string
    {
        switch ($type) {
            case 1: return 'personal';
            case 2: return 'commercial';
            case 3: return 'extended';
            default: return 'personal';
        }
    }

    protected function getMaxDownloads(int $type): int
    {
        switch ($type) {
            case 1: return 3;      // Personal: 3 downloads
            case 2: return 10;    // Commercial: 10 downloads
            case 3: return 999;   // Extended: unlimited
            default: return 3;
        }
    }
}
