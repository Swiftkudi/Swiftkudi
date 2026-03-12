@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">Platform overview and statistics</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('admin.analytics') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="fas fa-chart-line mr-2"></i> Analytics
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                        <i class="fas fa-users text-indigo-600 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_users']) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-green-600">{{ number_format($stats['activated_users']) }} activated</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <i class="fas fa-tasks text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Tasks</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['active_tasks']) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-gray-500">{{ number_format($stats['total_tasks']) }} total tasks</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                        <i class="fas fa-money-bill-wave text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Earnings</dt>
                            <dd class="text-lg font-medium text-gray-900">₦{{ number_format($stats['total_earnings'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-gray-500">₦{{ number_format($stats['total_withdrawals'], 2) }} withdrawn</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Actions</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $pendingCompletions + $pendingWithdrawals }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.completions') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                        {{ $pendingCompletions }} completions
                    </a>
                    <span class="text-gray-300 mx-2">|</span>
                    <a href="{{ route('admin.withdrawals') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                        {{ $pendingWithdrawals }} withdrawals
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Recent Users -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Users</h3>
                </div>
                <ul class="divide-y divide-gray-200">
                    @foreach($recentUsers as $user)
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-indigo-600 font-medium">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                @if($user->wallet && $user->wallet->is_activated)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                                @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Pending
                                </span>
                                @endif
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                    <a href="{{ route('admin.users') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View all users</a>
                </div>
            </div>

            <!-- Recent Tasks -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Tasks</h3>
                </div>
                <ul class="divide-y divide-gray-200">
                    @foreach($recentTasks as $task)
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                                <p class="text-sm text-gray-500">{{ $task->platform }} • ₦{{ number_format($task->budget) }}</p>
                            </div>
                            <div class="flex items-center">
                                @if($task->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                                @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ ($task->completed_count / $task->quantity) * 100 }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $task->completed_count }}/{{ $task->quantity }} completed</p>
                        </div>
                    </li>
                    @endforeach
                </ul>
                <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                    <a href="{{ route('admin.tasks') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View all tasks</a>
                </div>
            </div>
        </div>

        <!-- Recent Withdrawals -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Withdrawals</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentWithdrawals as $withdrawal)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $withdrawal->user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $withdrawal->user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">₦{{ number_format($withdrawal->amount) }}</div>
                                <div class="text-xs text-gray-500">Fee: ₦{{ number_format($withdrawal->fee) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{{ $withdrawal->method }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $withdrawal->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($withdrawal->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $withdrawal->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $withdrawal->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($withdrawal->isPending())
                                <a href="{{ route('admin.withdrawals') }}" class="text-indigo-600 hover:text-indigo-900">Review</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                <a href="{{ route('admin.withdrawals') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View all withdrawals</a>
            </div>
        </div>

        <!-- Fraud Alerts -->
        @if($stats['unresolved_fraud_logs'] > 0)
        <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Attention Required</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>There are {{ $stats['unresolved_fraud_logs'] }} unresolved fraud alerts that need review.</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.fraud-logs') }}" class="text-sm font-medium text-red-800 hover:text-red-900">View fraud logs</a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
