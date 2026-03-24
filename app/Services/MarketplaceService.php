<?php

namespace App\Services;

use App\Models\Dispute;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public function holdInEscrow(User $buyer, float $amount, string $orderType, int $orderId, string $description): array
    {
        try {
            return DB::transaction(function () use ($buyer, $amount, $orderType, $orderId, $description) {
                $wallet = $buyer->wallet;
                
                if (!$wallet) {
                    return [
                        'success' => false,
                        'message' => 'Wallet not found',
                    ];
                }

                $totalBalance = $wallet->withdrawable_balance + $wallet->promo_credit_balance;
                
                if ($totalBalance < $amount) {
                    return [
                        'success' => false,
                        'message' => 'Insufficient balance',
                        'available' => $totalBalance,
                        'required' => $amount,
                    ];
                }

                // Use withdrawable balance first
                $withdrawableUsed = min($amount, $wallet->withdrawable_balance);
                $promoUsed = $amount - $withdrawableUsed;

                if ($withdrawableUsed > 0) {
                    $wallet->decrement('withdrawable_balance', $withdrawableUsed);
                    
                    // Record transaction
                    $this->earnDeskService->recordTransaction(
                        $buyer,
                        'marketplace_payment',
                        -$withdrawableUsed,
                        $wallet->id,
                        "Payment held: {$description}"
                    );
                }

                if ($promoUsed > 0) {
                    $wallet->decrement('promo_credit_balance', $promoUsed);
                }

                // Add to escrow balance
                $wallet->addToEscrow($amount);

                Log::info('Funds held in escrow', [
                    'user_id' => $buyer->id,
                    'amount' => $amount,
                    'order_type' => $orderType,
                    'order_id' => $orderId,
                ]);

                return [
                    'success' => true,
                    'message' => 'Payment held in escrow',
                    'escrow_amount' => $amount,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Escrow hold failed', [
                'user_id' => $buyer->id,
                'amount' => $amount,
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
    public function releaseEscrow(User $seller, float $amount, string $orderType, int $orderId, string $description): array
    {
        try {
            return DB::transaction(function () use ($seller, $amount, $orderType, $orderId, $description) {
                $wallet = $seller->wallet;
                
                if (!$wallet) {
                    return [
                        'success' => false,
                        'message' => 'Wallet not found',
                    ];
                }

                // Remove from escrow
                if (!$wallet->releaseFromEscrow($amount)) {
                    return [
                        'success' => false,
                        'message' => 'Insufficient escrow balance',
                    ];
                }

                // Add to seller's withdrawable balance
                $wallet->addWithdrawable($amount, 'escrow_release');

                // Record transaction
                $this->earnDeskService->recordTransaction(
                    $seller,
                    'marketplace_earning',
                    $amount,
                    $wallet->id,
                    "Earning: {$description}"
                );

                Log::info('Funds released from escrow', [
                    'user_id' => $seller->id,
                    'amount' => $amount,
                    'order_type' => $orderType,
                    'order_id' => $orderId,
                ]);

                return [
                    'success' => true,
                    'message' => 'Payment released to seller',
                    'released_amount' => $amount,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Escrow release failed', [
                'user_id' => $seller->id,
                'amount' => $amount,
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
    public function refundFromEscrow(User $buyer, float $amount, string $orderType, int $orderId, string $description): array
    {
        try {
            return DB::transaction(function () use ($buyer, $amount, $orderType, $orderId, $description) {
                $wallet = $buyer->wallet;
                
                if (!$wallet) {
                    return [
                        'success' => false,
                        'message' => 'Wallet not found',
                    ];
                }

                // Remove from escrow
                if (!$wallet->releaseFromEscrow($amount)) {
                    return [
                        'success' => false,
                        'message' => 'Insufficient escrow balance',
                    ];
                }

                // Refund to buyer's withdrawable balance
                $wallet->addWithdrawable($amount, 'refund');

                // Record transaction
                $this->earnDeskService->recordTransaction(
                    $buyer,
                    'refund',
                    $amount,
                    $wallet->id,
                    "Refund: {$description}"
                );

                Log::info('Funds refunded from escrow', [
                    'user_id' => $buyer->id,
                    'amount' => $amount,
                    'order_type' => $orderType,
                    'order_id' => $orderId,
                ]);

                return [
                    'success' => true,
                    'message' => 'Refund processed',
                    'refunded_amount' => $amount,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Escrow refund failed', [
                'user_id' => $buyer->id,
                'amount' => $amount,
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

                switch ($resolution) {
                    case Dispute::RESOLUTION_BUYER_WINS:
                        // Refund buyer
                        $this->refundFromEscrow(
                            $order->buyer,
                            $order->escrow_amount,
                            get_class($order),
                            $order->id,
                            'Dispute resolution - Buyer wins'
                        );
                        $order->update(['status' => $order::STATUS_REFUNDED]);
                        break;

                    case Dispute::RESOLUTION_SELLER_WINS:
                        // Release to seller
                        $this->releaseEscrow(
                            $order->seller,
                            $order->escrow_amount,
                            get_class($order),
                            $order->id,
                            'Dispute resolution - Seller wins'
                        );
                        $order->update(['status' => $order::STATUS_COMPLETED]);
                        break;

                    case Dispute::RESOLUTION_REFUND:
                        // Full refund to buyer
                        $this->refundFromEscrow(
                            $order->buyer,
                            $order->escrow_amount,
                            get_class($order),
                            $order->id,
                            'Dispute resolution - Refund'
                        );
                        $order->update(['status' => $order::STATUS_REFUNDED]);
                        break;

                    case Dispute::RESOLUTION_SPLIT:
                        // Split refund (50/50 for simplicity)
                        $halfAmount = $order->escrow_amount / 2;
                        $this->refundFromEscrow(
                            $order->buyer,
                            $halfAmount,
                            get_class($order),
                            $order->id,
                            'Dispute resolution - Split refund'
                        );
                        $this->releaseEscrow(
                            $order->seller,
                            $halfAmount,
                            get_class($order),
                            $order->id,
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
