<?php

namespace App\Services\Marketplace;

use App\Models\User;
use App\Models\Wallet;
use App\Models\EscrowTransaction;
use App\Models\FinancialTransaction;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MarketplaceService
{
    public function getCommissionRate(): float
    {
        return (float) SystemSetting::get('marketplace_commission_rate', 5);
    }

    public function calculateEscrow(float $amount): array
    {
        $commissionRate = $this->getCommissionRate();
        $platformFee = round($amount * ($commissionRate / 100), 2);
        $escrowAmount = round($amount - $platformFee, 2);

        return [
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'escrow_amount' => $escrowAmount,
            'commission_rate' => $commissionRate,
        ];
    }

    public function holdInEscrow(User $buyer, User $seller, float $totalAmount, float $platformFee, $order, string $description): array
    {
        try {
            return DB::transaction(function () use ($buyer, $seller, $totalAmount, $platformFee, $order, $description) {
                $wallet = $buyer->wallet;

                if (!$wallet) {
                    return ['success' => false, 'message' => 'Wallet not found'];
                }

                $totalBalance = $wallet->withdrawable_balance + $wallet->promo_credit_balance;
                if ($totalBalance < $totalAmount) {
                    return ['success' => false, 'message' => 'Insufficient balance', 'available' => $totalBalance, 'required' => $totalAmount];
                }

                // Withdraw from wallet: promo credit first, then withdrawable
                $remaining = $totalAmount;
                if ($wallet->promo_credit_balance > 0) {
                    $fromPromo = min($wallet->promo_credit_balance, $remaining);
                    $wallet->deductPromoCredit($fromPromo, 'marketplace_escrow', $description);
                    $remaining -= $fromPromo;
                }
                if ($remaining > 0) {
                    $wallet->deductWithdrawable($remaining, 'marketplace_escrow', $description);
                }

                // Increase escrow balance
                $wallet->escrow_balance = ($wallet->escrow_balance ?? 0) + $totalAmount;
                $wallet->save();

                $escrow = EscrowTransaction::create([
                    'transaction_no' => 'MKT-ESC-' . strtoupper(Str::random(10)),
                    'order_id' => $order->id,
                    'order_type' => get_class($order),
                    'payer_id' => $buyer->id,
                    'payee_id' => $seller->id,
                    'amount' => $totalAmount - $platformFee,
                    'platform_fee' => $platformFee,
                    'total_amount' => $totalAmount,
                    'status' => EscrowTransaction::STATUS_FUNDED,
                ]);

                Log::info('Marketplace escrow funded', [
                    'order_id' => $order->id,
                    'escrow_id' => $escrow->id,
                    'amount' => $totalAmount,
                    'buyer_id' => $buyer->id,
                ]);

                return ['success' => true, 'message' => 'Payment held in escrow', 'escrow_transaction' => $escrow];
            });
        } catch (\Exception $e) {
            Log::error('Marketplace escrow hold failed', [
                'buyer_id' => $buyer->id,
                'amount' => $totalAmount,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'Failed to process payment: ' . $e->getMessage()];
        }
    }

    public function releaseEscrow(EscrowTransaction $escrow, string $description): array
    {
        try {
            return DB::transaction(function () use ($escrow, $description) {
                $payerWallet = Wallet::firstOrCreate(
                    ['user_id' => $escrow->payer_id],
                    ['withdrawable_balance' => 0, 'promo_credit_balance' => 0,
                     'total_earned' => 0, 'total_spent' => 0, 'pending_balance' => 0, 'escrow_balance' => 0]
                );

                if ($payerWallet->escrow_balance < $escrow->total_amount) {
                    return ['success' => false, 'message' => 'Insufficient escrow balance'];
                }

                $payerWallet->escrow_balance -= $escrow->total_amount;
                $payerWallet->save();

                $payeeWallet = Wallet::firstOrCreate(
                    ['user_id' => $escrow->payee_id],
                    ['withdrawable_balance' => 0, 'promo_credit_balance' => 0,
                     'total_earned' => 0, 'total_spent' => 0, 'pending_balance' => 0, 'escrow_balance' => 0]
                );

                $payeeWallet->addWithdrawable($escrow->amount, 'marketplace_earning', $description);

                FinancialTransaction::create([
                    'wallet_id' => $payeeWallet->id,
                    'user_id' => $escrow->payee_id,
                    'type' => 'marketplace_earning',
                    'amount' => $escrow->amount,
                    'status' => 'completed',
                    'reference' => 'MKT-EARN-' . $escrow->id,
                    'description' => "Earning from marketplace order — {$description}",
                ]);

                $escrow->status = EscrowTransaction::STATUS_RELEASED;
                $escrow->released_at = now();
                $escrow->save();

                Log::info('Marketplace escrow released', ['escrow_id' => $escrow->id]);

                return ['success' => true, 'message' => 'Payment released to seller', 'released_amount' => $escrow->amount];
            });
        } catch (\Exception $e) {
            Log::error('Marketplace escrow release failed', ['escrow_id' => $escrow->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to release payment: ' . $e->getMessage()];
        }
    }

    public function refundFromEscrow(EscrowTransaction $escrow, string $description): array
    {
        try {
            return DB::transaction(function () use ($escrow, $description) {
                $payerWallet = Wallet::firstOrCreate(
                    ['user_id' => $escrow->payer_id],
                    ['withdrawable_balance' => 0, 'promo_credit_balance' => 0,
                     'total_earned' => 0, 'total_spent' => 0, 'pending_balance' => 0, 'escrow_balance' => 0]
                );

                if ($payerWallet->escrow_balance < $escrow->total_amount) {
                    return ['success' => false, 'message' => 'Insufficient escrow balance'];
                }

                $payerWallet->escrow_balance -= $escrow->total_amount;
                $payerWallet->save();
                $payerWallet->addWithdrawable($escrow->total_amount, 'marketplace_refund', $description);

                FinancialTransaction::create([
                    'wallet_id' => $payerWallet->id,
                    'user_id' => $escrow->payer_id,
                    'type' => 'marketplace_refund',
                    'amount' => $escrow->total_amount,
                    'status' => 'completed',
                    'reference' => 'MKT-REFUND-' . $escrow->id,
                    'description' => "Refund: {$description}",
                ]);

                $escrow->status = EscrowTransaction::STATUS_CANCELLED;
                $escrow->save();

                return ['success' => true, 'message' => 'Refund processed', 'refunded_amount' => $escrow->total_amount];
            });
        } catch (\Exception $e) {
            Log::error('Marketplace escrow refund failed', ['escrow_id' => $escrow->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to process refund: ' . $e->getMessage()];
        }
    }

    public function getEscrowTransaction($order): ?EscrowTransaction
    {
        return EscrowTransaction::where('order_id', $order->id)
            ->where('order_type', get_class($order))
            ->first();
    }

    public function getPlatformFee(float $amount): array
    {
        return $this->calculateEscrow($amount);
    }

    public function resolveDispute($dispute, string $resolution, ?string $notes = null): array
    {
        try {
            DB::transaction(function () use ($dispute, $resolution, $notes) {
                $order = $dispute->disputable;

                $dispute->update([
                    'status' => \App\Models\Dispute::STATUS_RESOLVED,
                    'resolution' => $resolution,
                    'resolution_notes' => $notes,
                    'resolved_by' => auth()->id(),
                    'resolved_at' => now(),
                ]);

                $escrow = $this->getEscrowTransaction($order);
                if (!$escrow) {
                    throw new \Exception('Escrow transaction not found for order');
                }

                switch ($resolution) {
                    case \App\Models\Dispute::RESOLUTION_BUYER_WINS:
                        $this->refundFromEscrow($escrow, 'Dispute resolution — Buyer wins');
                        $order->update(['status' => Order::STATUS_REFUNDED]);
                        break;
                    case \App\Models\Dispute::RESOLUTION_SELLER_WINS:
                        $this->releaseEscrow($escrow, 'Dispute resolution — Seller wins');
                        $order->update(['status' => Order::STATUS_COMPLETED]);
                        break;
                    case \App\Models\Dispute::RESOLUTION_REFUND:
                        $this->refundFromEscrow($escrow, 'Dispute resolution — Refund');
                        $order->update(['status' => Order::STATUS_REFUNDED]);
                        break;
                    case \App\Models\Dispute::RESOLUTION_SPLIT:
                        $this->releaseEscrow($escrow, 'Dispute resolution — Split payout');
                        $order->update(['status' => Order::STATUS_COMPLETED]);
                        break;
                }
            });

            return ['success' => true, 'message' => 'Dispute resolved successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to resolve dispute: ' . $e->getMessage()];
        }
    }
}