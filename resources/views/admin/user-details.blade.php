@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.users') }}" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                    <i class="fas fa-arrow-left mr-2"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">User Details</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">View user information and activity</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- User Info -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $user->name }}</h2>
                        <p class="text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                        @if($user->wallet && $user->wallet->is_activated)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 mt-2">
                            <i class="fas fa-check-circle mr-1"></i>Activated
                        </span>
                        @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400 mt-2">
                            <i class="fas fa-clock mr-1"></i>Pending
                        </span>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Level</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">Level {{ $user->level }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">XP</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($user->experience_points) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Daily Streak</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->daily_streak }} days</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Joined</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Account Status</span>
                            <div class="mt-2">
                                @if($user->is_suspended)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400">
                                    <i class="fas fa-ban mr-1"></i>Suspended
                                </span>
                                @elseif($user->wallet && $user->wallet->is_activated)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400">
                                    <i class="fas fa-check-circle mr-1"></i>Active
                                </span>
                                @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400">
                                    <i class="fas fa-clock mr-1"></i>Pending
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Account Type</span>
                            <div class="mt-2">
                                @if($user->account_type)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400">
                                    {{ ucfirst(str_replace('_', ' ', $user->account_type)) }}
                                </span>
                                @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-500/20 text-gray-700 dark:text-gray-400">
                                    <i class="fas fa-user-clock mr-1"></i>Not Selected
                                </span>
                                @endif
                            </div>
                            @if($user->account_type && $user->id !== auth()->id())
                            <div class="mt-3">
                                <form action="{{ route('admin.users.remove-account-type', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs px-3 py-1 bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-500/30 rounded-lg transition-colors" onclick="return confirm('Remove account type for this user? They will need to go through onboarding again to select their account type.')">
                                        <i class="fas fa-undo mr-1"></i>Remove Account Type
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                        <div class="py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Admin Status</span>
                            <div class="mt-2">
                                @if($user->is_admin)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400">
                                    <i class="fas fa-shield-alt mr-1"></i>Admin
                                </span>
                                @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-500/20 text-gray-700 dark:text-gray-400">
                                    <i class="fas fa-user mr-1"></i>User
                                </span>
                                @endif
                            </div>
                            @if($user->id !== auth()->id())
                            <div class="mt-3">
                                <form action="{{ route('admin.users.suspend', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @if($user->is_suspended)
                                    <button type="submit" class="text-xs px-3 py-1 bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-500/30 rounded-lg transition-colors" onclick="return confirm('Unsuspend this account?')">
                                        <i class="fas fa-unlock mr-1"></i>Unsuspend
                                    </button>
                                    @else
                                    <button type="submit" class="text-xs px-3 py-1 bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-500/30 rounded-lg transition-colors" onclick="return confirm('Suspend this account?')">
                                        <i class="fas fa-ban mr-1"></i>Suspend
                                    </button>
                                    @endif
                                </form>
                                @if($user->is_admin)
                                <form action="{{ route('admin.users.demote', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs px-3 py-1 bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-500/30 transition-colors" onclick="return confirm('Are you sure you want to demote this user from admin?')">
                                        <i class="fas fa-arrow-down mr-1"></i>Demote
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('admin.users.promote', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs px-3 py-1 bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400 rounded-lg hover:bg-purple-200 dark:hover:bg-purple-500/30 transition-colors" onclick="return confirm('Are you sure you want to promote this user to admin?')">
                                        <i class="fas fa-arrow-up mr-1"></i>Promote to Admin
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.users.delete', $user) }}" method="POST" class="inline ml-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs px-3 py-1 bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-500/30 transition-colors" onclick="return confirm('Delete this user account? This action cannot be undone.')">
                                        <i class="fas fa-trash mr-1"></i>Delete User
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @if($wallet)
                        <div class="pt-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Wallet</h3>
                                <form action="{{ route('admin.users.clear-wallet', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs px-3 py-1 bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400 rounded-lg hover:bg-yellow-200 dark:hover:bg-yellow-500/30 transition-colors" onclick="return confirm('Clear all wallet money for this user?')">
                                        <i class="fas fa-wallet mr-1"></i>Clear Wallet
                                    </button>
                                </form>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Balance</span>
                                    <span class="font-bold text-green-600 dark:text-green-400">₦{{ number_format($wallet->withdrawable_balance + $wallet->promo_credit_balance, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Withdrawable</span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">₦{{ number_format($wallet->withdrawable_balance, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Promo</span>
                                    <span class="font-semibold text-blue-600 dark:text-blue-400">₦{{ number_format($wallet->promo_credit_balance, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- User Activity -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Tasks -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Tasks ({{ count($tasks) }})</h3>
                    @if(count($tasks) > 0)
                    <div class="space-y-3">
                        @foreach($tasks->take(5) as $task)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-800 rounded-xl">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ Str::limit($task->title, 40) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $task->platform }} • {{ $task->status }}</p>
                            </div>
                            <span class="font-semibold text-green-600 dark:text-green-400">₦{{ number_format($task->worker_reward_per_task, 0) }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No tasks created</p>
                    @endif
                </div>

                <!-- Completions -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Task Completions ({{ count($completions) }})</h3>
                    @if(count($completions) > 0)
                    <div class="space-y-3">
                        @foreach($completions->take(5) as $completion)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-800 rounded-xl">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ Str::limit($completion->task->title ?? 'Task', 40) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $completion->created_at->format('M d, Y') }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $completion->status === 'approved' ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400' : ($completion->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400' : 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400') }}">
                                {{ ucfirst($completion->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No completions yet</p>
                    @endif
                </div>

                <!-- Withdrawals -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Withdrawals ({{ count($withdrawals) }})</h3>
                    @if(count($withdrawals) > 0)
                    <div class="space-y-3">
                        @foreach($withdrawals->take(5) as $withdrawal)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-800 rounded-xl">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-gray-100">₦{{ number_format($withdrawal->amount, 2) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $withdrawal->created_at->format('M d, Y') }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $withdrawal->status === 'completed' ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400' : ($withdrawal->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400' : 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400') }}">
                                {{ ucfirst($withdrawal->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No withdrawals yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
