@extends('layouts.admin')

@section('title', 'Currency Settings')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Currency Settings</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Configure supported currencies and conversion rates</p>
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

        <form action="{{ route('admin.settings.update', 'currency') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Default Display Currency -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-coins text-orange-600 dark:text-orange-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Default Display Currency</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Select the primary currency for displaying prices</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div class="max-w-xs">
                        <label for="default_currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Default Currency
                        </label>
                        <select name="default_currency" id="default_currency" 
                            class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white">
                            <option value="NGN" {{ ($settingsByKey['default_currency'] ?? 'NGN') === 'NGN' ? 'selected' : '' }}>NGN (₦)</option>
                            <option value="USD" {{ ($settingsByKey['default_currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="USDT" {{ ($settingsByKey['default_currency'] ?? '') === 'USDT' ? 'selected' : '' }}>USDT (₮)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Enabled Currencies -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Enabled Currencies</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Toggle which currencies are available on the platform</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- NGN -->
                        <div class="border rounded-xl p-4 {{ ($settingsByKey['currency_ngn_enabled'] ?? true) === 'true' || $settingsByKey['currency_ngn_enabled'] ?? true ? 'border-green-300 dark:border-green-600 bg-green-50 dark:bg-green-500/10' : 'border-gray-200 dark:border-dark-700' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-lg font-medium text-gray-900 dark:text-gray-100">NGN</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="currency_ngn_enabled" value="true"
                                        {{ (($settingsByKey['currency_ngn_enabled'] ?? true) === 'true' || $settingsByKey['currency_ngn_enabled'] ?? true) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                </label>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nigerian Naira</p>
                        </div>

                        <!-- USD -->
                        <div class="border rounded-xl p-4 {{ ($settingsByKey['currency_usd_enabled'] ?? false) === 'true' || $settingsByKey['currency_usd_enabled'] ?? false ? 'border-green-300 dark:border-green-600 bg-green-50 dark:bg-green-500/10' : 'border-gray-200 dark:border-dark-700' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-lg font-medium text-gray-900 dark:text-gray-100">USD</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="currency_usd_enabled" value="true"
                                        {{ (($settingsByKey['currency_usd_enabled'] ?? false) === 'true' || $settingsByKey['currency_usd_enabled'] ?? false) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                </label>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">US Dollar</p>
                        </div>

                        <!-- USDT -->
                        <div class="border rounded-xl p-4 {{ ($settingsByKey['currency_usdt_enabled'] ?? false) === 'true' || $settingsByKey['currency_usdt_enabled'] ?? false ? 'border-green-300 dark:border-green-600 bg-green-50 dark:bg-green-500/10' : 'border-gray-200 dark:border-dark-700' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-lg font-medium text-gray-900 dark:text-gray-100">USDT</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="currency_usdt_enabled" value="true"
                                        {{ (($settingsByKey['currency_usdt_enabled'] ?? false) === 'true' || $settingsByKey['currency_usdt_enabled'] ?? false) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                </label>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tether (Crypto)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manual Conversion Rates -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-exchange-alt text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Manual Conversion Rates</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Set exchange rates for manual currency conversion</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="ngn_to_usd_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                NGN to USD Rate
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400">₦</span>
                                </div>
                                <input type="number" step="0.01" name="ngn_to_usd_rate" id="ngn_to_usd_rate"
                                    value="{{ old('ngn_to_usd_rate', $settingsByKey['ngn_to_usd_rate'] ?? '1500') }}"
                                    class="w-full pl-7 pr-16 rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white"
                                    placeholder="1500">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 text-sm">per $1</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Auto-fetch Rates -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-sync-alt text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Automatic Rate Fetching</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Enable automatic currency rate updates</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Automatic Rate Fetching</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">When enabled, currency rates will be fetched from external APIs</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="auto_fetch_rates" value="true"
                                {{ (($settingsByKey['auto_fetch_rates'] ?? false) === 'true' || $settingsByKey['auto_fetch_rates'] ?? false) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-save mr-2"></i>Save Currency Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
