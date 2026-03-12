@extends('layouts.admin')

@section('title', 'Revenue Analytics - SwiftKudi')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-dark-950">
    <!-- Page Header -->
    <div class="bg-white dark:bg-dark-900 border-b border-gray-200 dark:border-dark-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Revenue Analytics</h1>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">Platform financial performance overview</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <button onclick="exportReport('csv')" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-download mr-2"></i>Export CSV
                    </button>
                    <button onclick="exportReport('excel')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-file-excel mr-2"></i>Export Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    @if(count($alerts) > 0)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        @foreach($alerts as $alert)
        <div class="mb-3 p-4 rounded-lg {{ $alert['severity'] === 'high' ? 'bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30' : 'bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/30' }}">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle mt-0.5 mr-3 {{ $alert['severity'] === 'high' ? 'text-red-500' : 'text-yellow-500' }}"></i>
                <div>
                    <p class="font-medium {{ $alert['severity'] === 'high' ? 'text-red-800 dark:text-red-300' : 'text-yellow-800 dark:text-yellow-300' }}">{{ $alert['message'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Filters -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <form method="GET" class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-4">
            <div class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                        class="px-4 py-2 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                        class="px-4 py-2 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Apply Filter
                </button>
            </div>
        </form>

        <div class="mt-4 bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-red-200 dark:border-red-500/30 p-4">
            <h3 class="text-sm font-semibold text-red-700 dark:text-red-300 mb-3">Danger Zone (Admin Only)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <form method="POST" action="{{ route('admin.revenue.clear-system-revenue') }}" onsubmit="return confirm('This will clear SYSTEM REVENUE records and analytics totals. Type carefully and proceed only if you are sure.')" class="flex gap-2 items-center">
                    @csrf
                    <input type="text" name="confirm_text" placeholder="Type CLEAR_REVENUE" class="flex-1 px-3 py-2 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-lg text-sm" required>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Clear System Revenue
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.revenue.clear-total-earned') }}" onsubmit="return confirm('This will reset total earned for ALL users to 0. Continue?')" class="flex gap-2 items-center">
                    @csrf
                    <input type="text" name="confirm_text" placeholder="Type CLEAR_EARNINGS" class="flex-1 px-3 py-2 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-lg text-sm" required>
                    <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Clear Total Earned
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-4 bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Revenue Reset History</h3>
            @if($revenueResetHistory->count() === 0)
                <p class="text-sm text-gray-500 dark:text-gray-400">No revenue reset actions recorded yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-dark-700 text-left text-gray-500 dark:text-gray-400">
                                <th class="py-2 pr-4">Time</th>
                                <th class="py-2 pr-4">Admin</th>
                                <th class="py-2 pr-4">Action</th>
                                <th class="py-2 pr-4">Details</th>
                                <th class="py-2">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($revenueResetHistory as $log)
                                @php
                                    $details = json_decode($log->new_value ?? '{}', true);
                                    $actionLabel = $log->setting_key === 'revenue.clear_system_revenue'
                                        ? 'Cleared System Revenue'
                                        : 'Cleared Total Earned';
                                @endphp
                                <tr class="border-b border-gray-100 dark:border-dark-800 last:border-0 text-gray-700 dark:text-gray-300">
                                    <td class="py-2 pr-4 whitespace-nowrap">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="py-2 pr-4">{{ $log->admin->name ?? $log->admin->email ?? 'Unknown Admin' }}</td>
                                    <td class="py-2 pr-4">{{ $actionLabel }}</td>
                                    <td class="py-2 pr-4">
                                        @if(is_array($details) && count($details) > 0)
                                            {{ collect($details)->map(fn($value, $key) => $key . ': ' . $value)->implode(', ') }}
                                        @else
                                            {{ $log->masked_new_value }}
                                        @endif
                                    </td>
                                    <td class="py-2">{{ $log->ip_address ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($revenueResetHistory->hasPages())
                    <div class="mt-3">
                        {{ $revenueResetHistory->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Today Revenue -->
            <div class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Today's Revenue</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">₦{{ number_format($summary['today']['revenue'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-arrow-up text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
            </div>

            <!-- Today Expenses -->
            <div class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Today's Expenses</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">₦{{ number_format($summary['today']['expenses'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-arrow-down text-red-600 dark:text-red-400"></i>
                    </div>
                </div>
            </div>

            <!-- Today's Net Profit -->
            <div class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Today's Net Profit</p>
                        <p class="text-2xl font-bold {{ $summary['today']['net_profit'] >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-red-600 dark:text-red-400' }} mt-1">
                            ₦{{ number_format($summary['today']['net_profit'], 2) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-wallet text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Monthly Revenue</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">₦{{ number_format($summary['monthly']['revenue'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
            <!-- Monthly Expenses -->
            <div class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Monthly Expenses</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">₦{{ number_format($summary['monthly']['expenses'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-credit-card text-red-600 dark:text-red-400"></i>
                    </div>
                </div>
            </div>

            <!-- Monthly Net Profit -->
            <div class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Monthly Net Profit</p>
                        <p class="text-2xl font-bold {{ $summary['monthly']['net_profit'] >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-red-600 dark:text-red-400' }} mt-1">
                            ₦{{ number_format($summary['monthly']['net_profit'], 2) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-piggy-bank text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                </div>
            </div>

            <!-- Lifetime Profit -->
            <div class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Lifetime Profit</p>
                        <p class="text-2xl font-bold {{ $summary['lifetime']['net_profit'] >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-red-600 dark:text-red-400' }} mt-1">
                            ₦{{ number_format($summary['lifetime']['net_profit'], 2) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-building-columns text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                </div>
            </div>

            <!-- Profit Margin -->
            <div class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Profit Margin</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($profitMargin, 1) }}%</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-percentage text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue vs Expense Chart -->
            <div class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Revenue vs Expenses</h3>
                <canvas id="revenueExpenseChart" height="300"></canvas>
            </div>

            <!-- Revenue Source Pie Chart -->
            <div class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Revenue by Source</h3>
                <canvas id="revenueSourceChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Activation Stats -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Activation Statistics</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 dark:bg-dark-800 rounded-lg">
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $activationStats['total'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Activations</p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-dark-800 rounded-lg">
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $activationStats['normal'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Normal</p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-dark-800 rounded-lg">
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $activationStats['referral'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Referral</p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-dark-800 rounded-lg">
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">₦{{ number_format($activationStats['revenue'], 0) }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Revenue</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="p-4 bg-red-50 dark:bg-red-500/10 rounded-lg border border-red-100 dark:border-red-500/20">
                    <p class="text-sm text-red-600 dark:text-red-400">Referral Bonuses Paid</p>
                    <p class="text-xl font-bold text-red-700 dark:text-red-300">₦{{ number_format($activationStats['referral_bonus'], 2) }}</p>
                </div>
                <div class="p-4 bg-indigo-50 dark:bg-indigo-500/10 rounded-lg border border-indigo-100 dark:border-indigo-500/20">
                    <p class="text-sm text-indigo-600 dark:text-indigo-400">Net Activation Profit</p>
                    <p class="text-xl font-bold text-indigo-700 dark:text-indigo-300">₦{{ number_format($activationStats['net_profit'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Breakdown Table -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Sources -->
            <div class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Revenue Breakdown</h3>
                <div class="space-y-3">
                    @foreach($revenueBySource as $source => $amount)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-dark-700 last:border-0">
                        <span class="text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $source)) }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">₦{{ number_format($amount, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Expense Categories -->
            <div class="bg-white dark:bg-dark-900 rounded-xl shadow-sm border border-gray-200 dark:border-dark-700 p-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Expense Breakdown</h3>
                <div class="space-y-3">
                    @foreach($expensesByCategory as $category => $amount)
                    @if($amount > 0)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-dark-700 last:border-0">
                        <span class="text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $category)) }}</span>
                        <span class="font-medium text-red-600 dark:text-red-400">₦{{ number_format($amount, 2) }}</span>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div id="revenue-json-data"
     data-daily='@json($dailyBreakdown)'
     data-sources='@json($revenueBySource)'
     class="hidden"></div>
<script>
    const revenueJsonNode = document.getElementById('revenue-json-data');
    const dailyData = revenueJsonNode ? JSON.parse(revenueJsonNode.dataset.daily || '[]') : [];
    const revenueSources = revenueJsonNode ? JSON.parse(revenueJsonNode.dataset.sources || '{}') : {};

    // Revenue vs Expense Chart
    const revenueExpenseCtx = document.getElementById('revenueExpenseChart').getContext('2d');
    
    new Chart(revenueExpenseCtx, {
        type: 'line',
        data: {
            labels: dailyData.map(d => d.date),
            datasets: [
                {
                    label: 'Revenue',
                    data: dailyData.map(d => d.revenue),
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Expenses',
                    data: dailyData.map(d => d.expense),
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Profit',
                    data: dailyData.map(d => d.profit),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Revenue Source Pie Chart
    const revenueSourceCtx = document.getElementById('revenueSourceChart').getContext('2d');
    
    new Chart(revenueSourceCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(revenueSources).map(k => k.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())),
            datasets: [{
                data: Object.values(revenueSources),
                backgroundColor: [
                    '#16a34a', '#22c55e', '#4ade80', '#86efac',
                    '#6366f1', '#8b5cf6', '#a78bfa', '#c4b5fd'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });

    // Export function
    function exportReport(format) {
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        window.location.href = `/admin/revenue/export?format=${format}&start_date=${startDate}&end_date=${endDate}`;
    }
</script>
@endpush
@endsection
