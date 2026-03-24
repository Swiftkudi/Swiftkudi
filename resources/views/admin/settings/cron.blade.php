@extends('layouts.admin')

@section('title', 'Cron Jobs')

@section('content')
<div class="py-4 lg:py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Cron Job Settings</h1>
                <p class="text-gray-400 mt-1 text-sm lg:text-base">Control scheduled task execution</p>
            </div>
            <a href="{{ route('admin.settings') }}" class="inline-flex items-center text-indigo-400 hover:text-indigo-300 text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Settings
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-xl">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.settings.update', 'cron') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Cron Job Controls -->
            <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 mb-6">
                <div class="px-4 lg:px-6 py-4 border-b border-dark-700">
                    <h3 class="text-lg font-medium text-white">Scheduled Tasks</h3>
                    <p class="text-sm text-gray-400">Enable or disable automatic scheduled tasks</p>
                </div>
                <div class="px-4 lg:px-6 py-4 space-y-4">
                    <!-- Task Expiry -->
                    <div class="bg-dark-800 rounded-xl p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center">
                                <div class="bg-red-500/20 rounded-lg p-2 mr-3">
                                    <i class="fas fa-clock text-red-400"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-white">Task Expiry Cron</h4>
                                    <p class="text-xs text-gray-400">Automatically expire tasks past their end date</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="cron_task_expiry_enabled" value="true"
                                        {{ (($settingsByKey['cron_task_expiry_enabled'] ?? true) === 'true' || $settingsByKey['cron_task_expiry_enabled'] ?? true) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div class="w-11 h-6 bg-dark-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                                <a href="{{ route('admin.settings.trigger-cron', 'task_expiry') }}" class="inline-flex items-center px-3 py-1.5 bg-dark-700 text-gray-300 hover:text-white text-xs font-medium rounded-lg transition-colors">
                                    <i class="fas fa-play mr-1"></i> Run Now
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Referral Bonus -->
                    <div class="bg-dark-800 rounded-xl p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center">
                                <div class="bg-green-500/20 rounded-lg p-2 mr-3">
                                    <i class="fas fa-gift text-green-400"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-white">Referral Bonus Distribution</h4>
                                    <p class="text-xs text-gray-400">Distribute referral bonuses when conditions are met</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="cron_referral_bonus_enabled" value="true"
                                        {{ (($settingsByKey['cron_referral_bonus_enabled'] ?? true) === 'true' || $settingsByKey['cron_referral_bonus_enabled'] ?? true) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div class="w-11 h-6 bg-dark-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                                <a href="{{ route('admin.settings.trigger-cron', 'referral_bonus') }}" class="inline-flex items-center px-3 py-1.5 bg-dark-700 text-gray-300 hover:text-white text-xs font-medium rounded-lg transition-colors">
                                    <i class="fas fa-play mr-1"></i> Run Now
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Streak -->
                    <div class="bg-dark-800 rounded-xl p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center">
                                <div class="bg-yellow-500/20 rounded-lg p-2 mr-3">
                                    <i class="fas fa-fire text-yellow-400"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-white">Daily Streak Reset</h4>
                                    <p class="text-xs text-gray-400">Reset broken streaks and award streak rewards</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="cron_daily_streak_enabled" value="true"
                                        {{ (($settingsByKey['cron_daily_streak_enabled'] ?? true) === 'true' || $settingsByKey['cron_daily_streak_enabled'] ?? true) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div class="w-11 h-6 bg-dark-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                                <a href="{{ route('admin.settings.trigger-cron', 'daily_streak') }}" class="inline-flex items-center px-3 py-1.5 bg-dark-700 text-gray-300 hover:text-white text-xs font-medium rounded-lg transition-colors">
                                    <i class="fas fa-play mr-1"></i> Run Now
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Fraud Scan -->
                    <div class="bg-dark-800 rounded-xl p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center">
                                <div class="bg-purple-500/20 rounded-lg p-2 mr-3">
                                    <i class="fas fa-shield-alt text-purple-400"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-white">Fraud Scan</h4>
                                    <p class="text-xs text-gray-400">Scan for fraudulent activities and patterns</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="cron_fraud_scan_enabled" value="true"
                                        {{ (($settingsByKey['cron_fraud_scan_enabled'] ?? true) === 'true' || $settingsByKey['cron_fraud_scan_enabled'] ?? true) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div class="w-11 h-6 bg-dark-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                                <a href="{{ route('admin.settings.trigger-cron', 'fraud_scan') }}" class="inline-flex items-center px-3 py-1.5 bg-dark-700 text-gray-300 hover:text-white text-xs font-medium rounded-lg transition-colors">
                                    <i class="fas fa-play mr-1"></i> Run Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cron Schedule Info -->
            <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-xl p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-indigo-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-indigo-300">
                            <strong>Setup Instructions:</strong> Add the following to your server's crontab:
                        </p>
                        <code class="block mt-2 bg-dark-800 px-3 py-2 rounded-lg text-xs text-gray-300 overflow-x-auto">* * * * * php {{ base_path('artisan') }} schedule:run >> /dev/null 2>&1</code>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">
                    <i class="fas fa-save mr-2"></i> Save Cron Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
