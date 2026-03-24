<?php

namespace App\Http\Controllers;

use App\Models\RevenueReport;
use App\Services\RevenueAggregator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RevenueController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            abort(403, 'This action is unauthorized.');
        }

        $from = $request->query('from', now()->subMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $currency = $request->query('currency');

        $rows = RevenueReport::whereBetween('date', [$from, $to])
            ->when($currency, function ($q) use ($currency) { return $q->where('currency', $currency); })
            ->orderBy('date', 'desc')
            ->paginate(25);

        $totalsQuery = RevenueReport::whereBetween('date', [$from, $to]);
        if ($currency) {
            $totalsQuery = $totalsQuery->where('currency', $currency);
        }

        $totals = [
            'gross' => $totalsQuery->sum('gross_amount'),
            'gateway_fees' => $totalsQuery->sum('gateway_fees'),
            'refunds' => $totalsQuery->sum('refunds'),
            'payouts' => $totalsQuery->sum('worker_payouts'),
            'commissions' => $totalsQuery->sum('commissions_paid'),
            'taxes' => $totalsQuery->sum('taxes'),
            'net' => $totalsQuery->sum('platform_net'),
            'transaction_count' => $totalsQuery->sum('transaction_count'),
            'task_amount' => $totalsQuery->sum('task_amount'),
            'total_deposits' => $totalsQuery->sum('total_deposits'),
            'pending_withdrawals' => $totalsQuery->sum('pending_withdrawals'),
            'total_transactions_amount' => $totalsQuery->sum('total_transactions_amount'),
            'total_wallet_balance' => $totalsQuery->sum('total_wallet_balance'),
            'total_withdrawable_balance' => $totalsQuery->sum('total_withdrawable_balance'),
            'total_withdrawn' => $totalsQuery->sum('total_withdrawn'),
            'admin_deposits' => $totalsQuery->sum('admin_deposits'),
            'activation_fees' => $totalsQuery->sum('activation_fees'),
            'commission_fees' => $totalsQuery->sum('commission_fees'),
        ];

        return view('admin.revenue.index', compact('rows', 'totals', 'from', 'to', 'currency'));
    }

    public function refresh(Request $request)
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            abort(403, 'This action is unauthorized.');
        }

        $date = $request->input('date', now()->toDateString());
        RevenueAggregator::aggregateForDate($date);

        return back()->with('success', 'Revenue recomputed for ' . $date);
    }

    /**
     * Export aggregated revenue for the range as CSV
     */
    public function export(Request $request)
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            abort(403, 'This action is unauthorized.');
        }

        $from = $request->query('from', now()->subMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $currency = $request->query('currency');

        $query = RevenueReport::whereBetween('date', [$from, $to]);
        if ($currency) $query->where('currency', $currency);

        $filename = 'revenue_' . $from . '_' . $to . '.csv';

        $response = new StreamedResponse(function() use ($query) {
            $handle = fopen('php://output', 'w');
            // header
            fputcsv($handle, [
                'date','currency','gateway','gross_amount','gateway_fees','refunds','worker_payouts','commissions_paid','taxes','platform_net','transaction_count','task_amount','total_deposits','pending_withdrawals','total_transactions_amount','total_wallet_balance','total_withdrawable_balance','total_withdrawn','admin_deposits','activation_fees','commission_fees'
            ]);

            foreach ($query->orderBy('date','asc')->cursor() as $row) {
                fputcsv($handle, [
                    $row->date->toDateString(),
                    $row->currency,
                    $row->gateway,
                    (float)$row->gross_amount,
                    (float)$row->gateway_fees,
                    (float)$row->refunds,
                    (float)$row->worker_payouts,
                    (float)$row->commissions_paid,
                    (float)$row->taxes,
                    (float)$row->platform_net,
                    (int)$row->transaction_count,
                    (float)$row->task_amount,
                    (float)$row->total_deposits,
                    (float)$row->pending_withdrawals,
                    (float)$row->total_transactions_amount,
                    (float)$row->total_wallet_balance,
                    (float)$row->total_withdrawable_balance,
                    (float)$row->total_withdrawn,
                    (float)$row->admin_deposits,
                    (float)$row->activation_fees,
                    (float)$row->commission_fees,
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
