@extends('layouts.admin')

@section('title', 'Notification Settings')

@section('content')
<div class="py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Notification Settings</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Configure notification preferences and delivery channels</p>
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

            <!-- Global Notification Settings -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-bell text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Global Notification Settings</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Master controls for all notification systems</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Enable All Notifications</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Master switch for the entire notification system</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notifications_enabled" value="true" id="notifications_enabled"
                                {{ (data_get($settingsByKey, 'notifications_enabled', true) === 'true' || data_get($settingsByKey, 'notifications_enabled', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">In-App Notifications</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Show notifications within the application</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_in_app_enabled" value="true" id="notify_in_app_enabled"
                                {{ (data_get($settingsByKey, 'notify_in_app_enabled', true) === 'true' || data_get($settingsByKey, 'notify_in_app_enabled', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Email Notifications</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Send notifications via email</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_email_enabled" value="true" id="notify_email_enabled"
                                {{ (data_get($settingsByKey, 'notify_email_enabled', true) === 'true' || data_get($settingsByKey, 'notify_email_enabled', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-dark-700">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Push Notifications</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Send browser push notifications</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_push_enabled" value="true" id="notify_push_enabled"
                                {{ (data_get($settingsByKey, 'notify_push_enabled', false) === 'true' || data_get($settingsByKey, 'notify_push_enabled', false)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-3">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Admin Activity Notifications</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notify admins about user activities</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_admin_activity" value="true" id="notify_admin_activity"
                                {{ (data_get($settingsByKey, 'notify_admin_activity', true) === 'true' || data_get($settingsByKey, 'notify_admin_activity', true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Task-Related Notifications -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-tasks text-green-600 dark:text-green-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Task Notifications</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notifications related to task creation, completion, and management</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-6">
                    <!-- Task Approved -->
                    <div class="border border-gray-200 dark:border-dark-700 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Task Submission Approved</h4>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notify_task_approved" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_approved', true) === 'true' || data_get($settingsByKey, 'notify_task_approved', true)) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Notify workers when their task submissions are approved</p>
                        <div class="grid grid-cols-3 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_task_approved_in_app" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_approved_in_app', true) === 'true' || data_get($settingsByKey, 'notify_task_approved_in_app', true)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-green-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">In-App</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_task_approved_email" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_approved_email', true) === 'true' || data_get($settingsByKey, 'notify_task_approved_email', true)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-green-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Email</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_task_approved_push" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_approved_push', false) === 'true' || data_get($settingsByKey, 'notify_task_approved_push', false)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-green-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Push</span>
                            </label>
                        </div>
                    </div>

                    <!-- Task Rejected -->
                    <div class="border border-gray-200 dark:border-dark-700 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Task Submission Rejected</h4>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notify_task_rejected" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_rejected', true) === 'true' || data_get($settingsByKey, 'notify_task_rejected', true)) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Notify workers when their task submissions are rejected</p>
                        <div class="grid grid-cols-3 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_task_rejected_in_app" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_rejected_in_app', true) === 'true' || data_get($settingsByKey, 'notify_task_rejected_in_app', true)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-red-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">In-App</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_task_rejected_email" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_rejected_email', true) === 'true' || data_get($settingsByKey, 'notify_task_rejected_email', true)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-red-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Email</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_task_rejected_push" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_rejected_push', false) === 'true' || data_get($settingsByKey, 'notify_task_rejected_push', false)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-red-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Push</span>
                            </label>
                        </div>
                    </div>

                    <!-- Task Created -->
                    <div class="border border-gray-200 dark:border-dark-700 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Task Created</h4>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notify_task_created" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_created', true) === 'true' || data_get($settingsByKey, 'notify_task_created', true)) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Notify task creators when their tasks are successfully created</p>
                        <div class="grid grid-cols-3 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_task_created_in_app" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_created_in_app', true) === 'true' || data_get($settingsByKey, 'notify_task_created_in_app', true)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">In-App</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_task_created_email" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_created_email', true) === 'true' || data_get($settingsByKey, 'notify_task_created_email', true)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Email</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_task_created_push" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_created_push', false) === 'true' || data_get($settingsByKey, 'notify_task_created_push', false)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Push</span>
                            </label>
                        </div>
                    </div>

                    <!-- Task Bundle Available -->
                    <div class="border border-gray-200 dark:border-dark-700 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">New Task Bundles</h4>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notify_task_bundle_available" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_bundle_available', true) === 'true' || data_get($settingsByKey, 'notify_task_bundle_available', true)) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Notify workers when new task bundles become available</p>
                        <div class="grid grid-cols-3 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_task_bundle_available_in_app" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_bundle_available_in_app', true) === 'true' || data_get($settingsByKey, 'notify_task_bundle_available_in_app', true)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-purple-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">In-App</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_task_bundle_available_email" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_bundle_available_email', true) === 'true' || data_get($settingsByKey, 'notify_task_bundle_available_email', true)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-purple-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Email</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_task_bundle_available_push" value="true"
                                    {{ (data_get($settingsByKey, 'notify_task_bundle_available_push', false) === 'true' || data_get($settingsByKey, 'notify_task_bundle_available_push', false)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-purple-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Push</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Notifications -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-naira-sign text-yellow-600 dark:text-yellow-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Financial Notifications</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notifications related to earnings, withdrawals, and payments</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-6">
                    <!-- Withdrawal Status -->
                    <div class="border border-gray-200 dark:border-dark-700 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Withdrawal Status Updates</h4>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notify_withdrawal_status" value="true"
                                    {{ (data_get($settingsByKey, 'notify_withdrawal_status', true) === 'true' || data_get($settingsByKey, 'notify_withdrawal_status', true)) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 dark:peer-focus:ring-yellow-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-600"></div>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Notify users about withdrawal request status changes</p>
                        <div class="grid grid-cols-3 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_withdrawal_status_in_app" value="true"
                                    {{ (data_get($settingsByKey, 'notify_withdrawal_status_in_app', true) === 'true' || data_get($settingsByKey, 'notify_withdrawal_status_in_app', true)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-yellow-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">In-App</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_withdrawal_status_email" value="true"
                                    {{ (data_get($settingsByKey, 'notify_withdrawal_status_email', true) === 'true' || data_get($settingsByKey, 'notify_withdrawal_status_email', true)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-yellow-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Email</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_withdrawal_status_push" value="true"
                                    {{ (data_get($settingsByKey, 'notify_withdrawal_status_push', false) === 'true' || data_get($settingsByKey, 'notify_withdrawal_status_push', false)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-yellow-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Push</span>
                            </label>
                        </div>
                    </div>

                    <!-- Referral Bonus -->
                    <div class="border border-gray-200 dark:border-dark-700 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Referral Bonuses</h4>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notify_referral_bonus" value="true"
                                    {{ (data_get($settingsByKey, 'notify_referral_bonus', true) === 'true' || data_get($settingsByKey, 'notify_referral_bonus', true)) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-cyan-300 dark:peer-focus:ring-cyan-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-600"></div>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Notify users when they earn referral bonuses</p>
                        <div class="grid grid-cols-3 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_referral_bonus_in_app" value="true"
                                    {{ (data_get($settingsByKey, 'notify_referral_bonus_in_app', true) === 'true' || data_get($settingsByKey, 'notify_referral_bonus_in_app', true)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-cyan-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">In-App</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_referral_bonus_email" value="true"
                                    {{ (data_get($settingsByKey, 'notify_referral_bonus_email', true) === 'true' || data_get($settingsByKey, 'notify_referral_bonus_email', true)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-cyan-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Email</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_referral_bonus_push" value="true"
                                    {{ (data_get($settingsByKey, 'notify_referral_bonus_push', false) === 'true' || data_get($settingsByKey, 'notify_referral_bonus_push', false)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-cyan-600">
                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Push</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-save mr-2"></i>Save Notification Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
