<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\RevenueReport;
use App\Models\Withdrawal;
use App\Models\WalletLedger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RevenueAggregator
{
    /**
     * Aggregate revenue for a single date (YYYY-MM-DD)
     */
    public static function aggregateForDate(string $date)
    {
        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();

        // Base transactions considered as revenue-generating: deposits and task payments
        $transactions = Transaction::whereIn('type', [Transaction::TYPE_DEPOSIT, Transaction::TYPE_TASK_PAYMENT])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end]);

        // Group by currency and gateway
        // NOTE: avoid using JSON_EXTRACT in SQL because metadata schema varies between installations.
        $grouped = $transactions->selectRaw('currency, payment_method as gateway, COUNT(*) as transaction_count, SUM(amount) as gross_amount, SUM(CASE WHEN type = "'.Transaction::TYPE_TASK_PAYMENT.'" THEN amount ELSE 0 END) as task_amount')
            ->groupBy('currency', 'gateway')
            ->get();

        $seenCurrency = [];

        foreach ($grouped as $row) {
            $currency = $row->currency;
            $gateway = $row->gateway;
            $gross = (float) $row->gross_amount;
            $transactionCount = (int) $row->transaction_count;
            $taskAmount = (float) $row->task_amount;

            // Compute gateway fees in PHP to avoid SQL JSON issues
            $gatewayFees = Transaction::whereIn('type', [Transaction::TYPE_DEPOSIT, Transaction::TYPE_TASK_PAYMENT])
                ->where('status', 'completed')
                ->whereBetween('created_at', [$start, $end])
                ->where('currency', $currency)
                ->where('payment_method', $gateway)
                ->get()
                ->sum(function($t) {
                    return isset($t->metadata['gateway_fee']) ? (float) $t->metadata['gateway_fee'] : 0.0;
                });

            // Refunds
            $refunds = Transaction::where('type', Transaction::TYPE_REFUND)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$start, $end])
                ->where('currency', $currency)
                ->where('payment_method', $gateway)
                ->sum('amount');

            // Worker payouts (task rewards payouts out of platform)
            $workerPayouts = Transaction::where('type', Transaction::TYPE_TASK_REWARD)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$start, $end])
                ->where('currency', $currency)
                ->where('payment_method', $gateway)
                ->sum('amount');

            // Total deposits for the day (could be higher than gross if there are deposit-only flows)
            $totalDeposits = Transaction::where('type', Transaction::TYPE_DEPOSIT)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$start, $end])
                ->where('currency', $currency)
                ->where('payment_method', $gateway)
                ->sum('amount');

            // Pending withdrawals amount
            $pendingWithdrawals = Withdrawal::where('status', Withdrawal::STATUS_PENDING)
                ->where('currency', $currency)
                ->sum('amount');

            // Total transactions amount (all transactions completed on that day)
            $totalTransactionsAmount = Transaction::where('status', 'completed')
                ->whereBetween('created_at', [$start, $end])
                ->where('currency', $currency)
                ->where('payment_method', $gateway)
                ->sum('amount');

            // Wallet totals snapshot: sum of all wallet balances in the system for this currency
            // Some installations may not have a 'currency' column on wallets; check before filtering
            if (!isset($seenCurrency[$currency])) {
                if (Schema::hasColumn('wallets', 'currency')) {
                    $totalWalletBalance = \App\Models\Wallet::where('currency', $currency)->sum(DB::raw('COALESCE(withdrawable_balance,0) + COALESCE(promo_credit_balance,0) + COALESCE(escrow_balance,0)'));
                    $totalWithdrawableBalance = \App\Models\Wallet::where('currency', $currency)->sum('withdrawable_balance');
                } else {
                    // fallback: sum across all wallets
                    $totalWalletBalance = \App\Models\Wallet::sum(DB::raw('COALESCE(withdrawable_balance,0) + COALESCE(promo_credit_balance,0) + COALESCE(escrow_balance,0)'));
                    $totalWithdrawableBalance = \App\Models\Wallet::sum('withdrawable_balance');
                }
            } else {
                $totalWalletBalance = 0;
                $totalWithdrawableBalance = 0;
            }

            // Total withdrawn (completed withdrawals)
            $totalWithdrawn = Withdrawal::where('status', Withdrawal::STATUS_COMPLETED)
                ->whereBetween('created_at', [$start, $end])
                ->where('currency', $currency)
                ->sum('amount');

            // Admin deposits: transactions created by admin users (if user_id refers to admin)
            $adminDeposits = Transaction::where('type', Transaction::TYPE_DEPOSIT)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$start, $end])
                ->where('currency', $currency)
                ->where('payment_method', $gateway)
                ->whereIn('user_id', function($q){ $q->select('id')->from('users')->where('is_admin', true); })
                ->sum('amount');

            // Activation fees and commission fees approximated from wallet ledger or transactions with specific types
            if (!isset($seenCurrency[$currency])) {
                $activationFees = WalletLedger::where('type', WalletLedger::TYPE_ACTIVATION)
                    ->whereBetween('created_at', [$start, $end])
                    ->where('currency', $currency)
                    ->sum('amount');
            } else {
                $activationFees = 0;
            }

            $commissionFees = Transaction::where('type', Transaction::TYPE_FEE)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$start, $end])
                ->where('currency', $currency)
                ->where('payment_method', $gateway)
                ->sum('amount');

            // Commissions and taxes (if stored separately) — attempt to read from metadata
            $commissions = 0;
            $taxes = 0;

            $platformNet = $gross - abs($gatewayFees) - abs($refunds) - abs($workerPayouts) - abs($commissions) - abs($taxes);

            RevenueReport::updateOrCreate(
                ['date' => $start->toDateString(), 'currency' => $currency, 'gateway' => $gateway],
                [
                    'gross_amount' => $gross,
                    'gateway_fees' => $gatewayFees,
                    'refunds' => abs($refunds),
                    'worker_payouts' => abs($workerPayouts),
                    'commissions_paid' => $commissions,
                    'taxes' => $taxes,
                    'platform_net' => $platformNet,
                    'transaction_count' => $transactionCount,
                    'task_amount' => $taskAmount,
                    'total_deposits' => $totalDeposits,
                    'pending_withdrawals' => $pendingWithdrawals,
                    'total_transactions_amount' => $totalTransactionsAmount,
                    'total_wallet_balance' => $totalWalletBalance,
                    'total_withdrawable_balance' => $totalWithdrawableBalance,
                    'total_withdrawn' => $totalWithdrawn,
                    'admin_deposits' => $adminDeposits,
                    'activation_fees' => $activationFees,
                    'commission_fees' => $commissionFees,
                    'meta' => [
                        'computed_at' => now()->toDateTimeString(),
                    ],
                ]
            );

            $seenCurrency[$currency] = true;
        }

        // Broadcast a simple summary for realtime admin UI
        try {
            event(new \App\Events\RevenueUpdated([
                'date' => $start->toDateString(),
                'totals' => [
                    'gross' => (float) $grouped->sum('gross_amount'),
                    'net' => (float) RevenueReport::where('date', $start->toDateString())->sum('platform_net'),
                ],
            ]));
        } catch (\Exception $e) {
            // broadcasting is optional — swallow errors so aggregation still succeeds
        }

        return true;
    }
}
