@extends('layouts.admin')

@section('title', 'Analytics')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Analytics</h1>
                <p class="mt-1 text-sm text-gray-500">Detailed platform performance metrics and reports</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <a href="{{ route('admin.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="fas fa-print mr-2"></i> Print Report
                </button>
            </div>
        </div>

        <!-- Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Platform Revenue -->
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <i class="fas fa-naira-sign text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Platform Revenue</dt>
                            <dd class="text-lg font-medium text-gray-900">₦{{ number_format($platformRevenue, 2) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-xs text-gray-500">All recorded system revenue</span>
                </div>
            </div>

            <!-- New Users -->
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                        <i class="fas fa-user-plus text-indigo-600 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">New Users (30 days)</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($newUsers) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-xs text-gray-500">{{ number_format($stats['total_users']) }} total users</span>
                </div>
            </div>

            <!-- Completion Rate -->
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                        <i class="fas fa-check-double text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completion Rate</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $completionRate }}%</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $completionRate }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Total Earnings -->
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                        <i class="fas fa-wallet text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Earnings</dt>
                            <dd class="text-lg font-medium text-gray-900">₦{{ number_format($stats['total_earnings'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-xs text-gray-500">Platform-wide earnings</span>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- User Growth Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">User Growth (Last 12 Months)</h3>
                <div class="h-64">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>

            <!-- Task Completion Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Task Completion Status</h3>
                <div class="h-64 flex items-center justify-center">
                    <canvas id="taskCompletionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Detailed Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Tasks Overview -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Tasks Overview</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Active Tasks</span>
                        <span class="text-lg font-medium text-gray-900">{{ number_format($stats['active_tasks']) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Total Tasks</span>
                        <span class="text-lg font-medium text-gray-900">{{ number_format($stats['total_tasks']) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Pending Review</span>
                        <span class="text-lg font-medium text-yellow-600">{{ number_format($stats['pending_completions'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Total Earnings</span>
                        <span class="text-lg font-medium text-green-600">₦{{ number_format($stats['total_earnings'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Withdrawals Overview -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Withdrawals Overview</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Total Withdrawn</span>
                        <span class="text-lg font-medium text-gray-900">₦{{ number_format($stats['total_withdrawals'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Pending</span>
                        <span class="text-lg font-medium text-yellow-600">{{ number_format($stats['pending_withdrawals']) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Fraud Alerts</span>
                        <span class="text-lg font-medium text-red-600">{{ number_format($stats['unresolved_fraud_logs']) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Total Fees Earned</span>
                        <span class="text-lg font-medium text-indigo-600">₦{{ number_format($stats['total_fees'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Summary</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Total Earnings</span>
                        <span class="text-lg font-medium text-gray-900">₦{{ number_format($stats['total_earnings'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Withdrawal Fees</span>
                        <span class="text-lg font-medium text-indigo-600">₦{{ number_format($stats['total_fees'] ?? 0, 2) }}</span>
                    </div>
                    <hr class="border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-medium text-gray-900">Total Revenue</span>
                        <span class="text-xl font-bold text-green-600">₦{{ number_format($platformRevenue, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Platform Activity Summary</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-indigo-600">{{ number_format($stats['total_users']) }}</div>
                        <div class="text-sm text-gray-500 mt-1">Total Users</div>
                        <div class="text-xs text-green-600 mt-1">+{{ number_format($newUsers) }} this month</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600">{{ number_format($stats['activated_users']) }}</div>
                        <div class="text-sm text-gray-500 mt-1">Activated Wallets</div>
                        <div class="text-xs text-gray-500 mt-1">{{ number_format(($stats['activated_users'] / max($stats['total_users'], 1)) * 100, 1) }}% activation rate</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ number_format($stats['total_tasks']) }}</div>
                        <div class="text-sm text-gray-500 mt-1">Total Tasks</div>
                        <div class="text-xs text-blue-600 mt-1">{{ number_format($stats['active_tasks']) }} active</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'New Users',
                data: [65, 59, 80, 81, 56, 55, 40, 70, 85, 90, 75, 95],
                borderColor: 'rgb(79, 70, 229)',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Task Completion Chart
    const taskCompletionCtx = document.getElementById('taskCompletionChart').getContext('2d');
    new Chart(taskCompletionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [{{ $completionRate }}, {{ 100 - $completionRate - 10 }}, 10],
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(234, 179, 8)',
                    'rgb(239, 68, 68)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
@endsection
