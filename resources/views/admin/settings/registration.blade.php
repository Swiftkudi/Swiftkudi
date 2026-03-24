@extends('layouts.admin')

@section('title', 'Registration Settings')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Registration Settings</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Configure user signup and verification requirements</p>
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

        <form action="{{ route('admin.settings.update', 'registration') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Registration Toggle -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center mr-4">
                            <i class="fas fa-user-plus text-green-600 dark:text-green-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">User Registration</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Allow new users to register on the platform</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="registration_enabled" value="true"
                                {{ (($settingsByKey['registration_enabled'] ?? true) === 'true' || $settingsByKey['registration_enabled'] ?? true) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-500/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Enabled</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Verification Requirements -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-shield-alt text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Verification Requirements</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Configure identity verification for new users</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <!-- Email Verification -->
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center mr-4">
                                <i class="fas fa-envelope text-indigo-600 dark:text-indigo-400"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Email Verification Required</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Users must verify their email address before logging in</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="email_verification_required" value="true"
                                {{ (($settingsByKey['email_verification_required'] ?? true) === 'true' || $settingsByKey['email_verification_required'] ?? true) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-500/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <!-- Admin Approval -->
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center mr-4">
                                <i class="fas fa-user-check text-purple-600 dark:text-purple-400"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Admin Approval Required</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">New users require admin approval before their account is activated</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="admin_approval_required" value="true"
                                {{ (($settingsByKey['admin_approval_required'] ?? false) === 'true' || $settingsByKey['admin_approval_required'] ?? false) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-500/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Referral System -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-user-friends text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Referral System</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Configure the user referral program</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-500/20 flex items-center justify-center mr-4">
                                <i class="fas fa-toggle-on text-green-600 dark:text-green-400"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Enable Referral System</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Allow users to refer others and earn rewards</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="referral_enabled" value="true"
                                {{ (($settingsByKey['referral_enabled'] ?? true) === 'true' || $settingsByKey['referral_enabled'] ?? true) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-500/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Referral Bonus Amount (₦)</label>
                            <input type="number" name="referral_bonus_amount" value="{{ $settingsByKey['referral_bonus_amount'] ?? 500 }}" 
                                class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Bonus earned when referred user activates</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Activation Fee (₦)</label>
                            <input type="number" name="activation_fee" value="{{ $settingsByKey['activation_fee'] ?? 1000 }}" 
                                class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">One-time activation fee for new users</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activation Fee Settings -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-credit-card text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Activation Fee</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Configure activation fee requirements</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <!-- Activation Fee Toggle -->
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center mr-4">
                                <i class="fas fa-toggle-on text-indigo-600 dark:text-indigo-400"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Enable Activation Fee</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Turn on to require payment before activation; turn off to make activation free</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="compulsory_activation_fee" value="true"
                                {{ (($settingsByKey['compulsory_activation_fee'] ?? true) === 'true' || ($settingsByKey['compulsory_activation_fee'] ?? true)) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-500/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <!-- Referred User Discount -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Referred User Discount (₦)</label>
                            <input type="number" name="referred_activation_discount" value="{{ $settingsByKey['referred_activation_discount'] ?? 0 }}" 
                                class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Discount on activation fee for referred users</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Referred Multiplier</label>
                            <input type="number" name="referred_activation_multiplier" value="{{ $settingsByKey['referred_activation_multiplier'] ?? 1.0 }}" step="0.1" min="0" max="2"
                                class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Multiply activation fee (e.g., 0.5 = 50% of normal)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-save mr-2"></i>Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
