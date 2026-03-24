@extends('layouts.admin')

@section('title', 'Admin Dashboard - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Admin Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Platform overview and management</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</h3>
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_users']) }}</div>
                <div class="mt-2 text-sm text-green-600 dark:text-green-400">
                    <i class="fas fa-user-plus mr-1"></i>{{ $stats['new_users_today'] }} today
                </div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tasks</h3>
                    <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                        <i class="fas fa-tasks text-purple-600 dark:text-purple-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_tasks']) }}</div>
                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ number_format($stats['active_tasks']) }} active
                </div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Earnings</h3>
                    <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-naira-sign text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-green-600 dark:text-green-400">₦{{ number_format($stats['total_earnings'], 2) }}</div>
                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Platform paid out
                </div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Approvals</h3>
                    <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center">
                        <i class="fas fa-hourglass-half text-orange-600 dark:text-orange-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $pendingCompletions }}</div>
                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Task completions
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <a href="{{ route('admin.users') }}" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-5 text-white hover:shadow-xl hover:shadow-blue-500/30 transition-all transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-bold">Manage Users</h3>
                        <p class="text-blue-100 text-sm">View all users</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.tasks') }}" class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-5 text-white hover:shadow-xl hover:shadow-purple-500/30 transition-all transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-tasks text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-bold">Manage Tasks</h3>
                        <p class="text-purple-100 text-sm">View all tasks</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.professional-services') }}" class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl p-5 text-white hover:shadow-xl hover:shadow-teal-500/30 transition-all transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-briefcase text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-bold">Services</h3>
                        <p class="text-teal-100 text-sm">Professional services</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.withdrawals') }}" class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-5 text-white hover:shadow-xl hover:shadow-green-500/30 transition-all transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-bold">Withdrawals</h3>
                        <p class="text-green-100 text-sm">{{ $pendingWithdrawals }} pending</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Second Row Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <a href="{{ route('admin.completions') }}" class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-5 text-white hover:shadow-xl hover:shadow-orange-500/30 transition-all transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-bold">Completions</h3>
                        <p class="text-orange-100 text-sm">{{ $pendingCompletions }} pending</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.referrals') }}" class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-2xl p-5 text-white hover:shadow-xl hover:shadow-pink-500/30 transition-all transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-user-plus text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-bold">Referrals</h3>
                        <p class="text-pink-100 text-sm">Manage referrals</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.analytics') }}" class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-5 text-white hover:shadow-xl hover:shadow-indigo-500/30 transition-all transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-chart-line text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-bold">Analytics</h3>
                        <p class="text-indigo-100 text-sm">View reports</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.settings') }}" class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-2xl p-5 text-white hover:shadow-xl hover:shadow-gray-500/30 transition-all transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-cog text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-bold">Settings</h3>
                        <p class="text-gray-100 text-sm">Platform config</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Users -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Recent Users</h2>
                    <a href="{{ route('admin.users') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">View all</a>
                </div>
                @if(count($recentUsers) > 0)
                <div class="space-y-4">
                    @foreach($recentUsers as $user)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-800 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $user->created_at->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No users yet</p>
                @endif
            </div>

            <!-- Recent Tasks -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Recent Tasks</h2>
                    <a href="{{ route('admin.tasks') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">View all</a>
                </div>
                @if(count($recentTasks) > 0)
                <div class="space-y-4">
                    @foreach($recentTasks as $task)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-800 rounded-xl>
                            <p">
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ Str::limit($task->title, 30) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">by {{ $task->user->name }}</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $task->status === 'active' ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                            {{ ucfirst($task->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No tasks yet</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
