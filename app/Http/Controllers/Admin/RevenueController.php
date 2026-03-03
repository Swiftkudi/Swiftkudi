<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseLog;
use App\Services\RevenueAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;

/**
 * Controller for Admin Revenue Analytics Dashboard
 */
class RevenueController extends Controller
{
    /**
     * @var RevenueAnalyticsService
     */
    private $analyticsService;

    /**
     * Create a new controller instance.
     */
    public function __construct(RevenueAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Show the revenue analytics dashboard
     */
    public function index(Request $request): View
    {
        // Get date range from request or default to this month
        $startDate = $request->has('start_date') 
            ? Carbon::parse($request->start_date)->startOfDay()
            : today()->startOfMonth();
            
        $endDate = $request->has('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : today()->endOfDay();

        // Get dashboard summary
        $summary = $this->analyticsService->getDashboardSummary();

        // Get revenue breakdown
        $revenueBySource = $this->analyticsService->getRevenueBySource($startDate, $endDate);

        // Get expense breakdown
        $expensesByCategory = $this->analyticsService->getExpensesByCategory($startDate, $endDate);

        // Get activation stats
        $activationStats = $this->analyticsService->getActivationStats($startDate, $endDate);

        // Get daily breakdown for charts
        $dailyBreakdown = $this->analyticsService->getDailyBreakdown($startDate, $endDate);

        // Get activation trend
        $activationTrend = $this->analyticsService->getActivationTrend($startDate, $endDate);

        // Get alerts
        $alerts = $this->analyticsService->checkForAnomalies();

        // Get profit margin
        $profitMargin = $this->analyticsService->getProfitMargin($startDate, $endDate);

        return view('admin.revenue.index', compact(
            'summary',
            'revenueBySource',
            'expensesByCategory',
            'activationStats',
            'dailyBreakdown',
            'activationTrend',
            'alerts',
            'profitMargin',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get revenue data for AJAX requests
     */
    public function getRevenueData(Request $request): JsonResponse
    {
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $revenueBySource = $this->analyticsService->getRevenueBySource($startDate, $endDate);
        $expensesByCategory = $this->analyticsService->getExpensesByCategory($startDate, $endDate);
        $dailyBreakdown = $this->analyticsService->getDailyBreakdown($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => [
                'revenue_by_source' => $revenueBySource,
                'expenses_by_category' => $expensesByCategory,
                'daily_breakdown' => $dailyBreakdown,
            ],
        ]);
    }

    /**
     * Show expense logs
     */
    public function expenses(Request $request): View
    {
        $filters = $request->only(['type', 'start_date', 'end_date', 'status', 'per_page']);

        $expenses = $this->analyticsService->getExpenseLogs($filters);

        $expenseTypes = [
            'gateway_fee' => 'Gateway Fee',
            'server_cost' => 'Server/Hosting',
            'email_cost' => 'Email Cost',
            'sms_cost' => 'SMS Cost',
            'staff_cost' => 'Staff Cost',
            'referral_bonus' => 'Referral Bonus',
            'marketing' => 'Marketing',
            'operations' => 'Operations',
            'custom' => 'Custom',
        ];

        return view('admin.revenue.expenses', compact('expenses', 'expenseTypes', 'filters'));
    }

    /**
     * Create a new expense
     */
    public function createExpense(Request $request): JsonResponse
    {
        $request->validate([
            'expense_type' => 'required|string',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_type' => 'nullable|string',
        ]);

        try {
            $expense = $this->analyticsService->createExpense(
                $request->all(),
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Expense logged successfully',
                'data' => $expense,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log expense: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export financial report
     */
    public function export(Request $request)
    {
        $startDate = $request->has('start_date')
            ? Carbon::parse($request->start_date)
            : today()->startOfMonth();
            
        $endDate = $request->has('end_date')
            ? Carbon::parse($request->end_date)
            : today();

        $format = $request->get('format', 'csv');

        $filename = 'financial_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d');

        $data = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'revenue_by_source' => $this->analyticsService->getRevenueBySource($startDate, $endDate),
            'expenses_by_category' => $this->analyticsService->getExpensesByCategory($startDate, $endDate),
            'activation_stats' => $this->analyticsService->getActivationStats($startDate, $endDate),
            'daily_breakdown' => $this->analyticsService->getDailyBreakdown($startDate, $endDate),
            'total_revenue' => $this->analyticsService->getRevenue($startDate, $endDate),
            'total_expenses' => $this->analyticsService->getExpenses($startDate, $endDate),
            'net_profit' => $this->analyticsService->getNetProfit($startDate, $endDate),
        ];

        if ($format === 'excel') {
            return Excel::download(new FinancialReportExport($data), $filename . '.xlsx');
        }

        // CSV export
        return $this->exportCsv($data, $filename);
    }

    /**
     * Export as CSV
     */
    private function exportCsv(array $data, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            // Add header
            fputcsv($file, ['Date', 'Revenue', 'Expenses', 'Profit']);

            // Add daily data
            foreach ($data['daily_breakdown'] as $row) {
                fputcsv($file, [
                    $row['date'],
                    $row['revenue'],
                    $row['expense'],
                    $row['profit'],
                ]);
            }

            // Add totals
            fputcsv($file, ['']);
            fputcsv($file, ['TOTAL', $data['total_revenue'], $data['total_expenses'], $data['net_profit']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get activation logs
     */
    public function activations(Request $request): View
    {
        $startDate = $request->has('start_date')
            ? Carbon::parse($request->start_date)
            : today()->startOfMonth();
            
        $endDate = $request->has('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : today()->endOfDay();

        $activations = \App\Models\ActivationLog::with(['user', 'referrer'])
            ->dateRange($startDate, $endDate)
            ->orderBy('activated_at', 'desc')
            ->paginate(20);

        return view('admin.revenue.activations', compact('activations', 'startDate', 'endDate'));
    }

    /**
     * Get real-time stats (for dashboard widgets)
     */
    public function getQuickStats(): JsonResponse
    {
        $summary = $this->analyticsService->getDashboardSummary();

        return response()->json([
            'success' => true,
            'data' => [
                'today_revenue' => $summary['today']['revenue'],
                'today_expenses' => $summary['today']['expenses'],
                'today_profit' => $summary['today']['net_profit'],
                'monthly_revenue' => $summary['monthly']['revenue'],
                'monthly_expenses' => $summary['monthly']['expenses'],
                'monthly_profit' => $summary['monthly']['net_profit'],
            ],
        ]);
    }

    /**
     * Refresh revenue dashboard data endpoint (supports both page and AJAX).
     */
    public function refresh(Request $request)
    {
        $startDate = $request->has('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : today()->startOfMonth();

        $endDate = $request->has('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : today()->endOfDay();

        $summary = $this->analyticsService->getDashboardSummary();
        $revenueBySource = $this->analyticsService->getRevenueBySource($startDate, $endDate);
        $expensesByCategory = $this->analyticsService->getExpensesByCategory($startDate, $endDate);
        $activationStats = $this->analyticsService->getActivationStats($startDate, $endDate);
        $dailyBreakdown = $this->analyticsService->getDailyBreakdown($startDate, $endDate);
        $activationTrend = $this->analyticsService->getActivationTrend($startDate, $endDate);
        $alerts = $this->analyticsService->checkForAnomalies();
        $profitMargin = $this->analyticsService->getProfitMargin($startDate, $endDate);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Revenue dashboard refreshed successfully.',
                'data' => [
                    'summary' => $summary,
                    'revenue_by_source' => $revenueBySource,
                    'expenses_by_category' => $expensesByCategory,
                    'activation_stats' => $activationStats,
                    'daily_breakdown' => $dailyBreakdown,
                    'activation_trend' => $activationTrend,
                    'alerts' => $alerts,
                    'profit_margin' => $profitMargin,
                ],
            ]);
        }

        return view('admin.revenue.index', compact(
            'summary',
            'revenueBySource',
            'expensesByCategory',
            'activationStats',
            'dailyBreakdown',
            'activationTrend',
            'alerts',
            'profitMargin',
            'startDate',
            'endDate'
        ))->with('success', 'Revenue dashboard refreshed successfully.');
    }
}
