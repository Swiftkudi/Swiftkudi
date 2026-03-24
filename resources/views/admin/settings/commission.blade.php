@extends('layouts.admin')

@section('title', 'Commission Settings')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Commission & Earnings</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Configure platform fees, commission rates, and earnings splits</p>
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

        <form action="{{ route('admin.settings.update', 'commission') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Platform Commission -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-percentage text-yellow-600 dark:text-yellow-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Platform Commission</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Control how much the platform earns from each transaction</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div>
                        <label for="platform_commission" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Platform Commission (%)
                        </label>
                        <div class="relative">
                            <input type="number" name="platform_commission" id="platform_commission"
                                value="{{ old('platform_commission', $settingsByKey['platform_commission'] ?? 25) }}"
                                min="0" max="100" step="0.01"
                                class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white pr-12">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400">%</span>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Default: 25%. This is deducted from task budgets before calculating worker rewards.
                        </p>
                    </div>

                    <div class="bg-yellow-50 dark:bg-yellow-500/10 border-l-4 border-yellow-400 p-4 rounded-r-xl">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                    <strong>Example:</strong> With a ₦2,500 task budget and 25% commission:<br>
                                    Platform earns: ₦625 | Worker pool: ₦1,875
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings Split -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-chart-pie text-green-600 dark:text-green-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Worker Earnings Split</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">How worker earnings are distributed between withdrawable balance and promo credits</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="withdrawable_split" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Withdrawable Balance (%)
                            </label>
                            <div class="relative">
                                <input type="number" name="withdrawable_split" id="withdrawable_split"
                                    value="{{ old('withdrawable_split', $settingsByKey['withdrawable_split'] ?? 80) }}"
                                    min="0" max="100" step="1"
                                    class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white pr-12">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400">%</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Can be withdrawn anytime</p>
                        </div>

                        <div>
                            <label for="promo_credit_split" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Promo Credit (%)
                            </label>
                            <div class="relative">
                                <input type="number" name="promo_credit_split" id="promo_credit_split"
                                    value="{{ old('promo_credit_split', $settingsByKey['promo_credit_split'] ?? 20) }}"
                                    min="0" max="100" step="1"
                                    class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white pr-12">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400">%</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Can be used for task creation only</p>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="auto_distribute" name="auto_distribute" value="true"
                            {{ (data_get($settingsByKey, 'auto_distribute', true) === 'true' || data_get($settingsByKey, 'auto_distribute', true)) ? 'checked' : '' }}
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-dark-600 rounded dark:bg-dark-800">
                        <label for="auto_distribute" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                            Automatically split earnings on each payout
                        </label>
                    </div>
                </div>
            </div>

            <!-- Minimum Thresholds -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-grip-lines text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Minimum Thresholds</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Minimum amounts required for platform operations</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="minimum_withdrawal" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Minimum Withdrawal (₦)
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400">₦</span>
                                </div>
                                <input type="number" name="minimum_withdrawal" id="minimum_withdrawal"
                                    value="{{ old('minimum_withdrawal', $settingsByKey['minimum_withdrawal'] ?? 1000) }}"
                                    min="0" step="100"
                                    class="w-full pl-8 rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white">
                            </div>
                        </div>

                        <div>
                            <label for="minimum_required_budget" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Minimum Task Budget (₦)
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400">₦</span>
                                </div>
                                <input type="number" name="minimum_required_budget" id="minimum_required_budget"
                                    value="{{ old('minimum_required_budget', $settingsByKey['minimum_required_budget'] ?? 2500) }}"
                                    min="0" step="100"
                                    class="w-full pl-8 rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Withdrawal Fees -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-receipt text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Withdrawal Fees</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Fees charged on withdrawals</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="withdrawal_fee_standard" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Standard Withdrawal Fee (%)
                            </label>
                            <div class="relative">
                                <input type="number" name="withdrawal_fee_standard" id="withdrawal_fee_standard"
                                    value="{{ old('withdrawal_fee_standard', $settingsByKey['withdrawal_fee_standard'] ?? 5) }}"
                                    min="0" max="100" step="0.01"
                                    class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white pr-12">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400">%</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Takes 1-3 business days</p>
                        </div>

                        <div>
                            <label for="withdrawal_fee_instant" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Instant Withdrawal Fee (%)
                            </label>
                            <div class="relative">
                                <input type="number" name="withdrawal_fee_instant" id="withdrawal_fee_instant"
                                    value="{{ old('withdrawal_fee_instant', $settingsByKey['withdrawal_fee_instant'] ?? 10) }}"
                                    min="0" max="100" step="0.01"
                                    class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white pr-12">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400">%</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Instant processing</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-save mr-2"></i>Save Commission Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
