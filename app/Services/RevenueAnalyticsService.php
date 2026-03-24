<?php

namespace App\Services;

use App\Models\ActivationLog;
use App\Models\ExpenseLog;
use App\Models\FinancialTransaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for Revenue Analytics and Financial Reporting
 */
class RevenueAnalyticsService
{
    /**
     * Get dashboard summary data
     */
    public function getDashboardSummary(): array
    {
        // Use explicit start/end bounds so whereBetween queries cover full days
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        $startOfMonth = Carbon::today()->startOfMonth()->startOfDay();
        $allTimeStart = Carbon::create(2000, 1, 1)->startOfDay();

        // Today's stats (full day)
        $todayRevenue = $this->getRevenue($todayStart, $todayEnd);
        $todayExpenses = $this->getExpenses($todayStart, $todayEnd);
        $todayActivations = $this->getActivationStats($todayStart, $todayEnd);

        // Monthly stats (from start of month until end of today)
        $monthlyRevenue = $this->getRevenue($startOfMonth, $todayEnd);
        $monthlyExpenses = $this->getExpenses($startOfMonth, $todayEnd);
        $monthlyActivations = $this->getActivationStats($startOfMonth, $todayEnd);

        // Lifetime stats (all-time until end of today)
        $lifetimeRevenue = $this->getRevenue($allTimeStart, $todayEnd);
        $lifetimeExpenses = $this->getExpenses($allTimeStart, $todayEnd);
        $lifetimeActivations = $this->getActivationStats($allTimeStart, $todayEnd);
        $walletTotalEarned = (float) Wallet::query()->sum('total_earned');

        return [
            'today' => [
                'revenue' => $todayRevenue,
                'expenses' => $todayExpenses,
                'net_profit' => $todayRevenue - $todayExpenses,
                'activations' => $todayActivations,
            ],
            'monthly' => [
                'revenue' => $monthlyRevenue,
                'expenses' => $monthlyExpenses,
                'net_profit' => $monthlyRevenue - $monthlyExpenses,
                'activations' => $monthlyActivations,
            ],
            'lifetime' => [
                'revenue' => $lifetimeRevenue,
                'expenses' => $lifetimeExpenses,
                'net_profit' => $lifetimeRevenue - $lifetimeExpenses,
                'activations' => $lifetimeActivations,
            ],
            'wallet_total_earned' => $walletTotalEarned,
        ];
    }

    /**
     * Get revenue breakdown by source
     */
    public function getRevenueBySource(Carbon $startDate, Carbon $endDate): array
    {
        // Activation revenue
        $activationRevenue = ActivationLog::completed()
            ->dateRange($startDate, $endDate)
            ->sum('platform_revenue');

        // Other revenue from financial transactions
        $transactions = FinancialTransaction::completed()
            ->revenue()
            ->where('category', '!=', FinancialTransaction::CAT_ACTIVATION)
            ->dateRange($startDate, $endDate)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category')
            ->toArray();

        return [
            'activation' => $activationRevenue,
            'task_creation' => $transactions[FinancialTransaction::CAT_TASK_CREATION] ?? 0,
            'affiliate_commission' => $transactions[FinancialTransaction::CAT_AFFILIATE_COMMISSION] ?? 0,
            'withdrawal_fee' => $transactions[FinancialTransaction::CAT_WITHDRAWAL_FEE] ?? 0,
            'advertising' => $transactions[FinancialTransaction::CAT_ADVERTISING] ?? 0,
            'marketplace' => $transactions[FinancialTransaction::CAT_MARKETPLACE] ?? 0,
            'other' => $transactions[FinancialTransaction::CAT_OTHER_REVENUE] ?? 0,
            'total' => $activationRevenue + array_sum($transactions),
        ];
    }

    /**
     * Get expenses breakdown by category
     */
    public function getExpensesByCategory(Carbon $startDate, Carbon $endDate): array
    {
        $expenses = ExpenseLog::approved()
            ->dateRange($startDate, $endDate)
            ->select('expense_type', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_type')
            ->get()
            ->pluck('total', 'expense_type')
            ->toArray();

        // Also get referral bonuses from activation logs
        $referralBonus = ActivationLog::completed()
            ->dateRange($startDate, $endDate)
            ->sum('referral_bonus');

        $manualReferralBonus = $expenses[ExpenseLog::TYPE_REFERRAL_BONUS] ?? 0;
        $combinedReferralBonus = $referralBonus + $manualReferralBonus;

        $nonReferralExpenseTotal = array_sum($expenses) - $manualReferralBonus;

        return [
            'gateway_fees' => $expenses[ExpenseLog::TYPE_GATEWAY_FEE] ?? 0,
            'server_cost' => $expenses[ExpenseLog::TYPE_SERVER_COST] ?? 0,
            'email_cost' => $expenses[ExpenseLog::TYPE_EMAIL_COST] ?? 0,
            'sms_cost' => $expenses[ExpenseLog::TYPE_SMS_COST] ?? 0,
            'staff_cost' => $expenses[ExpenseLog::TYPE_STAFF_COST] ?? 0,
            'marketing' => $expenses[ExpenseLog::TYPE_MARKETING] ?? 0,
            'operations' => $expenses[ExpenseLog::TYPE_OPERATIONS] ?? 0,
            'referral_bonus' => $combinedReferralBonus,
            'custom' => $expenses[ExpenseLog::TYPE_CUSTOM] ?? 0,
            'total' => $nonReferralExpenseTotal + $combinedReferralBonus,
        ];
    }

    /**
     * Get activation statistics
     */
    public function getActivationStats(Carbon $startDate, Carbon $endDate): array
    {
        $activations = ActivationLog::completed()->dateRange($startDate, $endDate);

        return [
            'total' => $activations->count(),
            'normal' => $activations->normal()->count(),
            'referral' => $activations->referral()->count(),
            'revenue' => $activations->sum('platform_revenue'),
            'referral_bonus' => $activations->sum('referral_bonus'),
            'net_profit' => $activations->sum('platform_revenue') - $activations->sum('referral_bonus'),
        ];
    }

    /**
     * Get revenue for a period
     */
    public function getRevenue(Carbon $startDate, Carbon $endDate): float
    {
        $activationRevenue = ActivationLog::completed()
            ->dateRange($startDate, $endDate)
            ->sum('platform_revenue');

        $transactionRevenue = FinancialTransaction::completed()
            ->revenue()
            ->where('category', '!=', FinancialTransaction::CAT_ACTIVATION)
            ->dateRange($startDate, $endDate)
            ->sum('amount');

        return $activationRevenue + $transactionRevenue;
    }

    /**
     * Get expenses for a period
     */
    public function getExpenses(Carbon $startDate, Carbon $endDate): float
    {
        $expenseLogTotal = ExpenseLog::approved()
            ->dateRange($startDate, $endDate)
            ->where('expense_type', '!=', ExpenseLog::TYPE_REFERRAL_BONUS)
            ->sum('amount');

        $manualReferralBonus = ExpenseLog::approved()
            ->dateRange($startDate, $endDate)
            ->where('expense_type', ExpenseLog::TYPE_REFERRAL_BONUS)
            ->sum('amount');

        $referralBonus = ActivationLog::completed()
            ->dateRange($startDate, $endDate)
            ->sum('referral_bonus');

        return $expenseLogTotal + $referralBonus + $manualReferralBonus;
    }

    /**
     * Get net profit for a period
     */
    public function getNetProfit(Carbon $startDate, Carbon $endDate): float
    {
        return $this->getRevenue($startDate, $endDate) - $this->getExpenses($startDate, $endDate);
    }

    /**
     * Get daily revenue/expense for charting
     */
    public function getDailyBreakdown(Carbon $startDate, Carbon $endDate): Collection
    {
        $days = $startDate->diffInDays($endDate) + 1;
        
        $revenueData = FinancialTransaction::completed()
            ->revenue()
            ->where('category', '!=', FinancialTransaction::CAT_ACTIVATION)
            ->dateRange($startDate, $endDate)
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('date')
            ->pluck('revenue', 'date')
            ->toArray();

        $activationRevenueData = ActivationLog::completed()
            ->dateRange($startDate, $endDate)
            ->select(
                DB::raw('DATE(activated_at) as date'),
                DB::raw('SUM(platform_revenue) as revenue')
            )
            ->groupBy('date')
            ->pluck('revenue', 'date')
            ->toArray();

        $expenseData = ExpenseLog::approved()
            ->dateRange($startDate, $endDate)
            ->where('expense_type', '!=', ExpenseLog::TYPE_REFERRAL_BONUS)
            ->select(
                DB::raw('DATE(expense_date) as date'),
                DB::raw('SUM(amount) as expense')
            )
            ->groupBy('date')
            ->pluck('expense', 'date')
            ->toArray();

        $manualReferralExpenseData = ExpenseLog::approved()
            ->dateRange($startDate, $endDate)
            ->where('expense_type', ExpenseLog::TYPE_REFERRAL_BONUS)
            ->select(
                DB::raw('DATE(expense_date) as date'),
                DB::raw('SUM(amount) as expense')
            )
            ->groupBy('date')
            ->pluck('expense', 'date')
            ->toArray();

        $activationReferralExpenseData = ActivationLog::completed()
            ->dateRange($startDate, $endDate)
            ->select(
                DB::raw('DATE(activated_at) as date'),
                DB::raw('SUM(referral_bonus) as expense')
            )
            ->groupBy('date')
            ->pluck('expense', 'date')
            ->toArray();

        // Build complete date range with zeros
        $result = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();
            $dayRevenue = ($revenueData[$dateStr] ?? 0) + ($activationRevenueData[$dateStr] ?? 0);
            $dayExpense = ($expenseData[$dateStr] ?? 0) + ($manualReferralExpenseData[$dateStr] ?? 0) + ($activationReferralExpenseData[$dateStr] ?? 0);
            $result[] = [
                'date' => $dateStr,
                'revenue' => $dayRevenue,
                'expense' => $dayExpense,
                'profit' => $dayRevenue - $dayExpense,
            ];
            $current->addDay();
        }

        return collect($result);
    }

    /**
     * Get activation trend for charting
     */
    public function getActivationTrend(Carbon $startDate, Carbon $endDate): Collection
    {
        return ActivationLog::completed()
            ->dateRange($startDate, $endDate)
            ->select(
                DB::raw('DATE(activated_at) as date'),
                DB::raw('COUNT(*) as activations'),
                DB::raw('SUM(platform_revenue) as revenue'),
                DB::raw('SUM(referral_bonus) as bonus')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get revenue by gateway
     */
    public function getRevenueByGateway(Carbon $startDate, Carbon $endDate): array
    {
        return FinancialTransaction::completed()
            ->revenue()
            ->dateRange($startDate, $endDate)
            ->select('payment_gateway', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_gateway')
            ->pluck('total', 'payment_gateway')
            ->toArray();
    }

    /**
     * Get expense logs with filters
     */
    public function getExpenseLogs(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = ExpenseLog::with('creator');

        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('expense_date', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Create a new expense log
     */
    public function createExpense(array $data, int $adminId): ExpenseLog
    {
        return ExpenseLog::create([
            'expense_type' => $data['expense_type'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'NGN',
            'payment_gateway' => $data['payment_gateway'] ?? null,
            'created_by' => $adminId,
            'expense_date' => $data['expense_date'] ?? today(),
            'status' => $data['status'] ?? ExpenseLog::STATUS_APPROVED,
            'notes' => $data['notes'] ?? null,
            'attachment_url' => $data['attachment_url'] ?? null,
            'is_recurring' => $data['is_recurring'] ?? false,
            'recurring_type' => $data['recurring_type'] ?? null,
        ]);
    }

    /**
     * Log an activation (called when user activates wallet)
     */
    public function logActivation(
        int $userId,
        float $activationFee,
        float $referralBonus,
        ?int $referrerId = null,
        array $metadata = []
    ): ActivationLog {
        return ActivationLog::create([
            'user_id' => $userId,
            'referrer_id' => $referrerId,
            'activation_fee' => $activationFee,
            'referral_bonus' => $referralBonus,
            'platform_revenue' => max(0, $activationFee - $referralBonus),
            'payment_method' => $metadata['payment_method'] ?? null,
            'payment_gateway' => $metadata['payment_gateway'] ?? null,
            'reference' => $metadata['reference'] ?? null,
            'status' => ActivationLog::STATUS_COMPLETED,
            'activation_type' => $referrerId ? ActivationLog::TYPE_REFERRAL : ActivationLog::TYPE_NORMAL,
            'activated_at' => now(),
        ]);
    }

    /**
     * Calculate profit margin
     */
    public function getProfitMargin(Carbon $startDate, Carbon $endDate): float
    {
        $revenue = $this->getRevenue($startDate, $endDate);
        $profit = $this->getNetProfit($startDate, $endDate);

        if ($revenue <= 0) {
            return 0;
        }

        return ($profit / $revenue) * 100;
    }

    /**
     * Get anomaly alerts
     */
    public function checkForAnomalies(): array
    {
        $alerts = [];
        $today = today();
        $yesterday = today()->subDay();
        $lastWeek = today()->subWeek();

        // Check for profit drop
        $todayProfit = $this->getNetProfit($today, $today);
        $yesterdayProfit = $this->getNetProfit($yesterday, $yesterday);
        
        if ($yesterdayProfit > 0 && $todayProfit < ($yesterdayProfit * 0.5)) {
            $alerts[] = [
                'type' => 'profit_drop',
                'severity' => 'high',
                'message' => 'Profit dropped by more than 50% compared to yesterday',
            ];
        }

        // Check for unusual expenses
        $avgDailyExpense = $this->getExpenses($lastWeek, $yesterday) / 7;
        $todayExpense = $this->getExpenses($today, $today);
        
        if ($todayExpense > ($avgDailyExpense * 3)) {
            $alerts[] = [
                'type' => 'expense_spike',
                'severity' => 'medium',
                'message' => 'Today\'s expenses are 3x higher than average',
            ];
        }

        return $alerts;
    }
}
