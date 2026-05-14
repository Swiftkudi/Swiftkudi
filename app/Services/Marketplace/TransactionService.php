<?php

namespace App\Services\Marketplace;

use App\Models\FinancialTransaction;
use App\Models\Marketplace\Order;
use App\Models\Marketplace\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function calculateCommission(float $orderTotal, bool $isPremiumSeller = false): array
    {
        if ($isPremiumSeller) {
            $rate = (float) SystemSetting::get('marketplace_premium_commission_rate', 2);
        } else {
            $rate = (float) SystemSetting::get('marketplace_commission_rate', 5);
        }

        $commission = round($orderTotal * ($rate / 100), 2);
        $sellerPayout = round($orderTotal - $commission, 2);

        return [
            'total' => $orderTotal,
            'commission_rate' => $rate,
            'commission_amount' => $commission,
            'seller_payout' => $sellerPayout,
        ];
    }

    public function recordCommission(Order $order): void
    {
        $seller = $order->seller;
        $isPremium = $seller->marketplaceSubscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();

        // Temporarily check User model for is_premium_seller column
        $isPremium = $seller->marketplaceSellerVerified()
            || DB::table('user_subscriptions')
                ->where('user_id', $seller->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->exists();

        $calc = $this->calculateCommission($order->amount, $isPremium);

        FinancialTransaction::create([
            'wallet_id' => $seller->wallet->id ?? null,
            'user_id' => $seller->id,
            'type' => 'marketplace_commission',
            'amount' => $calc['commission_amount'],
            'currency' => 'NGN',
            'status' => 'completed',
            'reference' => 'MKT-COMM-' . $order->id,
            'description' => "Marketplace commission for order #{$order->id}",
            'metadata' => [
                'order_id' => $order->id,
                'listing_id' => $order->listing_id,
                'commission_rate' => $calc['commission_rate'],
            ],
        ]);

        FinancialTransaction::create([
            'wallet_id' => $seller->wallet->id ?? null,
            'user_id' => $seller->id,
            'type' => 'marketplace_payout',
            'amount' => $calc['seller_payout'],
            'currency' => 'NGN',
            'status' => 'completed',
            'reference' => 'MKT-PAYOUT-' . $order->id,
            'description' => "Marketplace seller payout for order #{$order->id}",
        ]);

        $this->recalculateSellerRating($seller);
    }

    public function recalculateSellerRating(User $seller): void
    {
        $avg = Review::where('reviewed_id', $seller->id)
            ->where('is_approved', true)
            ->avg('rating');

        $seller->update([
            'seller_rating' => round($avg ?? 0, 2),
            'seller_rating_count' => Review::where('reviewed_id', $seller->id)
                ->where('is_approved', true)
                ->count(),
        ]);
    }
}