<?php

namespace App\Http\Controllers;

use App\Models\RevenueReport;
use App\Models\Transaction;
use Illuminate\Http\Request;

class RevenueApiController extends Controller
{
    public function chartData(Request $request)
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        $from = $request->query('from', now()->subDays(30)->toDateString());
        $to = $request->query('to', now()->toDateString());

        $rows = RevenueReport::whereBetween('date', [$from, $to])
            ->orderBy('date', 'asc')
            ->get()
            ->groupBy('date')
            ->map(function($group) {
                return [
                    'date' => $group->first()->date->toDateString(),
                    'gross' => (float) $group->sum('gross_amount'),
                    'net' => (float) $group->sum('platform_net'),
                ];
            })->values();

        return response()->json($rows);
    }

    /**
     * Drilldown data for a specific date
     */
    public function drilldown(Request $request)
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        $date = $request->query('date', now()->toDateString());

        // Per-gateway aggregates from revenue_reports
        $gatewayRows = RevenueReport::where('date', $date)
            ->select('gateway', 'currency', 'gross_amount', 'gateway_fees', 'refunds', 'worker_payouts', 'platform_net')
            ->get()
            ->map(function($r) {
                return [
                    'gateway' => $r->gateway,
                    'currency' => $r->currency,
                    'gross' => (float) $r->gross_amount,
                    'gateway_fees' => (float) $r->gateway_fees,
                    'refunds' => (float) $r->refunds,
                    'worker_payouts' => (float) $r->worker_payouts,
                    'net' => (float) $r->platform_net,
                ];
            });

        // Recent transactions for that day (limit 200)
        $start = \Carbon\Carbon::parse($date)->startOfDay();
        $end = \Carbon\Carbon::parse($date)->endOfDay();

        $transactions = Transaction::whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get(['id','user_id','type','amount','currency','payment_method','status','created_at'])
            ->map(function($t) {
                return [
                    'id' => $t->id,
                    'user_id' => $t->user_id,
                    'type' => $t->type,
                    'amount' => (float) $t->amount,
                    'currency' => $t->currency,
                    'gateway' => $t->payment_method,
                    'status' => $t->status,
                    'created_at' => $t->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'date' => $date,
            'gateways' => $gatewayRows,
            'transactions' => $transactions,
        ]);
    }
}
