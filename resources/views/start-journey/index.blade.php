@extends('layouts.app')

@section('title', 'Start Your Journey - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 bg-indigo-50 dark:bg-indigo-500/10 rounded-full px-4 py-2 mb-4">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                <span class="text-indigo-600 dark:text-indigo-400 text-sm font-medium">Welcome to SwiftKudi 🚀</span>
            </div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
                Start Your Earning Journey
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Create your first campaign to unlock instant access to high-paying tasks.
            </p>
        </div>

        <!-- Progress Card -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 mb-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="42" stroke="currentColor" stroke-width="8" fill="none"
                                class="text-gray-200 dark:text-dark-700"/>
                            <circle cx="50" cy="50" r="42" stroke="url(#progressGradient)" stroke-width="8" fill="none"
                                stroke-linecap="round" stroke-dasharray="264"
                                stroke-dashoffset="{{ 264 - (264 * min(100, ($userBudget / $minimumBudget) * 100) / 100) }}"/>
                            <defs>
                                <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#4f46e5"/>
                                    <stop offset="100%" stop-color="#7c3aed"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($userBudget) }}</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">Your Progress</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Create ₦{{ number_format($minimumBudget) }} to unlock</p>
                        @if($remaining > 0)
                            <p class="text-sm text-indigo-600 dark:text-indigo-400 font-medium mt-1">
                                ₦{{ number_format($remaining) }} more needed
                            </p>
                        @else
                            <p class="text-sm text-green-600 dark:text-green-400 font-medium mt-1 flex items-center gap-1">
                                <i class="fas fa-check-circle"></i> Unlocked!
                            </p>
                        @endif
                    </div>
                </div>

                @if($remaining > 0)
                    <a href="{{ route('tasks.create') }}"
                       class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold px-6 py-3 rounded-xl transition-all shadow-lg shadow-indigo-500/30 flex items-center gap-2">
                        <i class="fas fa-plus"></i> Create Campaign
                    </a>
                @else
                    <a href="{{ route('tasks.index') }}"
                       class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white font-semibold px-6 py-3 rounded-xl transition-all shadow-lg shadow-green-500/30 flex items-center gap-2">
                        <i class="fas fa-rocket"></i> Start Earning
                    </a>
                @endif
            </div>

            <!-- Progress Bar -->
            <div class="mt-6">
                <div class="w-full bg-gray-200 dark:bg-dark-700 rounded-full h-2.5">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2.5 rounded-full transition-all duration-500"
                         style="width: {{ min(100, ($userBudget / $minimumBudget) * 100) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Quick Start Bundles -->
        @if(count($bundles) > 0)
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                <i class="fas fa-bolt text-yellow-500"></i> Quick Start Bundles
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($bundles as $index => $bundle)
                <form action="{{ route('start-journey.apply-bundle') }}" method="POST">
                    @csrf
                    <input type="hidden" name="bundle_id" value="{{ $bundle['id'] }}">
                    <button type="submit"
                            class="w-full bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-5 hover:shadow-xl hover:border-indigo-300 dark:hover:border-indigo-500 transition-all transform hover:-translate-y-1 text-left">
                        @if($index === 1)
                        <div class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full inline-block mb-3">
                            ⭐ Most Popular
                        </div>
                        @endif
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 mb-1">{{ $bundle['name'] }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ $bundle['description'] }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                ₦{{ number_format($bundle['total_price']) }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $bundle['total_tasks'] }} tasks</span>
                        </div>
                    </button>
                </form>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Platform Stats -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-users text-indigo-600 dark:text-indigo-400 text-xl"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($activeWorkers) }}+</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Active Workers</div>
            </div>
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-naira-sign text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">₦{{ number_format($totalPaidOut / 1000000, 1) }}M</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Paid Out</div>
            </div>
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check-circle text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">98%</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Success Rate</div>
            </div>
        </div>

        <!-- Earnings Preview -->
        @if(count($availableTasks) > 0)
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <i class="fas fa-money-bill-wave text-green-500"></i> Available Earnings
                </h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">Create campaign to unlock →</span>
            </div>
            <div class="space-y-3">
                @foreach($availableTasks as $task)
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-800 rounded-xl opacity-60">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-200 dark:bg-dark-700 flex items-center justify-center">
                            @switch($task->platform)
                                @case('instagram')<span>📷</span>@break
                                @case('twitter')<span>🐦</span>@break
                                @case('tiktok')<span>🎵</span>@break
                                @case('youtube')<span>▶️</span>@break
                                @default<span>📱</span>
                            @endswitch
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $task->title }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 capitalize">{{ $task->platform }} • {{ $task->task_type }}</p>
                        </div>
                    </div>
                    <span class="font-bold text-green-600 dark:text-green-400">₦{{ number_format($task->worker_reward_per_task) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Motivational Message -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-500/10 dark:to-purple-500/10 rounded-2xl p-6 text-center border border-indigo-100 dark:border-indigo-500/20">
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                "Top earners started exactly where you are. Creating your first campaign unlocks your full earning potential!"
            </p>
            <a href="{{ route('tasks.create') }}"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold px-8 py-3 rounded-xl transition-all shadow-lg shadow-indigo-500/30">
                <i class="fas fa-rocket"></i> Create Your First Campaign
            </a>
        </div>
    </div>
</div>

<!-- Success Modal -->
@if($userBudget >= $minimumBudget)
<div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-2xl p-8 max-w-md w-full text-center border border-gray-100 dark:border-dark-700">
        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-trophy text-4xl text-white"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">🎉 Earnings Unlocked!</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Your first campaign has been created. You can now start earning from tasks!
        </p>
        <a href="{{ route('tasks.index') }}"
           class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white font-semibold px-8 py-3 rounded-xl transition-all shadow-lg inline-flex items-center gap-2">
            <i class="fas fa-rocket"></i> Start Earning Now
        </a>
    </div>
</div>
<script>setTimeout(() => { document.querySelector('.fixed').classList.add('hidden'); }, 3000);</script>
@endif

<script>setInterval(() => { fetch('{{ route("start-journey.check-status") }}').then(r => r.json()).then(d => { if (d.unlocked) location.reload(); }); }, 15000);</script>
@endsection
