@extends('layouts.app')

@section('title', 'Dashboard - SwiftKudi')

@section('content')
@php
$user = auth()->user();
$accountType = $user->account_type ?? '';
@endphp
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
               
                <span class="text-green-600 dark:text-green-400 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Welcome to your {{ ucfirst($accountType) }} dashboard.
                </span>
                @if(!$isActivated)
                <span class="text-yellow-500 dark:text-yellow-400 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i> Please activate your account to start earning.
                </span>
                @else
                <span class="text-green-600 dark:text-green-400 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Your account is active. Keep up the great work!
                </span>
                @endif
            </p>
        </div>

        @if(!$isActivated)
        <!-- Activation Call-to-Action -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-xl shadow-indigo-500/30 p-8 mb-8 text-white">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="text-center md:text-left">
                    <h2 class="text-2xl font-bold mb-2">Activate Your Account</h2>
                    <p class="text-indigo-100 mb-4">
                        @if($accountType === 'earner')
                            To unlock all features and start earning, please activate your account.
                        @if($activationFeeEnabled)
                            Pay ₦{{ number_format($activationFee, 0) }} activation fee to unlock all features and start earning.
                        @else
                            Activate your account for free to unlock all features and start earning.
                        @endif
                        @else
                            Activate your account for free to unlock all {{ ucfirst($accountType) }} features.
                        @endif

                    </p>
                    <a href="{{ route('wallet.activate') }}" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 font-semibold rounded-xl hover:bg-indigo-50 transition-all shadow-lg">
                        <i class="fas fa-rocket mr-2"></i>Activate Now
                    </a>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold">
                        @if($accountType === 'earner')
                        @if($activationFeeEnabled)
                            ₦{{ number_format($activationFee, 0) }}
                        @else
                            Free
                        @endif
                        @else
                            Free
                        @endif
                    </div>
                    <div class="text-indigo-200 mt-1">
                        @if($accountType === 'earner')
                        @if($activationFeeEnabled)
                            One-time activation
                        @else
                            No charge required
                        @endif
                        @else
                            No charge required
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($isActivated )
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Balance -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Wallet Balance</h3>
                    <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-wallet text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">₦{{ number_format($wallet->withdrawable_balance + $wallet->promo_credit_balance, 2) }}</div>
                <div class="mt-3 flex flex-col gap-1 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Withdrawable: <span class="text-gray-900 dark:text-gray-200 font-medium">₦{{ number_format($wallet->withdrawable_balance, 2) }}</span></span>
                    <span class="text-blue-500">Promo: ₦{{ number_format($wallet->promo_credit_balance, 2) }}</span>
                </div>
            </div>

            <!-- Level & XP -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Level {{ $user->level }}</h3>
                    <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center">
                        <i class="fas fa-star text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($user->experience_points) }} XP</div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 dark:bg-dark-700 rounded-full h-2.5">
                        <div id="dashboard-level-progress" class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2.5 rounded-full" data-progress="{{ (float) ($levelProgress['percentage'] ?? 0) }}"></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ number_format($levelProgress['xp_progress']) }} / {{ number_format($levelProgress['xp_needed']) }} XP to Level {{ $levelProgress['next_level'] }}</p>
                </div>
            </div>

            <!-- Tasks -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Tasks</h3>
                    <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                        <i class="fas fa-tasks text-purple-600 dark:text-purple-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['tasks_completed']) }}</div>
                <div class="mt-3 text-sm flex flex-col gap-1">
                    <span class="text-green-600 dark:text-green-400"><i class="fas fa-check mr-1"></i>{{ $stats['tasks_completed'] }} completed</span>
                    <span class="text-yellow-600 dark:text-yellow-400"><i class="fas fa-plus mr-1"></i>{{ $stats['tasks_created'] }} created</span>
                </div>
            </div>

            <!-- Streak -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Daily Streak</h3>
                    <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center">
                        <i class="fas fa-fire text-orange-600 dark:text-orange-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $user->daily_streak }} days</div>
                <div class="mt-3 text-sm">
                    @if($user->daily_streak >= 7)
                    <span class="text-green-600 dark:text-green-400 flex items-center gap-2">
                        <i class="fas fa-fire"></i> Keep it up!
                    </span>
                    @else
                    <span class="text-gray-500 dark:text-gray-400">Complete a task today!</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            @php
            $user = auth()->user();
            $accountType = $user->account_type ?? '';
            @endphp

            @if($accountType === 'task_creator')
            <!-- Create Task -->
            <a href="{{ route('tasks.create') }}" class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white hover:shadow-xl hover:shadow-indigo-500/30 transition-all transform hover:-translate-y-1">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-plus text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Create Task</h3>
                        <p class="text-indigo-100 text-sm">Get engagement on your posts</p>
                    </div>
                </div>
            </a>
            @endif

            @if(!in_array($accountType, ['buyer', 'freelancer', 'task_creator', 'digital_seller', 'growth_seller']))
            <!-- Available Tasks -->
            <a href="{{ route('tasks.index') }}" class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:border-indigo-300 dark:hover:border-indigo-500 transition-all transform hover:-translate-y-1">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-tasks text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100">Available Tasks</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Browse tasks to complete</p>
                    </div>
                </div>
            </a>
            @endif

            <!-- Wallet (Always visible) -->
            <a href="{{ route('wallet.index') }}" class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:border-indigo-300 dark:hover:border-indigo-500 transition-all transform hover:-translate-y-1">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                        <i class="fas fa-wallet text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100">My Wallet</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Manage your earnings</p>
                    </div>
                </div>
            </a>

            @if(!in_array($accountType, ['task_creator', 'digital_seller', 'growth_seller', 'earner', 'buyer']))
            <!-- Hire (Professional Services) -->
            <a href="{{ route('professional-services.index') }}" class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:border-indigo-300 dark:hover:border-indigo-500 transition-all transform hover:-translate-y-1">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                        <i class="fas fa-briefcase text-purple-600 dark:text-purple-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100">Hire</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Find professionals</p>
                    </div>
                </div>
            </a>
            @endif

            @if(!in_array($accountType, ['task_creator', 'freelancer', 'digital_seller', 'earner']))
            <!-- Growth Marketplace -->
            <a href="{{ route('growth.index') }}" class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:border-indigo-300 dark:hover:border-indigo-500 transition-all transform hover:-translate-y-1">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100">Growth</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Backlinks & Leads</p>
                    </div>
                </div>
            </a>
            @endif

            @if(!in_array($accountType, ['task_creator', 'freelancer', 'growth_seller', 'earners']))
            <!-- Digital Products -->
            <a href="{{ route('digital-products.index') }}" class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:border-indigo-300 dark:hover:border-indigo-500 transition-all transform hover:-translate-y-1">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center">
                        <i class="fas fa-download text-orange-600 dark:text-orange-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100">Products</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Templates & Plugins</p>
                    </div>
                </div>
            </a>
            @endif
        </div>

        <!-- Feature Unlock CTA -->
        @if(!empty($accountType) && in_array($accountType, ['task_creator', 'freelancer', 'digital_seller', 'growth_seller', 'earner', 'buyer']))
        <div class="mb-8">
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-500/10 dark:to-purple-500/10 rounded-2xl p-6 border border-indigo-100 dark:border-indigo-500/20">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Unlock More Features</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Get access to additional features and capabilities</p>
                    </div>
                    <a href="{{ route('onboarding.features') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-all flex items-center gap-2">
                        <i class="fas fa-unlock"></i>
                        View Features
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Activity & Referrals -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- My Tasks -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">My Tasks</h2>
                    <a href="{{ route('tasks.my-tasks') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">View all</a>
                </div>
                @if(isset($myTasks) && count($myTasks) > 0)
                    <div class="space-y-4">
                        @foreach($myTasks->take(3) as $task)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-800 rounded-xl">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $task->title }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $task->completions_count }} / {{ $task->quantity }} completed</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $task->status === 'active' ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                {{ ucfirst($task->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-tasks text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">No tasks yet</p>
                        <a href="{{ route('tasks.create') }}" class="text-indigo-600 dark:text-indigo-400 text-sm hover:underline">Create your first task</a>
                    </div>
                @endif
            </div>

            <!-- Referrals -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Referrals</h2>
                    <a href="{{ route('referrals.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">View all</a>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="p-4 bg-indigo-50 dark:bg-indigo-500/10 rounded-xl text-center">
                        <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['total_referrals'] }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Referrals</div>
                    </div>
                    <div class="p-4 bg-green-50 dark:bg-green-500/10 rounded-xl text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">₦{{ number_format($stats['referral_earnings'], 2) }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Earned</div>
                    </div>
                </div>
                <div class="p-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-500/10 dark:to-purple-500/10 rounded-xl">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Your referral link:</p>
                    <div class="flex items-center gap-2">
                        @if(!empty($referralCode))
                            <input type="text" readonly value="{{ route('ref.redirect', ['code' => $referralCode]) }}" class="flex-1 px-3 py-2 bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-gray-100">
                            <button onclick="copyReferralLink()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                                <i class="fas fa-copy"></i>
                            </button>
                        @else
                            <div class="flex-1 px-3 py-2 bg-gray-100 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-lg text-sm text-gray-500 dark:text-gray-400">Generating referral code...</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Badges Section -->
        @if(count($badges) > 0)
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Your Badges</h2>
                <a href="#" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">View all badges</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($badges as $badge)
                <div class="text-center p-4 bg-gray-50 dark:bg-dark-800 rounded-xl hover:scale-105 transition-transform">
                    <div class="text-3xl mb-2">{!! $badge->icon !!}</div>
                    <h4 class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $badge->name }}</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $badge->pivot->earned_at->diffForHumans() }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endif
    </div>
</div>

<script>
const levelProgressBar = document.getElementById('dashboard-level-progress');
if (levelProgressBar) {
    const progress = Number(levelProgressBar.dataset.progress || 0);
    levelProgressBar.style.width = `${Math.min(100, Math.max(0, progress))}%`;
}

function copyReferralLink() {
    const input = document.querySelector('input[readonly][value*="ref="]');
    if (input) {
        input.select();
        document.execCommand('copy');
        alert('Referral link copied!');
    }
}
</script>
@endsection
