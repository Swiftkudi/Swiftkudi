@extends('layouts.admin')

@section('title', 'Notification Settings')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Notification Settings</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Configure email and system notification preferences</p>
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

        <form action="{{ route('admin.settings.update', 'notification') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Email Notifications -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-pink-100 dark:bg-pink-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-envelope text-pink-600 dark:text-pink-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Email Notifications</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Control which events trigger email notifications to users</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Enable In-App Notifications</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Master switch for all in-app notifications</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_in_app_enabled" value="true"
                                {{ (data_get($settingsByKey, 'notify_in_app_enabled', true) === 'true' || data_get($settingsByKey, 'notify_in_app_enabled', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Enable Email Notifications</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Master switch for all email notifications</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_email_enabled" value="true"
                                {{ (data_get($settingsByKey, 'notify_email_enabled', true) === 'true' || data_get($settingsByKey, 'notify_email_enabled', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Task Approval</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify workers when their task submission is approved</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_task_approval" value="true"
                                {{ (data_get($settingsByKey, 'notify_task_approval', true) === 'true' || data_get($settingsByKey, 'notify_task_approval', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Task Rejection</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify workers when their task submission is rejected</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_task_rejection" value="true"
                                {{ (data_get($settingsByKey, 'notify_task_rejection', true) === 'true' || data_get($settingsByKey, 'notify_task_rejection', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">New Task Bundles</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify workers when new task bundles are available</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_task_bundle" value="true"
                                {{ (data_get($settingsByKey, 'notify_task_bundle', true) === 'true' || data_get($settingsByKey, 'notify_task_bundle', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Referral Bonus</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify users when they earn referral bonuses</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_referral_bonus" value="true"
                                {{ (data_get($settingsByKey, 'notify_referral_bonus', true) === 'true' || data_get($settingsByKey, 'notify_referral_bonus', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Withdrawal Updates</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify users about withdrawal status changes</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_withdrawal" value="true"
                                {{ (data_get($settingsByKey, 'notify_withdrawal', true) === 'true' || data_get($settingsByKey, 'notify_withdrawal', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-t border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Task Created</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify users when task creation is successful</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_task_created" value="true"
                                {{ (data_get($settingsByKey, 'notify_task_created', true) === 'true' || data_get($settingsByKey, 'notify_task_created', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-t border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Service Orders</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify buyer and seller on professional service order activity</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_service_orders" value="true"
                                {{ (data_get($settingsByKey, 'notify_service_orders', true) === 'true' || data_get($settingsByKey, 'notify_service_orders', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-t border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Growth Orders</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify buyer and seller on growth order activity</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_growth_orders" value="true"
                                {{ (data_get($settingsByKey, 'notify_growth_orders', true) === 'true' || data_get($settingsByKey, 'notify_growth_orders', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-t border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Product Orders</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify buyer and seller on digital product purchases</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_product_orders" value="true"
                                {{ (data_get($settingsByKey, 'notify_product_orders', true) === 'true' || data_get($settingsByKey, 'notify_product_orders', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-t border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Chat Messages</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify users for new marketplace chat messages</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_chat_messages" value="true"
                                {{ (data_get($settingsByKey, 'notify_chat_messages', true) === 'true' || data_get($settingsByKey, 'notify_chat_messages', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Admin Notifications -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-shield-alt text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Admin Notifications</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Get notified about important platform events</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Large Withdrawals</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify admin for withdrawals above threshold</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_large_withdrawal" value="true"
                                {{ (data_get($settingsByKey, 'notify_large_withdrawal', true) === 'true' || data_get($settingsByKey, 'notify_large_withdrawal', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Large Withdrawal Threshold</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Minimum amount that triggers admin large-withdrawal alert</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">₦</span>
                            <input type="number" name="large_withdrawal_threshold"
                                value="{{ old('large_withdrawal_threshold', data_get($settingsByKey, 'large_withdrawal_threshold', 50000)) }}"
                                class="w-28 rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white text-center">
                        </div>
                    </div>

                    <div class="flex items-center justify-between py-3">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Fraud Alerts</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify admin when suspicious activity is detected</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="admin_fraud_alerts" value="true"
                                {{ (data_get($settingsByKey, 'admin_fraud_alerts', true) === 'true' || data_get($settingsByKey, 'admin_fraud_alerts', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-t border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">All Marketplace Activity</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify admins for task creation, orders, purchases, and chat activity</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_admin_all_activity" value="true"
                                {{ (data_get($settingsByKey, 'notify_admin_all_activity', true) === 'true' || data_get($settingsByKey, 'notify_admin_all_activity', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-save mr-2"></i>Save Notification Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
