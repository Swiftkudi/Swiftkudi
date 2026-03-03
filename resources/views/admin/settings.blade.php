@extends('layouts.admin')

@section('title', 'Admin Settings')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">System Settings</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Manage application configuration and platform options</p>
        </div>

        <!-- Settings Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- General -->
            <a href="{{ route('admin.settings.general') }}" class="group relative bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300 block">
                <div class="absolute top-3 right-3">
                    <div class="relative">
                        <button type="button" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-dark-700 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" data-tooltip-target="tooltip-general">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                        <div id="tooltip-general" class="absolute z-50 hidden group-hover:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-64 p-3 bg-dark-800 text-white text-xs rounded-lg shadow-xl">
                            Configure basic site settings including site name, description, logo, and locale preferences.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-dark-800"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-cog text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">General</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Site title, locale and basic options</p>
            </a>

            <!-- Email / SMTP -->
            <a href="{{ route('admin.settings.smtp') }}" class="group relative bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300 block">
                <div class="absolute top-3 right-3">
                    <div class="relative">
                        <button type="button" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-dark-700 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" data-tooltip-target="tooltip-smtp">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                        <div id="tooltip-smtp" class="absolute z-50 hidden group-hover:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-64 p-3 bg-dark-800 text-white text-xs rounded-lg shadow-xl">
                            Set up your email server to send transactional emails, password resets, and notifications to users.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-dark-800"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-envelope text-purple-600 dark:text-purple-400 text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Email / SMTP</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Configure outgoing mail server</p>
            </a>

            <!-- Email Notification Messages -->
            <a href="{{ route('admin.settings.notifications') }}" class="group relative bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:shadow-pink-500/10 transition-all duration-300 block">
                <div class="absolute top-3 right-3">
                    <div class="relative">
                        <button type="button" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-dark-700 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" data-tooltip-target="tooltip-notifications">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                        <div id="tooltip-notifications" class="absolute z-50 hidden group-hover:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-64 p-3 bg-dark-800 text-white text-xs rounded-lg shadow-xl">
                            Manage email templates and notification messages sent to users for various events.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-dark-800"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-pink-100 dark:bg-pink-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-bell text-pink-600 dark:text-pink-400 text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300 dark:text-gray-600 group-hover:text-pink-500 group-hover:translate-x-1 transition-all"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Email Notifications</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Manage email templates and messages</p>
            </a>

            <!-- Payments -->
            <div class="group relative bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300">
                <div class="absolute top-3 right-3">
                    <div class="relative">
                        <button type="button" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-dark-700 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" data-tooltip-target="tooltip-payment">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                        <div id="tooltip-payment" class="absolute z-50 hidden group-hover:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-64 p-3 bg-dark-800 text-white text-xs rounded-lg shadow-xl">
                            Configure payment gateways for deposits and withdrawals. Set minimum/maximum amounts and processing fees.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-dark-800"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-credit-card text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Payments</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Payment gateways and payout settings</p>
            </div>

            <!-- Commission -->
            <div class="group relative bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300">
                <div class="absolute top-3 right-3">
                    <div class="relative">
                        <button type="button" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-dark-700 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" data-tooltip-target="tooltip-commission">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                        <div id="tooltip-commission" class="absolute z-50 hidden group-hover:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-64 p-3 bg-dark-800 text-white text-xs rounded-lg shadow-xl">
                            Set platform commission rates for task payments, withdrawals, and other transactions.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-dark-800"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-percentage text-yellow-600 dark:text-yellow-400 text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Commission</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Platform commission and fee settings</p>
            </div>

            <!-- Currency -->
            <div class="group relative bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300">
                <div class="absolute top-3 right-3">
                    <div class="relative">
                        <button type="button" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-dark-700 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" data-tooltip-target="tooltip-currency">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                        <div id="tooltip-currency" class="absolute z-50 hidden group-hover:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-64 p-3 bg-dark-800 text-white text-xs rounded-lg shadow-xl">
                            Manage supported currencies and their exchange rates relative to the base currency.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-dark-800"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-coins text-orange-600 dark:text-orange-400 text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Currency</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Manage supported currencies</p>
            </div>

            <!-- Notifications -->
            <div class="group relative bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300">
                <div class="absolute top-3 right-3">
                    <div class="relative">
                        <button type="button" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-dark-700 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" data-tooltip-target="tooltip-notification">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                        <div id="tooltip-notification" class="absolute z-50 hidden group-hover:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-64 p-3 bg-dark-800 text-white text-xs rounded-lg shadow-xl">
                            Configure email and in-app notifications for users. Set up reminders and alerts.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-dark-800"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-pink-100 dark:bg-pink-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-bell text-pink-600 dark:text-pink-400 text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Notifications</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Email and in-app notification settings</p>
            </div>

            <!-- Security -->
            <div class="group relative bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300">
                <div class="absolute top-3 right-3">
                    <div class="relative">
                        <button type="button" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-dark-700 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" data-tooltip-target="tooltip-security">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                        <div id="tooltip-security" class="absolute z-50 hidden group-hover:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-64 p-3 bg-dark-800 text-white text-xs rounded-lg shadow-xl">
                            Configure security settings including password requirements, session timeouts, and 2FA options.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-dark-800"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-shield-alt text-red-600 dark:text-red-400 text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Security</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Authentication and admin policies</p>
            </div>

            <!-- Registration -->
            <div class="group relative bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300">
                <div class="absolute top-3 right-3">
                    <div class="relative">
                        <button type="button" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-dark-700 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" data-tooltip-target="tooltip-registration">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                        <div id="tooltip-registration" class="absolute z-50 hidden group-hover:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-64 p-3 bg-dark-800 text-white text-xs rounded-lg shadow-xl">
                            Manage user registration, email verification requirements, and referral system settings.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-dark-800"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-cyan-100 dark:bg-cyan-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-user-plus text-cyan-600 dark:text-cyan-400 text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Registration</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">User registration and email verification</p>
            </div>

            <!-- Cron & Jobs -->
            <div class="group relative bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300">
                <div class="absolute top-3 right-3">
                    <div class="relative">
                        <button type="button" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-dark-700 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" data-tooltip-target="tooltip-cron">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                        <div id="tooltip-cron" class="absolute z-50 hidden group-hover:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-64 p-3 bg-dark-800 text-white text-xs rounded-lg shadow-xl">
                            Configure and manually trigger background jobs like task expiration, payouts, and cleanup tasks.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-dark-800"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-clock text-indigo-600 dark:text-indigo-400 text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Cron & Jobs</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Trigger and configure background jobs</p>
            </div>

            <!-- Maintenance -->
            <div class="group relative bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300">
                <div class="absolute top-3 right-3">
                    <div class="relative">
                        <button type="button" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-dark-700 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" data-tooltip-target="tooltip-maintenance">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                        <div id="tooltip-maintenance" class="absolute z-50 hidden group-hover:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-64 p-3 bg-dark-800 text-white text-xs rounded-lg shadow-xl">
                            Put the site into maintenance mode to perform updates or restrict access during emergencies.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-dark-800"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-tools text-gray-600 dark:text-gray-400 text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Maintenance</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Put the site into maintenance mode</p>
            </div>

            <!-- Task Creation Gate -->
            <a href="{{ route('admin.settings.task-gate') }}" class="group relative bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-l-4 border-l-indigo-500 p-6 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300">
                @php
                    $taskGateEnabled = \App\Models\SystemSetting::isMandatoryTaskCreationEnabled();
                @endphp
                <div class="absolute top-3 right-3">
                    <div class="relative">
                        <button type="button" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-dark-700 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" data-tooltip-target="tooltip-taskgate">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                        <div id="tooltip-taskgate" class="absolute z-50 hidden group-hover:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-64 p-3 bg-dark-800 text-white text-xs rounded-lg shadow-xl">
                            Require new users to create tasks before they can start earning. This helps build the task pool.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-dark-800"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-gate text-white text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Task Creation Gate</h3>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $taskGateEnabled ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300' }}">
                        <i class="fas {{ $taskGateEnabled ? 'fa-toggle-on' : 'fa-toggle-off' }} mr-1.5"></i>
                        {{ $taskGateEnabled ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Mandatory task creation requirements</p>
            </a>
        </div>

        <!-- Quick Stats -->
        @if(isset($currencies) || isset($categories))
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            @if(isset($currencies) && $currencies->count())
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Supported Currencies</h4>
                    <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-coins text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($currencies as $currency)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300" title="Exchange rate: {{ $currency->rate_to_ngn }}">
                        {{ $currency->code }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($categories) && $categories->count())
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Task Categories</h4>
                    <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                        <i class="fas fa-tags text-purple-600 dark:text-purple-400"></i>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($categories as $category)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300" title="{{ $category->description ?? 'Task category' }}">
                        {{ $category->name }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
