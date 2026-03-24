@extends('layouts.admin')

@section('title', 'Security Settings')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Security Settings</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Configure fraud prevention, rate limiting, and security controls</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('admin.settings') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Settings
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-100 dark:bg-green-500/20 border border-green-400 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.settings.update', 'security') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Account Security -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-user-shield text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Account Security</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Prevent duplicate accounts and abuse</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">IP Tracking</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Track IP addresses for user accounts</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="ip_tracking_enabled" value="true"
                                {{ (($settingsByKey['ip_tracking_enabled'] ?? true) === 'true' || $settingsByKey['ip_tracking_enabled'] ?? true) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Device Fingerprinting</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Identify unique devices for fraud detection</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="device_fingerprinting_enabled" value="true"
                                {{ (($settingsByKey['device_fingerprinting_enabled'] ?? true) === 'true' || $settingsByKey['device_fingerprinting_enabled'] ?? true) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div>
                        <label for="max_accounts_per_ip" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Maximum Accounts per IP
                        </label>
                        <input type="number" name="max_accounts_per_ip" id="max_accounts_per_ip"
                            value="{{ old('max_accounts_per_ip', $settingsByKey['max_accounts_per_ip'] ?? 3) }}"
                            min="1" max="10"
                            class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white">
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Prevent multiple accounts from same IP address</p>
                    </div>
                </div>
            </div>

            <!-- Task Security -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-tasks text-orange-600 dark:text-orange-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Task Security</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Prevent fraud in task completion</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Self-Task Prevention</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Prevent users from completing their own tasks</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="self_task_prevention" value="true"
                                {{ (($settingsByKey['self_task_prevention'] ?? true) === 'true' || $settingsByKey['self_task_prevention'] ?? true) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Fraud Auto-Flagging</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Automatically flag suspicious activity patterns</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="fraud_auto_flagging" value="true"
                                {{ (($settingsByKey['fraud_auto_flagging'] ?? true) === 'true' || $settingsByKey['fraud_auto_flagging'] ?? true) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Rate Limiting -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-tachometer-alt text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Rate Limiting</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Control API and request frequency</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between py-3">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Enable Rate Limiting</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Limit repeated requests from same IP/user</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="rate_limiting_enabled" value="true"
                                {{ (($settingsByKey['rate_limiting_enabled'] ?? true) === 'true' || $settingsByKey['rate_limiting_enabled'] ?? true) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-save mr-2"></i>Save Security Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
