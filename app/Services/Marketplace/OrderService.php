<?php

namespace App\Services\Marketplace;

use App\Models\Marketplace\Listing;
use App\Models\Marketplace\Order;
use App\Models\User;
use App\Models\EscrowTransaction;
use App\Services\Marketplace\TransactionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService
{
    protected MarketplaceService $marketplaceSvc;
    protected TransactionService $transactionSvc;

    public function __construct()
    {
        $this->marketplaceSvc = app(MarketplaceService::class);
        $this->transactionSvc = app(TransactionService::class);
    }

    public function createOrder(Listing $listing, User $buyer, array $data = []): array
    {
        if ($listing->status !== Listing::STATUS_ACTIVE || $listing->user_id === $buyer->id) {
            return ['success' => false, 'message' => 'Listing not available'];
        }

        $amount = $listing->price;
        $shippingCost = $data['shipping_cost'] ?? 0;
        $total = $amount + $shippingCost;

        $feeCalc = $this->marketplaceSvc->calculateEscrow($total);
        $seller = $listing->seller;

        return DB::transaction(function () use ($listing, $buyer, $seller, $amount, $shippingCost, $total, $feeCalc) {
            $wallet = $buyer->wallet;
            if (!$wallet) {
                return ['success' => false, 'message' => 'Wallet not found. Please activate your wallet first.'];
            }

            $totalBalance = $wallet->withdrawable_balance + $wallet->promo_credit_balance;
            if ($totalBalance < $total) {
                return ['success' => false, 'message' => 'Insufficient balance', 'required' => $total, 'available' => $totalBalance];
            }

            $order = Order::create([
                'listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'amount' => $amount,
                'platform_fee' => $feeCalc['platform_fee'],
                'shipping_cost' => $shippingCost,
                'total_amount' => $feeCalc['amount'],
                'escrow_amount' => $feeCalc['escrow_amount'],
                'idempotency_key' => $data['idempotency_key'] ?? Str::uuid()->toString(),
            ]);

            $escrowResult = $this->marketplaceSvc->holdInEscrow(
                $buyer, $seller,
                $feeCalc['escrow_amount'],
                $feeCalc['platform_fee'],
                $order,
                "Marketplace order #{$order->id} — {$listing->title}"
            );

            if (!$escrowResult['success']) {
                throw new \Exception('Escrow hold failed: ' . $escrowResult['message']);
            }

            $listing->update(['status' => Listing::STATUS_SOLD]);

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $seller,
                'New Order Received',
                "You have a new order for \"{$listing->title}\" from {$buyer->name}.",
                \App\Models\Notification::TYPE_SYSTEM,
                ['order_id' => $order->id, 'action_url' => route('marketplace.sales.show', $order)],
                'new_marketplace_order'
            );

            Log::info('Marketplace order created', [
                'order_id' => $order->id,
                'listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'amount' => $feeCalc['amount'],
            ]);

            return ['success' => true, 'order' => $order, 'escrow' => $escrowResult['escrow_transaction']];
        });
    }

    public function confirmReceipt(Order $order, User $buyer): array
    {
        if ($order->buyer_id !== $buyer->id) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        if ($order->status !== Order::STATUS_DELIVERED) {
            return ['success' => false, 'message' => 'Order must be in delivered state'];
        }

        return DB::transaction(function () use ($order) {
            $escrow = $this->marketplaceSvc->getEscrowTransaction($order);
            if (!$escrow || !$escrow->isFunded()) {
                return ['success' => false, 'message' => 'No funded escrow found for this order'];
            }

            $releaseResult = $this->marketplaceSvc->releaseEscrow(
                $escrow,
                "Order #{$order->id} — Buyer confirmed receipt"
            );

            if (!$releaseResult['success']) {
                throw new \Exception('Escrow release failed: ' . $releaseResult['message']);
            }

            $order->markAsCompleted();
            $this->transactionSvc->recordCommission($order);

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $order->seller,
                'Order Completed',
                "Your order #{$order->id} for \"{$order->listing->title}\" has been completed. ₦{$order->platform_fee} platform fee was deducted. ₦{$order->escrow_amount} credited.",
                \App\Models\Notification::TYPE_SYSTEM,
                ['order_id' => $order->id, 'action_url' => route('marketplace.sales.show', $order)],
                'order_completed'
            );

            return ['success' => true, 'message' => 'Order confirmed and funds released'];
        });
    }

    public function cancelOrder(Order $order, User $buyer): array
    {
        if ($order->buyer_id !== $buyer->id) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        if (!in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_PAID])) {
            return ['success' => false, 'message' => 'Order can only be cancelled when pending or paid'];
        }

        return DB::transaction(function () use ($order) {
            if ($order->status === Order::STATUS_PAID) {
                $escrow = $this->marketplaceSvc->getEscrowTransaction($order);
                if ($escrow && $escrow->isFunded()) {
                    $this->marketplaceSvc->refundFromEscrow(
                        $escrow,
                        "Order #{$order->id} cancelled"
                    );
                }
            }

            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            if ($order->listing) {
                $order->listing()->update(['status' => Listing::STATUS_ACTIVE]);
            }

            return ['success' => true, 'message' => 'Order cancelled and funds refunded'];
        });
    }

    public function markAsShipped(Order $order, User $seller): array
    {
        if ($order->seller_id !== $seller->id) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        if ($order->status !== Order::STATUS_PAID) {
            return ['success' => false, 'message' => 'Order must be in paid state'];
        }

        $order->update([
            'status' => Order::STATUS_IN_PROGRESS,
            'seller_notes' => request('seller_notes', $order->seller_notes),
        ]);

        app(\App\Services\NotificationDispatchService::class)->sendToUser(
            $order->buyer,
            'Order Shipped',
            "Your order #{$order->id} for \"{$order->listing->title}\" has been shipped.",
            \App\Models\Notification::TYPE_SYSTEM,
            ['order_id' => $order->id]
        );

        return ['success' => true, 'message' => 'Order marked as shipped'];
    }

    public function markAsDelivered(Order $order, User $buyer): array
    {
        if ($order->buyer_id !== $buyer->id) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        if ($order->status !== Order::STATUS_IN_PROGRESS) {
            return ['success' => false, 'message' => 'Order must be in progress state'];
        }

        $order->markAsDelivered();

        app(\App\Services\NotificationDispatchService::class)->sendToUser(
            $order->seller,
            'Order Delivered',
            "Your order #{$order->id} for \"{$order->listing->title}\" has been marked delivered.",
            \App\Models\Notification::TYPE_SYSTEM,
            ['order_id' => $order->id]
        );

        return ['success' => true, 'message' => 'Order marked as delivered'];
    }

    public function processAutoReleases(): void
    {
        $autoReleaseDays = (int) SystemSetting::get('marketplace_auto_release_days', 7);

        $orders = Order::delivered()
            ->where('delivered_at', '<', now()->subDays($autoReleaseDays))
            ->whereDoesntHave('disputes', function ($q) {
                $q->whereIn('status', ['open', 'under_review']);
            })
            ->get();

        foreach ($orders as $order) {
            $escrow = $this->marketplaceSvc->getEscrowTransaction($order);
            if ($escrow && $escrow->isFunded()) {
                $this->marketplaceSvc->releaseEscrow(
                    $escrow,
                    "Auto-release after {$autoReleaseDays} days"
                );
                $order->markAsCompleted();

                Log::info('Auto-released escrow for marketplace order', [
                    'order_id' => $order->id,
                    'escrow_id' => $escrow->id,
                ]);
            }
        }
    }
}