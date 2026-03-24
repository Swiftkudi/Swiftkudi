<?php

namespace App\Services;

use App\Models\GrowthListing;
use App\Models\GrowthOrder;
use App\Models\User;
use App\Models\SystemSetting;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrowthService
{
    /**
     * Get commission rate
     */
    public function getCommissionRate(): float
    {
        return (float) SystemSetting::get('growth_commission', 15);
    }

    /**
     * Create a new listing
     */
    public function createListing(User $user, array $data): array
    {
        try {
            return DB::transaction(function () use ($user, $data) {
                if (!$user->wallet) {
                    Wallet::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'withdrawable_balance' => 0,
                            'promo_credit_balance' => 0,
                            'total_earned' => 0,
                            'total_spent' => 0,
                            'pending_balance' => 0,
                            'escrow_balance' => 0,
                        ]
                    );
                    $user->refresh();
                }

                $listing = GrowthListing::create([
                    'user_id' => $user->id,
                    'type' => $data['type'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'delivery_days' => $data['delivery_days'] ?? 1,
                    'specs' => $data['specs'] ?? [],
                    'status' => 'pending',
                ]);

                return [
                    'success' => true,
                    'message' => 'Listing created! Pending admin approval.',
                    'listing' => $listing,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error creating growth listing: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create listing'];
        }
    }

    /**
     * Create order with escrow
     */
    public function createOrder(User $buyer, int $listingId): array
    {
        try {
            return DB::transaction(function () use ($buyer, $listingId) {
                $listing = GrowthListing::findOrFail($listingId);

                if ($listing->status !== GrowthListing::STATUS_ACTIVE) {
                    return ['success' => false, 'message' => 'Listing is not available'];
                }

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

                $amount = $listing->price;
                $commissionRate = $this->getCommissionRate();
                $commission = round($amount * ($commissionRate / 100), 2);
                $sellerPayout = $amount - $commission;

                if ($wallet->withdrawable_balance < $amount) {
                    return [
                        'success' => false,
                        'message' => 'Insufficient balance',
                        'required' => $amount,
                        'available' => $wallet->withdrawable_balance,
                    ];
                }

                // Deduct from buyer
                $wallet->deductWithdrawable($amount, 'growth_order_' . $listingId);

                // Create order
                $order = GrowthOrder::create([
                    'listing_id' => $listingId,
                    'buyer_id' => $buyer->id,
                    'seller_id' => $listing->user_id,
                    'amount' => $amount,
                    'platform_commission' => $commission,
                    'seller_payout' => $sellerPayout,
                    'escrow_amount' => $amount,
                    'paid_amount' => $amount,
                    'status' => GrowthOrder::STATUS_PAID,
                ]);

                // Record commission
                $currentCommission = (float) SystemSetting::get('total_growth_commission', 0);
                SystemSetting::set('total_growth_commission', $currentCommission + $commission);

                return [
                    'success' => true,
                    'message' => 'Order placed! Funds held in escrow.',
                    'order' => $order,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error creating growth order: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create order'];
        }
    }

    /**
     * Submit proof (seller)
     */
    public function submitProof(GrowthOrder $order, User $seller, array $proofData, string $notes): array
    {
        try {
            return DB::transaction(function () use ($order, $seller, $proofData, $notes) {
                if ($order->seller_id !== $seller->id) {
                    return ['success' => false, 'message' => 'Unauthorized'];
                }

                if (!in_array($order->status, [GrowthOrder::STATUS_PAID, GrowthOrder::STATUS_IN_PROGRESS])) {
                    return ['success' => false, 'message' => 'Cannot submit proof in current status'];
                }

                $order->update([
                    'status' => GrowthOrder::STATUS_DELIVERED,
                    'proof_data' => $proofData,
                    'proof_notes' => $notes,
                    'delivered_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => 'Proof submitted! Waiting for buyer approval.',
                    'order' => $order,
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to submit proof'];
        }
    }

    /**
     * Approve proof and release escrow
     */
    public function approveProof(GrowthOrder $order, User $buyer): array
    {
        try {
            return DB::transaction(function () use ($order, $buyer) {
                if ($order->buyer_id !== $buyer->id) {
                    return ['success' => false, 'message' => 'Unauthorized'];
                }

                if (!in_array($order->status, [GrowthOrder::STATUS_DELIVERED, GrowthOrder::STATUS_REVISION])) {
                    return ['success' => false, 'message' => 'Cannot approve in current status'];
                }

                // Release funds to seller
                $seller = User::find($order->seller_id);
                if ($seller) {
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
                    $sellerWallet->addWithdrawable($order->seller_payout, 'growth_order_complete');
                }

                $order->update([
                    'status' => GrowthOrder::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => 'Order completed! Funds released to seller.',
                    'order' => $order,
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to approve'];
        }
    }

    /**
     * Request revision
     */
    public function requestRevision(GrowthOrder $order, User $buyer, string $notes): array
    {
        try {
            return DB::transaction(function () use ($order, $buyer, $notes) {
                if ($order->buyer_id !== $buyer->id) {
                    return ['success' => false, 'message' => 'Unauthorized'];
                }

                if ($order->status !== GrowthOrder::STATUS_DELIVERED) {
                    return ['success' => false, 'message' => 'Cannot request revision'];
                }

                $order->update([
                    'status' => GrowthOrder::STATUS_REVISION,
                    'proof_notes' => $notes,
                ]);

                return [
                    'success' => true,
                    'message' => 'Revision requested',
                    'order' => $order,
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to request revision'];
        }
    }

    /**
     * Cancel order and refund
     */
    public function cancelOrder(GrowthOrder $order, User $user): array
    {
        try {
            return DB::transaction(function () use ($order, $user) {
                if ($order->buyer_id !== $user->id && $order->seller_id !== $user->id) {
                    return ['success' => false, 'message' => 'Unauthorized'];
                }

                if (!$order->canCancel()) {
                    return ['success' => false, 'message' => 'Order cannot be cancelled'];
                }

                // Refund buyer
                $buyer = User::find($order->buyer_id);
                if ($buyer && $order->escrow_amount > 0) {
                    $buyerWallet = $buyer->wallet ?? Wallet::firstOrCreate(
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
                    $buyerWallet->addWithdrawable($order->escrow_amount, 'growth_order_refund');
                }

                $order->update([
                    'status' => GrowthOrder::STATUS_CANCELLED,
                    'escrow_amount' => 0,
                ]);

                return [
                    'success' => true,
                    'message' => 'Order cancelled and refunded',
                    'order' => $order,
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to cancel order'];
        }
    }

    /**
     * Get type specs fields
     */
    public static function getSpecsFields(string $type): array
    {
        $fields = [];
        
        switch($type) {
            case GrowthListing::TYPE_BACKLINKS:
                $fields = [
                    ['name' => 'website_url', 'label' => 'Website URL', 'type' => 'url', 'required' => true],
                    ['name' => 'niche', 'label' => 'Niche', 'type' => 'text', 'required' => true],
                    ['name' => 'traffic', 'label' => 'Monthly Traffic', 'type' => 'text', 'required' => false],
                    ['name' => 'domain_authority', 'label' => 'Domain Authority (DA)', 'type' => 'number', 'required' => false],
                    ['name' => 'link_type', 'label' => 'Link Type', 'type' => 'select', 'options' => ['Dofollow', 'Nofollow', 'Both'], 'required' => true],
                ];
                break;
                
            case GrowthListing::TYPE_INFLUENCER:
                $fields = [
                    ['name' => 'platform', 'label' => 'Platform', 'type' => 'select', 'options' => ['Instagram', 'TikTok', 'YouTube', 'Twitter', 'Facebook', 'Other'], 'required' => true],
                    ['name' => 'followers', 'label' => 'Followers Count', 'type' => 'number', 'required' => true],
                    ['name' => 'engagement_rate', 'label' => 'Engagement Rate (%)', 'type' => 'number', 'required' => false],
                    ['name' => 'audience_country', 'label' => 'Primary Audience Country', 'type' => 'text', 'required' => false],
                    ['name' => 'promotion_type', 'label' => 'Promotion Type', 'type' => 'select', 'options' => ['Story', 'Post', 'Reel', 'Video', 'Other'], 'required' => true],
                ];
                break;
                
            case GrowthListing::TYPE_NEWSLETTER:
                $fields = [
                    ['name' => 'subscriber_count', 'label' => 'Subscriber Count', 'type' => 'number', 'required' => true],
                    ['name' => 'open_rate', 'label' => 'Open Rate (%)', 'type' => 'number', 'required' => false],
                    ['name' => 'niche', 'label' => 'Niche/Category', 'type' => 'text', 'required' => true],
                ];
                break;
                
            case GrowthListing::TYPE_LEADS:
                $fields = [
                    ['name' => 'lead_type', 'label' => 'Lead Type', 'type' => 'select', 'options' => ['Email', 'Phone', 'Company', 'B2B', 'B2C', 'Other'], 'required' => true],
                    ['name' => 'target_country', 'label' => 'Target Country', 'type' => 'text', 'required' => true],
                    ['name' => 'min_quantity', 'label' => 'Minimum Quantity', 'type' => 'number', 'required' => true],
                ];
                break;
        }
        
        return $fields;
    }
}
