<?php

namespace App\Services\Marketplace;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function getPrice(): float
    {
        return (float) SystemSetting::get('marketplace_premium_seller_monthly', 2000);
    }

    public function subscribe(User $seller): array
    {
        DB::beginTransaction();
        try {
            $price = $this->getPrice();
            $wallet = $seller->wallet;

            if (!$wallet || !$wallet->canWithdraw($price)) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Insufficient balance. Minimum required: ₦' . number_format($price)];
            }

            $subscription = \App\Models\UserSubscription::create([
                'user_id' => $seller->id,
                'plan' => 'marketplace_premium',
                'status' => 'active',
                'amount_paid' => $price,
                'started_at' => now(),
                'expires_at' => now()->addMonth(),
                'metadata' => ['marketplace_plan' => true],
            ]);

            $wallet->deductWithdrawable($price, 'marketplace_subscription');
            $seller->update(['marketplace_seller_verified' => true]);

            DB::commit();

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $seller,
                'Premium Seller Subscribed',
                'You are now a Premium Seller on the marketplace. Enjoy lower commission rates!',
                \App\Models\Notification::TYPE_SYSTEM,
                [],
                'subscription_activated'
            );

            return [
                'success' => true,
                'message' => 'Subscribed to Premium Seller plan',
                'subscription' => $subscription,
                'expires_at' => $subscription->expires_at,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancel(User $seller): array
    {
        $activeSub = \App\Models\UserSubscription::where('user_id', $seller->id)
            ->where('status', 'active')
            ->where('metadata->marketplace_plan', true)
            ->first();

        if (!$activeSub) {
            return ['success' => false, 'message' => 'No active marketplace subscription found'];
        }

        $activeSub->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'user_request',
        ]);

        return ['success' => true, 'message' => 'Premium subscription cancelled'];
    }

    public function isPremium(User $seller): bool
    {
        return \App\Models\UserSubscription::where('user_id', $seller->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->where('metadata->marketplace_plan', true)
            ->exists();
    }

    public function processExpirations(): void
    {
        $expired = \App\Models\UserSubscription::where('status', 'active')
            ->where('metadata->marketplace_plan', true)
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $sub) {
            $sub->update(['status' => 'expired']);
            $sub->user()->update(['marketplace_seller_verified' => false]);
        }
    }
}