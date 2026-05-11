@extends('layouts.admin')

@section('title', 'Escrow Settings')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">Escrow Settings</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Configure escrow behavior, release rules, and dispute handling</p>
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

        <form action="{{ route('admin.settings.update', 'escrow') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Escrow Enable/Disable -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-shield-alt text-emerald-600 dark:text-emerald-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Escrow System</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Enable or disable the escrow system globally</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="escrow_enabled" class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                Enable Escrow System
                            </label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">When disabled, payments are processed directly without escrow</p>
                        </div>
                        <div class="relative">
                            <input type="checkbox" id="escrow_enabled" name="escrow_enabled" value="true"
                                {{ (data_get($settingsByKey, 'escrow_enabled', true) === 'true' || data_get($settingsByKey, 'escrow_enabled', true)) ? 'checked' : '' }}
                                class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-dark-600 rounded dark:bg-dark-800">
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <div>
                            <label for="escrow_require_approval" class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                Require Admin Approval
                            </label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Admin must approve escrow releases manually</p>
                        </div>
                        <div class="relative">
                            <input type="checkbox" id="escrow_require_approval" name="escrow_require_approval" value="true"
                                {{ (data_get($settingsByKey, 'escrow_require_approval', false) === 'true' || data_get($settingsByKey, 'escrow_require_approval', false)) ? 'checked' : '' }}
                                class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-dark-600 rounded dark:bg-dark-800">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Platform Fee -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-percentage text-yellow-600 dark:text-yellow-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Platform Fee</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Commission taken from escrow transactions</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div>
                        <label for="escrow_platform_fee_percent" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Escrow Platform Fee (%)
                        </label>
                        <div class="relative">
                            <input type="number" name="escrow_platform_fee_percent" id="escrow_platform_fee_percent"
                                value="{{ old('escrow_platform_fee_percent', $settingsByKey['escrow_platform_fee_percent'] ?? 10) }}"
                                min="0" max="100" step="0.01"
                                class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white pr-12">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400">%</span>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Default: 10%. This fee is deducted from the escrow amount before releasing to seller.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Release Rules -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-clock text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Release Rules</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Automatic release and acceptance settings</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="escrow_auto_release_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Auto-Release After Delivery (Days)
                            </label>
                            <input type="number" name="escrow_auto_release_days" id="escrow_auto_release_days"
                                value="{{ old('escrow_auto_release_days', $settingsByKey['escrow_auto_release_days'] ?? 7) }}"
                                min="1" max="90" step="1"
                                class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white">
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Funds auto-release if buyer doesn't respond</p>
                        </div>

                        <div>
                            <label for="escrow_auto_accept_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Auto-Accept Delivery (Days)
                            </label>
                            <input type="number" name="escrow_auto_accept_days" id="escrow_auto_accept_days"
                                value="{{ old('escrow_auto_accept_days', $settingsByKey['escrow_auto_accept_days'] ?? 3) }}"
                                min="1" max="30" step="1"
                                class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white">
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Auto-accept if no revision requested</p>
                        </div>
                    </div>

                    <div>
                        <label for="escrow_max_revision_cycles" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Maximum Revision Cycles
                        </label>
                        <input type="number" name="escrow_max_revision_cycles" id="escrow_max_revision_cycles"
                            value="{{ old('escrow_max_revision_cycles', $settingsByKey['escrow_max_revision_cycles'] ?? 3) }}"
                            min="0" max="20" step="1"
                            class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white">
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Number of revision requests allowed before forcing completion</p>
                    </div>
                </div>
            </div>

            <!-- Dispute Handling -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Dispute Handling</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Rules for handling disputes and refunds</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div>
                        <label for="escrow_dispute_window_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Dispute Window (Days)
                        </label>
                        <input type="number" name="escrow_dispute_window_days" id="escrow_dispute_window_days"
                            value="{{ old('escrow_dispute_window_days', $settingsByKey['escrow_dispute_window_days'] ?? 14) }}"
                            min="1" max="90" step="1"
                            class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white">
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Time after delivery when buyers can open disputes</p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="escrow_partial_refund_allowed" name="escrow_partial_refund_allowed" value="true"
                            {{ (data_get($settingsByKey, 'escrow_partial_refund_allowed', true) === 'true' || data_get($settingsByKey, 'escrow_partial_refund_allowed', true)) ? 'checked' : '' }}
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-dark-600 rounded dark:bg-dark-800">
                        <label for="escrow_partial_refund_allowed" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                            Allow Partial Refunds
                        </label>
                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">(Enable admins to refund partial amounts)</span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-700 hover:to-teal-700 transition-all shadow-lg shadow-emerald-500/30">
                    <i class="fas fa-save mr-2"></i>Save Escrow Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection