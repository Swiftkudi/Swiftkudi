<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class EscrowService
{
    protected $defaultPlatformFeePercent = 10;

    /**
     * Hold funds in escrow from buyer
     */
    public function holdInEscrow(int $buyerId, float $amount, string $description, string $reference): bool
    {
        return DB::transaction(function () use ($buyerId, $amount, $description, $reference) {
            $buyer = User::findOrFail($buyerId);
            $wallet = $buyer->wallet;

            // Check balance
            if ($wallet->balance < $amount) {
                throw new \Exception('Insufficient balance');
            }

            // Deduct from available balance
            $wallet->decrement('balance', $amount);
            
            // Add to escrow balance
            $wallet->increment('escrow_balance', $amount);

            // Record transaction
            Transaction::create([
                'user_id' => $buyerId,
                'type' => 'escrow_hold',
                'amount' => $amount,
                'description' => $description,
                'reference' => $reference,
                'status' => 'completed',
            ]);

            return true;
        });
    }

    /**
     * Release escrow funds to seller
     */
    public function releaseToSeller(int $sellerId, float $amount, float $platformFee, string $description, string $reference): bool
    {
        return DB::transaction(function () use ($sellerId, $amount, $platformFee, $description, $reference) {
            $seller = User::findOrFail($sellerId);
            $wallet = $seller->wallet;
            $sellerEarnings = $amount - $platformFee;

            // Remove from escrow
            $wallet->decrement('escrow_balance', $amount);
            
            // Add to seller's available balance
            $wallet->increment('balance', $sellerEarnings);

            // Record seller earnings transaction
            Transaction::create([
                'user_id' => $sellerId,
                'type' => 'earning',
                'amount' => $sellerEarnings,
                'description' => $description,
                'reference' => $reference,
                'status' => 'completed',
            ]);

            // Record platform fee
            Transaction::create([
                'user_id' => $sellerId,
                'type' => 'debit',
                'amount' => $platformFee,
                'description' => "Platform fee - {$description}",
                'reference' => $reference,
                'status' => 'completed',
            ]);

            return true;
        });
    }

    /**
     * Refund escrow back to buyer
     */
    public function refundToBuyer(int $buyerId, float $amount, string $description, string $reference): bool
    {
        return DB::transaction(function () use ($buyerId, $amount, $description, $reference) {
            $buyer = User::findOrFail($buyerId);
            $wallet = $buyer->wallet;

            // Remove from escrow
            $wallet->decrement('escrow_balance', $amount);
            
            // Return to available balance
            $wallet->increment('balance', $amount);

            // Record refund transaction
            Transaction::create([
                'user_id' => $buyerId,
                'type' => 'refund',
                'amount' => $amount,
                'description' => $description,
                'reference' => $reference,
                'status' => 'completed',
            ]);

            return true;
        });
    }

    /**
     * Cancel escrow (return to buyer without refund)
     */
    public function cancelEscrow(int $buyerId, float $amount, string $description, string $reference): bool
    {
        return DB::transaction(function () use ($buyerId, $amount, $description, $reference) {
            $buyer = User::findOrFail($buyerId);
            $wallet = $buyer->wallet;

            // Remove from escrow only (not returning to balance - it's a cancellation)
            $wallet->decrement('escrow_balance', $amount);

            // Record cancellation transaction
            Transaction::create([
                'user_id' => $buyerId,
                'type' => 'escrow_cancelled',
                'amount' => $amount,
                'description' => $description,
                'reference' => $reference,
                'status' => 'completed',
            ]);

            return true;
        });
    }

    /**
     * Get total escrow balance for a user
     */
    public function getUserEscrowBalance(int $userId): float
    {
        $wallet = User::findOrFail($userId)->wallet;
        return $wallet->escrow_balance ?? 0;
    }

    /**
     * Calculate platform fee
     */
    public function calculatePlatformFee(float $amount, ?float $customRate = null): float
    {
        $rate = $customRate ?? $this->defaultPlatformFeePercent;
        return $amount * ($rate / 100);
    }

    /**
     * Get escrow transactions for a user
     */
    public function getEscrowTransactions(int $userId)
    {
        return Transaction::where('user_id', $userId)
            ->whereIn('type', ['escrow_hold', 'escrow_release', 'escrow_cancelled', 'refund'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
