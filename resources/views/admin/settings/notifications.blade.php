@extends('layouts.admin')

@section('title', 'Email Notification Messages')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Email Notification Messages</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage email templates and notification messages</p>
            </div>
            <div class="mt-4 md:mt-0 space-x-4">
                <a href="/admin" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Admin Home</a>
                <a href="{{ route('admin.settings') }}" class="text-indigo-600 hover:text-indigo-900">← Back to Settings</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-200 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-200 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Notification Templates -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="bg-purple-100 dark:bg-purple-900 rounded-lg p-2 mr-3">
                        <i class="fas fa-bell text-purple-600 dark:text-purple-300 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Notification Templates</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Customize email messages sent to users</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <form action="{{ route('admin.settings.update', 'notification') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Welcome Email -->
                    <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center">
                            <i class="fas fa-user-plus mr-2 text-green-500"></i>
                            Welcome Email
                            <span class="ml-2 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-2 py-1 rounded">New Registration</span>
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Email sent when a user registers on the platform</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Subject</label>
                                <input type="text" name="notif_welcome_subject" value="{{ old('notif_welcome_subject', $settingsByKey['notif_welcome_subject'] ?? 'Welcome to {{site_name}}!') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Sender Name</label>
                                <input type="text" name="notif_welcome_from_name" value="{{ old('notif_welcome_from_name', $settingsByKey['notif_welcome_from_name'] ?? config('app.name', 'SwiftKudi')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Message Body</label>
                            <textarea name="notif_welcome_body" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('notif_welcome_body', $settingsByKey['notif_welcome_body'] ?? 'Hello {{user_name}},\n\nWelcome to {{site_name}}! We\'re excited to have you on board.\n\nYour referral code: {{referral_code}}\n\nGet started by completing your profile and exploring available tasks.') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Available variables: {{site_name}}, {{user_name}}, {{email}}, {{referral_code}}</p>
                        </div>
                    </div>

                    <!-- Task Approval -->
                    <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center">
                            <i class="fas fa-check-circle mr-2 text-blue-500"></i>
                            Task Approval
                            <span class="ml-2 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-2 py-1 rounded">Task Management</span>
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Email sent when a task is approved</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Subject</label>
                                <input type="text" name="notif_task_approved_subject" value="{{ old('notif_task_approved_subject', $settingsByKey['notif_task_approved_subject'] ?? 'Your Task Has Been Approved!') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Sender Name</label>
                                <input type="text" name="notif_task_approved_from_name" value="{{ old('notif_task_approved_from_name', $settingsByKey['notif_task_approved_from_name'] ?? config('app.name', 'SwiftKudi')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Message Body</label>
                            <textarea name="notif_task_approved_body" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('notif_task_approved_body', $settingsByKey['notif_task_approved_body'] ?? 'Hello {{user_name}},\n\nGreat news! Your task "{{task_title}}" has been approved.\n\nEarnings: {{earnings}}\n\nKeep up the great work!') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Available variables: {{site_name}}, {{user_name}}, {{task_title}}, {{earnings}}, {{task_url}}</p>
                        </div>
                    </div>

                    <!-- Task Rejection -->
                    <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center">
                            <i class="fas fa-times-circle mr-2 text-red-500"></i>
                            Task Rejection
                            <span class="ml-2 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-2 py-1 rounded">Task Management</span>
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Email sent when a task is rejected</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Subject</label>
                                <input type="text" name="notif_task_rejected_subject" value="{{ old('notif_task_rejected_subject', $settingsByKey['notif_task_rejected_subject'] ?? 'Task Update: Not Approved') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Sender Name</label>
                                <input type="text" name="notif_task_rejected_from_name" value="{{ old('notif_task_rejected_from_name', $settingsByKey['notif_task_rejected_from_name'] ?? config('app.name', 'SwiftKudi')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Message Body</label>
                            <textarea name="notif_task_rejected_body" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('notif_task_rejected_body', $settingsByKey['notif_task_rejected_body'] ?? 'Hello {{user_name}},\n\nYour task "{{task_title}}" was not approved.\n\nReason: {{rejection_reason}}\n\nPlease review the feedback and resubmit if needed.') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Available variables: {{site_name}}, {{user_name}}, {{task_title}}, {{rejection_reason}}</p>
                        </div>
                    </div>

                    <!-- Earnings Unlocked -->
                    <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center">
                            <i class="fas fa-coins mr-2 text-yellow-500"></i>
                            Earnings Unlocked
                            <span class="ml-2 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 px-2 py-1 rounded">Wallet</span>
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Email sent when user earnings are unlocked</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Subject</label>
                                <input type="text" name="notif_earnings_unlocked_subject" value="{{ old('notif_earnings_unlocked_subject', $settingsByKey['notif_earnings_unlocked_subject'] ?? 'Your Earnings Are Now Available!') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Sender Name</label>
                                <input type="text" name="notif_earnings_unlocked_from_name" value="{{ old('notif_earnings_unlocked_from_name', $settingsByKey['notif_earnings_unlocked_from_name'] ?? config('app.name', 'SwiftKudi')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Message Body</label>
                            <textarea name="notif_earnings_unlocked_body" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('notif_earnings_unlocked_body', $settingsByKey['notif_earnings_unlocked_body'] ?? 'Hello {{user_name}},\n\nGreat news! Your earnings of {{amount}} have been unlocked and are now available in your wallet.\n\nYou can withdraw these funds or use them for premium tasks.\n\nCurrent Balance: {{wallet_balance}}') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Available variables: {{site_name}}, {{user_name}}, {{amount}}, {{wallet_balance}}</p>
                        </div>
                    </div>

                    <!-- Activation Reminder -->
                    <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center">
                            <i class="fas fa-clock mr-2 text-orange-500"></i>
                            Activation Reminder
                            <span class="ml-2 text-xs bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 px-2 py-1 rounded">Activation</span>
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Email sent as a reminder to activate wallet</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Subject</label>
                                <input type="text" name="notif_activation_reminder_subject" value="{{ old('notif_activation_reminder_subject', $settingsByKey['notif_activation_reminder_subject'] ?? 'Reminder: Complete Your Wallet Activation') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Sender Name</label>
                                <input type="text" name="notif_activation_reminder_from_name" value="{{ old('notif_activation_reminder_from_name', $settingsByKey['notif_activation_reminder_from_name'] ?? config('app.name', 'SwiftKudi')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Message Body</label>
                            <textarea name="notif_activation_reminder_body" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('notif_activation_reminder_body', $settingsByKey['notif_activation_reminder_body'] ?? 'Hello {{user_name}},\n\nThis is a friendly reminder to complete your wallet activation.\n\nActivate now to unlock your earnings and start withdrawing!\n\nActivation Fee: {{activation_fee}}') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Available variables: {{site_name}}, {{user_name}}, {{activation_fee}}, {{activation_url}}</p>
                        </div>
                    </div>

                    <!-- Password Reset -->
                    <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center">
                            <i class="fas fa-key mr-2 text-indigo-500"></i>
                            Password Reset
                            <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded">Account</span>
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Email sent for password reset requests</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Subject</label>
                                <input type="text" name="notif_password_reset_subject" value="{{ old('notif_password_reset_subject', $settingsByKey['notif_password_reset_subject'] ?? 'Reset Your Password') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Sender Name</label>
                                <input type="text" name="notif_password_reset_from_name" value="{{ old('notif_password_reset_from_name', $settingsByKey['notif_password_reset_from_name'] ?? config('app.name', 'SwiftKudi')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Message Body</label>
                            <textarea name="notif_password_reset_body" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('notif_password_reset_body', $settingsByKey['notif_password_reset_body'] ?? 'Hello {{user_name}},\n\nYou requested a password reset. Click the link below to reset your password:\n\n{{reset_link}}\n\nThis link expires in 60 minutes.') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Available variables: {{site_name}}, {{user_name}}, {{reset_link}}</p>
                        </div>
                    </div>

                    <!-- Email Verification -->
                    <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center">
                            <i class="fas fa-envelope-open-text mr-2 text-teal-500"></i>
                            Email Verification
                            <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded">Account</span>
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Email sent for email verification</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Subject</label>
                                <input type="text" name="notif_email_verify_subject" value="{{ old('notif_email_verify_subject', $settingsByKey['notif_email_verify_subject'] ?? 'Verify Your Email Address') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Sender Name</label>
                                <input type="text" name="notif_email_verify_from_name" value="{{ old('notif_email_verify_from_name', $settingsByKey['notif_email_verify_from_name'] ?? config('app.name', 'SwiftKudi')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Message Body</label>
                            <textarea name="notif_email_verify_body" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('notif_email_verify_body', $settingsByKey['notif_email_verify_body'] ?? 'Hello {{user_name}},\n\nPlease verify your email address by clicking the link below:\n\n{{verify_link}}\n\nIf you did not create an account, please ignore this email.') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Available variables: {{site_name}}, {{user_name}}, {{verify_link}}</p>
                        </div>
                    </div>

                    <!-- Withdrawal Notification -->
                    <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center">
                            <i class="fas fa-money-bill-wave mr-2 text-green-500"></i>
                            Withdrawal Processed
                            <span class="ml-2 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 px-2 py-1 rounded">Wallet</span>
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Email sent when a withdrawal is processed</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Subject</label>
                                <input type="text" name="notif_withdrawal_subject" value="{{ old('notif_withdrawal_subject', $settingsByKey['notif_withdrawal_subject'] ?? 'Withdrawal Processed Successfully') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Sender Name</label>
                                <input type="text" name="notif_withdrawal_from_name" value="{{ old('notif_withdrawal_from_name', $settingsByKey['notif_withdrawal_from_name'] ?? config('app.name', 'SwiftKudi')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Message Body</label>
                            <textarea name="notif_withdrawal_body" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('notif_withdrawal_body', $settingsByKey['notif_withdrawal_body'] ?? 'Hello {{user_name}},\n\nYour withdrawal of {{amount}} has been processed successfully.\n\nWithdrawal Method: {{method}}\n\nAmount Received: {{net_amount}}') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Available variables: {{site_name}}, {{user_name}}, {{amount}}, {{method}}, {{net_amount}}</p>
                        </div>
                    </div>

                    <!-- Referral Bonus -->
                    <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center">
                            <i class="fas fa-share-alt mr-2 text-pink-500"></i>
                            Referral Bonus
                            <span class="ml-2 text-xs bg-pink-100 dark:bg-pink-900 text-pink-700 dark:text-pink-300 px-2 py-1 rounded">Referral</span>
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Email sent when a referral bonus is earned</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Subject</label>
                                <input type="text" name="notif_referral_bonus_subject" value="{{ old('notif_referral_bonus_subject', $settingsByKey['notif_referral_bonus_subject'] ?? 'You Earned a Referral Bonus!') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Sender Name</label>
                                <input type="text" name="notif_referral_bonus_from_name" value="{{ old('notif_referral_bonus_from_name', $settingsByKey['notif_referral_bonus_from_name'] ?? config('app.name', 'SwiftKudi')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Message Body</label>
                            <textarea name="notif_referral_bonus_body" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('notif_referral_bonus_body', $settingsByKey['notif_referral_bonus_body'] ?? 'Hello {{user_name}},\n\nCongratulations! You earned a referral bonus of {{bonus_amount}}!\n\nYour referral {{referred_user}} has completed their first task.\n\nShare your referral code to earn more: {{referral_code}}') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Available variables: {{site_name}}, {{user_name}}, {{bonus_amount}}, {{referred_user}}, {{referral_code}}</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-all transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <i class="fas fa-save mr-2"></i>
                            Save Notification Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Email Test Section -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Test Email Configuration</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Send a test email to verify your SMTP settings</p>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.settings.test-email') }}" method="POST" class="flex gap-4 items-end">
                    @csrf
                    <div class="flex-1">
                        <label for="test_email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Test Email Address</label>
                        <input type="email" name="test_email" id="test_email" required placeholder="admin@example.com" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all transform hover:scale-105">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Test Email
                    </button>
                </form>
            </div>
        </div>

        <!-- Send Push Notification Section -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="bg-indigo-100 dark:bg-indigo-900 rounded-lg p-2 mr-3">
                        <i class="fas fa-bullhorn text-indigo-600 dark:text-indigo-300 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Send Push Notification</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Send email or in-app notification to users</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.notifications.send') }}" method="POST">
                    @csrf
                    
                    <!-- Recipient Selection -->
                    <div class="mb-4">
                        <label for="recipient_type" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            <i class="fas fa-users mr-2 text-gray-400"></i>Recipients
                        </label>
                        <select name="recipient_type" id="recipient_type" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all">All Users</option>
                            <option value="active">Active Users (Last 30 days)</option>
                            <option value="inactive">Inactive Users</option>
                            <option value="new">New Users (Last 7 days)</option>
                            <option value="single">Single User</option>
                        </select>
                    </div>

                    <!-- Single User Email (shown when Single User is selected) -->
                    <div class="mb-4" id="single_user_field" style="display: none;">
                        <label for="user_email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            <i class="fas fa-envelope mr-2 text-gray-400"></i>User Email
                        </label>
                        <input type="email" name="user_email" id="user_email" placeholder="user@example.com" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Notification Title -->
                    <div class="mb-4">
                        <label for="notif_title" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            <i class="fas fa-heading mr-2 text-gray-400"></i>Notification Title
                        </label>
                        <input type="text" name="notif_title" id="notif_title" required placeholder="Important Update" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Notification Message -->
                    <div class="mb-4">
                        <label for="notif_message" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            <i class="fas fa-comment mr-2 text-gray-400"></i>Message
                        </label>
                        <textarea name="notif_message" id="notif_message" rows="4" required placeholder="Enter your notification message here..." class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <!-- Notification Type -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            <i class="fas fa-paper-plane mr-2 text-gray-400"></i>Send Via
                        </label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="send_via[]" value="email" checked class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                                <span class="ml-2 text-gray-700 dark:text-gray-200">Email</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="send_via[]" value="database" checked class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                                <span class="ml-2 text-gray-700 dark:text-gray-200">In-App Notification</span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-all transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Send Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.getElementById('recipient_type').addEventListener('change', function() {
                var singleUserField = document.getElementById('single_user_field');
                if (this.value === 'single') {
                    singleUserField.style.display = 'block';
                } else {
                    singleUserField.style.display = 'none';
                }
            });
        </script>
    </div>
</div>
@endsection
