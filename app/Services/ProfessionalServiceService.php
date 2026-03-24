<?php

namespace App\Services;

use App\Models\ProfessionalService;
use App\Models\ProfessionalServiceAddon;
use App\Models\ProfessionalServiceCategory;
use App\Models\ProfessionalServiceOrder;
use App\Models\ProfessionalServiceMessage;
use App\Models\ProfessionalServiceReview;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use App\Models\Wallet;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfessionalServiceService
{
    /**
     * Get commission rate from settings
     */
    public function getCommissionRate(): float
    {
        return (float) SystemSetting::get('professional_service_commission', 10);
    }

    /**
     * Create a new service listing
     */
    public function createService(User $user, array $data): array
    {
        try {
            return DB::transaction(function () use ($user, $data) {
                // Ensure user has wallet (activation is optional)
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

                $service = ProfessionalService::create([
                    'user_id' => $user->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'category_id' => $data['category_id'] ?? null,
                    'price' => $data['price'],
                    'delivery_days' => $data['delivery_days'],
                    'revisions_included' => $data['revisions_included'] ?? 1,
                    'portfolio_links' => $data['portfolio_links'] ?? [],
                    'portfolio_images' => $data['portfolio_images'] ?? [],
                    'status' => 'pending', // Requires admin approval
                ]);

                // Create add-ons if provided
                if (!empty($data['addons'])) {
                    foreach ($data['addons'] as $addon) {
                        ProfessionalServiceAddon::create([
                            'service_id' => $service->id,
                            'name' => $addon['name'],
                            'description' => $addon['description'] ?? null,
                            'price' => $addon['price'],
                            'delivery_days_extra' => $addon['delivery_days_extra'] ?? 0,
                        ]);
                    }
                }

                return [
                    'success' => true,
                    'message' => 'Service created! It will be reviewed by an admin.',
                    'service' => $service,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error creating professional service: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create service: ' . $e->getMessage()];
        }
    }

    /**
     * Create an order and fund via escrow
     */
    public function createOrder(User $buyer, int $serviceId, array $addonIds, string $requirements): array
    {
        try {
            return DB::transaction(function () use ($buyer, $serviceId, $addonIds, $requirements) {
                $service = ProfessionalService::findOrFail($serviceId);

                // Verify service is active
                if ($service->status !== ProfessionalService::STATUS_ACTIVE) {
                    return ['success' => false, 'message' => 'This service is not available'];
                }

                // Verify buyer has sufficient balance
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

                // Calculate totals
                $servicePrice = $service->price;
                $addonsTotal = 0;
                $selectedAddons = [];

                if (!empty($addonIds)) {
                    $addons = ProfessionalServiceAddon::whereIn('id', $addonIds)
                        ->where('service_id', $serviceId)
                        ->get();
                    
                    foreach ($addons as $addon) {
                        $addonsTotal += $addon->price;
                        $selectedAddons[] = $addon;
                    }
                }

                $totalAmount = $servicePrice + $addonsTotal;
                $commissionRate = $this->getCommissionRate();
                $commission = round($totalAmount * ($commissionRate / 100), 2);
                $sellerPayout = $totalAmount - $commission;

                // Check balance
                if ($wallet->withdrawable_balance < $totalAmount) {
                    return [
                        'success' => false,
                        'message' => 'Insufficient balance',
                        'required' => $totalAmount,
                        'available' => $wallet->withdrawable_balance,
                    ];
                }

                // Deduct from buyer's wallet and hold in escrow
                $wallet->deductWithdrawable($totalAmount, 'service_order_' . $serviceId);

                // Create order
                $order = ProfessionalServiceOrder::create([
                    'service_id' => $serviceId,
                    'buyer_id' => $buyer->id,
                    'seller_id' => $service->user_id,
                    'service_price' => $servicePrice,
                    'addons_total' => $addonsTotal,
                    'total_amount' => $totalAmount,
                    'platform_commission' => $commission,
                    'seller_payout' => $sellerPayout,
                    'escrow_amount' => $totalAmount,
                    'paid_amount' => $totalAmount,
                    'status' => ProfessionalServiceOrder::STATUS_PAID,
                    'requirements' => $requirements,
                ]);

                // Record commission in system settings (accrued platform revenue)
                $currentCommission = (float) SystemSetting::get('total_professional_commission', 0);
                SystemSetting::set('total_professional_commission', $currentCommission + $commission);

                return [
                    'success' => true,
                    'message' => 'Order placed! Funds held in escrow.',
                    'order' => $order,
                    'escrow_amount' => $totalAmount,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error creating professional service order: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage()];
        }
    }

    /**
     * Seller delivers work
     */
    public function deliverOrder(ProfessionalServiceOrder $order, User $seller, string $notes, array $files): array
    {
        try {
            return DB::transaction(function () use ($order, $seller, $notes, $files) {
                // Verify seller owns this order
                if ($order->seller_id !== $seller->id) {
                    return ['success' => false, 'message' => 'Unauthorized'];
                }

                // Verify order is in progress
                if (!in_array($order->status, [ProfessionalServiceOrder::STATUS_PAID, ProfessionalServiceOrder::STATUS_IN_PROGRESS])) {
                    return ['success' => false, 'message' => 'Order cannot be delivered in current status'];
                }

                $order->update([
                    'status' => ProfessionalServiceOrder::STATUS_DELIVERED,
                    'delivery_notes' => $notes,
                    'delivery_files' => $files,
                    'delivered_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => 'Work delivered! Waiting for buyer approval.',
                    'order' => $order,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error delivering order: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to deliver: ' . $e->getMessage()];
        }
    }

    /**
     * Buyer approves delivery and releases escrow
     */
    public function approveDelivery(ProfessionalServiceOrder $order, User $buyer): array
    {
        try {
            return DB::transaction(function () use ($order, $buyer) {
                // Verify buyer owns this order
                if ($order->buyer_id !== $buyer->id) {
                    return ['success' => false, 'message' => 'Unauthorized'];
                }

                // Verify order can be completed
                if (!in_array($order->status, [ProfessionalServiceOrder::STATUS_DELIVERED, ProfessionalServiceOrder::STATUS_REVISION])) {
                    return ['success' => false, 'message' => 'Order cannot be completed in current status'];
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
                    $sellerWallet->addWithdrawable($order->seller_payout, 'service_order_complete');
                }

                // Update order status
                $order->update([
                    'status' => ProfessionalServiceOrder::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);

                // Update seller stats
                $profile = ServiceProviderProfile::where('user_id', $order->seller_id)->first();
                if ($profile) {
                    $profile->increment('total_orders_completed');
                }

                return [
                    'success' => true,
                    'message' => 'Order completed! Funds released to seller.',
                    'order' => $order,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error approving delivery: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to approve: ' . $e->getMessage()];
        }
    }

    public function approveDeliveryWithReview(ProfessionalServiceOrder $order, User $buyer, int $rating, string $comment): array
    {
        try {
            return DB::transaction(function () use ($order, $buyer, $rating, $comment) {
                if ($order->buyer_id !== $buyer->id) {
                    return ['success' => false, 'message' => 'Unauthorized'];
                }

                if (!in_array($order->status, [ProfessionalServiceOrder::STATUS_DELIVERED, ProfessionalServiceOrder::STATUS_REVISION])) {
                    return ['success' => false, 'message' => 'Order cannot be completed in current status'];
                }

                $existingReview = ProfessionalServiceReview::where('order_id', $order->id)
                    ->where('reviewer_id', $buyer->id)
                    ->first();

                if ($existingReview) {
                    return ['success' => false, 'message' => 'You already reviewed this order'];
                }

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
                    $sellerWallet->addWithdrawable($order->seller_payout, 'service_order_complete');
                }

                $order->update([
                    'status' => ProfessionalServiceOrder::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);

                $review = ProfessionalServiceReview::create([
                    'order_id' => $order->id,
                    'reviewer_id' => $buyer->id,
                    'reviewee_id' => $order->seller_id,
                    'rating' => $rating,
                    'comment' => $comment,
                ]);

                $review->updateSellerRating();

                $profile = ServiceProviderProfile::where('user_id', $order->seller_id)->first();
                if ($profile) {
                    $profile->increment('total_orders_completed');
                }

                if ($seller) {
                    app(\App\Services\NotificationDispatchService::class)->sendToUser(
                        $seller,
                        'Service Payment Released',
                        'Buyer confirmed satisfaction for "' . ($order->service->title ?? 'your service') . '". Payment has been released to your wallet.',
                        \App\Models\Notification::TYPE_SYSTEM,
                        [
                            'order_id' => $order->id,
                            'action_url' => route('professional-services.orders.show', $order->id) . '#order-actions',
                        ],
                        'notify_service_orders',
                        true
                    );
                }

                app(\App\Services\NotificationDispatchService::class)->sendToUser(
                    $buyer,
                    'Service Confirmed Successfully',
                    'You confirmed delivery and submitted a review. Payment has been released to the provider.',
                    \App\Models\Notification::TYPE_SYSTEM,
                    [
                        'order_id' => $order->id,
                        'action_url' => route('professional-services.orders.show', $order->id) . '#order-actions',
                    ],
                    'notify_service_orders'
                );

                return [
                    'success' => true,
                    'message' => 'Service confirmed, review submitted, and payment released to seller.',
                    'order' => $order,
                    'review' => $review,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error approving delivery with review: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to confirm and release payment'];
        }
    }

    /**
     * Request revision
     */
    public function requestRevision(ProfessionalServiceOrder $order, User $buyer, string $notes): array
    {
        try {
            return DB::transaction(function () use ($order, $buyer, $notes) {
                if ($order->buyer_id !== $buyer->id) {
                    return ['success' => false, 'message' => 'Unauthorized'];
                }

                if (!$order->canRequestRevision()) {
                    return ['success' => false, 'message' => 'Cannot request revision'];
                }

                $order->update([
                    'status' => ProfessionalServiceOrder::STATUS_REVISION,
                    'revisions_requested' => $order->revisions_requested + 1,
                    'revision_notes' => $notes,
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
    public function cancelOrder(ProfessionalServiceOrder $order, User $user): array
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
                if ($buyer && $buyer->wallet && $order->escrow_amount > 0) {
                    $buyer->wallet->addWithdrawable($order->escrow_amount, 'service_order_refund');
                }

                $order->update([
                    'status' => ProfessionalServiceOrder::STATUS_CANCELLED,
                    'escrow_amount' => 0,
                    'cancelled_at' => now(),
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
     * Create review
     */
    public function createReview(ProfessionalServiceOrder $order, User $reviewer, int $rating, string $comment): array
    {
        try {
            return DB::transaction(function () use ($order, $reviewer, $rating, $comment) {
                // Only buyer can review
                if ($order->buyer_id !== $reviewer->id) {
                    return ['success' => false, 'message' => 'Only buyer can leave a review'];
                }

                // Order must be completed
                if ($order->status !== ProfessionalServiceOrder::STATUS_COMPLETED) {
                    return ['success' => false, 'message' => 'Can only review completed orders'];
                }

                // Check if already reviewed
                $existing = ProfessionalServiceReview::where('order_id', $order->id)
                    ->where('reviewer_id', $reviewer->id)
                    ->first();
                
                if ($existing) {
                    return ['success' => false, 'message' => 'Already reviewed'];
                }

                $review = ProfessionalServiceReview::create([
                    'order_id' => $order->id,
                    'reviewer_id' => $reviewer->id,
                    'reviewee_id' => $order->seller_id,
                    'rating' => $rating,
                    'comment' => $comment,
                ]);

                // Update seller rating
                $review->updateSellerRating();

                return [
                    'success' => true,
                    'message' => 'Review submitted',
                    'review' => $review,
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to create review'];
        }
    }

    /**
     * Create or update service provider profile
     */
    public function updateProviderProfile(User $user, array $data): array
    {
        try {
            $profile = ServiceProviderProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'is_available' => $data['is_available'] ?? true,
                    'hourly_rate' => $data['hourly_rate'] ?? null,
                    'bio' => $data['bio'] ?? null,
                    'skills' => $data['skills'] ?? [],
                    'portfolio_links' => $data['portfolio_links'] ?? [],
                    'certifications' => $data['certifications'] ?? [],
                ]
            );

            return [
                'success' => true,
                'message' => 'Profile updated',
                'profile' => $profile,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to update profile'];
        }
    }

    /**
     * Get settings for service creation
     */
    public function getSettings(): array
    {
        return [
            'commission_rate' => $this->getCommissionRate(),
            'categories' => ProfessionalServiceCategory::active()->get(),
            'max_delivery_days' => (int) SystemSetting::get('professional_service_max_delivery', 30),
            'max_revisions' => (int) SystemSetting::get('professional_service_max_revisions', 5),
        ];
    }
}
