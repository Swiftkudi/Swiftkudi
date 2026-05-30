<?php

namespace App\Services\Marketplace;

use App\Models\Marketplace\MarketplaceReview;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function submitReview(MarketplaceOrder $order, User $reviewer, array $data): MarketplaceReview
    {
        return DB::transaction(function () use ($order, $reviewer, $data) {
            $review = MarketplaceReview::create([
                'order_id' => $order->id,
                'reviewer_id' => $reviewer->id,
                'reviewed_id' => $order->seller_id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'is_approved' => (bool) SystemSetting::get('marketplace_auto_approve_reviews', true),
            ]);

            if ($review->is_approved) {
                app(TransactionService::class)->recalculateSellerRating($order->seller);
            }

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $order->seller,
                'New Review Received',
                "{$reviewer->name} left a {$review->rating}-star review on your order #{$order->id}.",
                \App\Models\Notification::TYPE_SYSTEM,
                ['order_id' => $order->id, 'action_url' => route('marketplace.sales.show', $order)],
                'marketplace_review'
            );

            return $review;
        });
    }

    public function moderateReview(MarketplaceReview $review, bool $approve): void
    {
        $review->update(['is_approved' => $approve]);
        if ($approve) {
            app(TransactionService::class)->recalculateSellerRating($review->reviewed);
        }
    }
}