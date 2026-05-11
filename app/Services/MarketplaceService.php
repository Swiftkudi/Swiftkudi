<?php

namespace App\Services;

use App\Models\Dispute;
use App\Models\EscrowTransaction;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MarketplaceService
{
    protected $earnDeskService;

    public function __construct(SwiftKudiService $earnDeskService)
    {
        $this->earnDeskService = $earnDeskService;
    }

    /**
     * Get commission rate for service or growth marketplace
     */
    public function getCommissionRate(string $type = 'service'): float
    {
        $key = $type === 'service' ? 'service_commission_rate' : 'growth_commission_rate';
        return (float) SystemSetting::get($key, 10);
    }

    /**
     * Calculate platform fee and escrow amount
     */
    public function calculateEscrow(float $amount, string $type = 'service'): array
    {
        $commissionRate = $this->getCommissionRate($type);
        $platformFee = $amount * ($commissionRate / 100);
        $escrowAmount = $amount - $platformFee;

        return [
            'amount' => $amount,
            'platform_fee' => round($platformFee, 2),
            'escrow_amount' => round($escrowAmount, 2),
            'commission_rate' => $commissionRate,
        ];
    }

    /**
     * Move funds from buyer to escrow
     */
    public function holdInEscrow(User $buyer, User $seller, float $totalAmount, float $platformFee, $order, string $description): array
    {
        try {
            return DB::transaction(function () use ($buyer, $seller, $totalAmount, $platformFee, $order, $description) {
                $wallet = $buyer->wallet;

                if (!$wallet) {
                    return [
                        'success' => false,
                        'message' => 'Wallet not found',
                    ];
                }

                $totalBalance = $wallet->withdrawable_balance + $wallet->promo_credit_balance;

                if ($totalBalance < $totalAmount) {
                    return [
                        'success' => false,
                        'message' => 'Insufficient balance',
                        'available' => $totalBalance,
                        'required' => $totalAmount,
                    ];
                }

                if (!$wallet->addToEscrow($totalAmount)) {
                    return [
                        'success' => false,
                        'message' => 'Failed to hold funds in escrow',
                    ];
                }

                $sellerPayout = round($totalAmount - $platformFee, 2);

                $escrow = EscrowTransaction::create([
                    'transaction_no' => 'ESC-' . Str::upper(Str::random(12)),
                    'order_id' => $order->id,
                    'order_type' => get_class($order),
                    'payer_id' => $buyer->id,
                    'payee_id' => $seller->id,
                    'amount' => $sellerPayout,
                    'platform_fee' => $platformFee,
                    'total_amount' => $totalAmount,
                    'status' => EscrowTransaction::STATUS_FUNDED,
                ]);

                Log::info('Funds held in escrow', [
                    'user_id' => $buyer->id,
                    'amount' => $totalAmount,
                    'order_type' => get_class($order),
                    'order_id' => $order->id,
                    'escrow_id' => $escrow->id,
                ]);

                return [
                    'success' => true,
                    'message' => 'Payment held in escrow',
                    'escrow_amount' => $totalAmount,
                    'escrow_transaction' => $escrow,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Escrow hold failed', [
                'user_id' => $buyer->id,
                'amount' => $totalAmount,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Release funds from escrow to seller
     */
    public function releaseEscrow(EscrowTransaction $escrow, string $description): array
    {
        try {
            return DB::transaction(function () use ($escrow, $description) {
                $payerWallet = Wallet::firstOrCreate(
                    ['user_id' => $escrow->payer_id],
                    [
                        'withdrawable_balance' => 0,
                        'promo_credit_balance' => 0,
                        'total_earned' => 0,
                        'total_spent' => 0,
                        'pending_balance' => 0,
                        'escrow_balance' => 0,
                    ]
                );

                if ($payerWallet->escrow_balance < $escrow->total_amount) {
                    return [
                        'success' => false,
                        'message' => 'Insufficient escrow balance',
                    ];
                }

                $payerWallet->escrow_balance = max(0, $payerWallet->escrow_balance - $escrow->total_amount);
                $payerWallet->save();

                $payeeWallet = Wallet::firstOrCreate(
                    ['user_id' => $escrow->payee_id],
                    [
                        'withdrawable_balance' => 0,
                        'promo_credit_balance' => 0,
                        'total_earned' => 0,
                        'total_spent' => 0,
                        'pending_balance' => 0,
                        'escrow_balance' => 0,
                    ]
                );

                $payeeWallet->addWithdrawable($escrow->amount, 'escrow_release', $description);

                $this->earnDeskService->recordTransaction(
                    $escrow->payee,
                    'marketplace_earning',
                    $escrow->amount,
                    $payeeWallet->id,
                    "Earning: {$description}"
                );

                $escrow->status = EscrowTransaction::STATUS_RELEASED;
                $escrow->released_at = now();
                $escrow->save();

                Log::info('Funds released from escrow', [
                    'payer_id' => $escrow->payer_id,
                    'payee_id' => $escrow->payee_id,
                    'amount' => $escrow->amount,
                    'order_type' => $escrow->order_type,
                    'order_id' => $escrow->order_id,
                    'escrow_id' => $escrow->id,
                ]);

                return [
                    'success' => true,
                    'message' => 'Payment released to seller',
                    'released_amount' => $escrow->amount,
                    'escrow_transaction' => $escrow,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Escrow release failed', [
                'escrow_id' => $escrow->id,
                'amount' => $escrow->amount,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to release payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Refund buyer from escrow
     */
    public function refundFromEscrow(EscrowTransaction $escrow, string $description): array
    {
        try {
            return DB::transaction(function () use ($escrow, $description) {
                $payerWallet = Wallet::firstOrCreate(
                    ['user_id' => $escrow->payer_id],
                    [
                        'withdrawable_balance' => 0,
                        'promo_credit_balance' => 0,
                        'total_earned' => 0,
                        'total_spent' => 0,
                        'pending_balance' => 0,
                        'escrow_balance' => 0,
                    ]
                );

                if ($payerWallet->escrow_balance < $escrow->total_amount) {
                    return [
                        'success' => false,
                        'message' => 'Insufficient escrow balance',
                    ];
                }

                $payerWallet->escrow_balance = max(0, $payerWallet->escrow_balance - $escrow->total_amount);
                $payerWallet->save();

                $payerWallet->addWithdrawable($escrow->total_amount, 'refund', $description);

                $this->earnDeskService->recordTransaction(
                    $escrow->payer,
                    'refund',
                    $escrow->total_amount,
                    $payerWallet->id,
                    "Refund: {$description}"
                );

                $escrow->status = EscrowTransaction::STATUS_CANCELLED;
                $escrow->save();

                Log::info('Funds refunded from escrow', [
                    'payer_id' => $escrow->payer_id,
                    'amount' => $escrow->total_amount,
                    'order_type' => $escrow->order_type,
                    'order_id' => $escrow->order_id,
                    'escrow_id' => $escrow->id,
                ]);

                return [
                    'success' => true,
                    'message' => 'Refund processed',
                    'refunded_amount' => $escrow->total_amount,
                    'escrow_transaction' => $escrow,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Escrow refund failed', [
                'escrow_id' => $escrow->id,
                'amount' => $escrow->total_amount,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process refund: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create a dispute for an order
     */
    public function createDispute(
        $order,
        User $raiser,
        string $reason
    ): array {
        try {
            $responderId = $raiser->id === $order->buyer_id 
                ? $order->seller_id 
                : $order->buyer_id;

            $dispute = Dispute::create([
                'disputable_type' => get_class($order),
                'disputable_id' => $order->id,
                'raiser_id' => $raiser->id,
                'responder_id' => $responderId,
                'reason' => $reason,
                'status' => Dispute::STATUS_OPEN,
            ]);

            // Update order status
            $order->update(['status' => $order::STATUS_DISPUTED]);

            Log::info('Dispute created', [
                'dispute_id' => $dispute->id,
                'order_id' => $order->id,
                'raiser_id' => $raiser->id,
            ]);

            return [
                'success' => true,
                'dispute' => $dispute,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create dispute: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Resolve a dispute
     */
    public function getEscrowTransaction($order): ?EscrowTransaction
    {
        return EscrowTransaction::where('order_id', $order->id)
            ->where('order_type', get_class($order))
            ->first();
    }

    public function resolveDispute(Dispute $dispute, string $resolution, ?string $notes = null): array
    {
        try {
            DB::transaction(function () use ($dispute, $resolution, $notes) {
                $order = $dispute->disputable;
                
                $dispute->update([
                    'status' => Dispute::STATUS_RESOLVED,
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
                    case Dispute::RESOLUTION_BUYER_WINS:
                        $this->refundFromEscrow(
                            $escrow,
                            'Dispute resolution - Buyer wins'
                        );
                        $order->update(['status' => $order::STATUS_REFUNDED]);
                        break;

                    case Dispute::RESOLUTION_SELLER_WINS:
                        $this->releaseEscrow(
                            $escrow,
                            'Dispute resolution - Seller wins'
                        );
                        $order->update(['status' => $order::STATUS_COMPLETED]);
                        break;

                    case Dispute::RESOLUTION_REFUND:
                        $this->refundFromEscrow(
                            $escrow,
                            'Dispute resolution - Refund'
                        );
                        $order->update(['status' => $order::STATUS_REFUNDED]);
                        break;

                    case Dispute::RESOLUTION_SPLIT:
                        // Full refund to buyer and full release to seller is not supported for partial splits in centralized escrow.
                        // As a fallback, treat split as seller wins to avoid leaving funds locked.
                        $this->releaseEscrow(
                            $escrow,
                            'Dispute resolution - Split payout'
                        );
                        $order->update(['status' => $order::STATUS_COMPLETED]);
                        break;
                }
            });

            return [
                'success' => true,
                'message' => 'Dispute resolved successfully',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to resolve dispute: ' . $e->getMessage(),
            ];
        }
    }
}
