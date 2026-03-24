@extends('layouts.admin')

@section('title', 'Payment Settings')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Payment Gateways</h1>
                <p class="mt-1 text-sm text-gray-500">Configure Paystack, Kora, and Stripe payment settings</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('admin.settings') }}" class="text-indigo-600 hover:text-indigo-900">
                    ← Back to Settings
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.settings.update', 'payment') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Paystack -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-purple-100 rounded-lg p-2 mr-3">
                            <i class="fas fa-credit-card text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Paystack</h3>
                            <p class="text-sm text-gray-500"> Nigerian Naira (NGN) payments</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="paystack_enabled" value="true"
                                {{ ($settingsByKey['paystack_enabled'] ?? false) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Enabled</span>
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="paystack_public_key" class="block text-sm font-medium text-gray-700">
                                Public Key
                            </label>
                            <input type="text" name="paystack_public_key" id="paystack_public_key"
                                value="{{ old('paystack_public_key', $settingsByKey['paystack_public_key'] ?? '') }}"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="paystack_secret_key" class="block text-sm font-medium text-gray-700">
                                Secret Key
                            </label>
                            <input type="password" name="paystack_secret_key" id="paystack_secret_key"
                                value=""
                                placeholder="Leave blank to keep existing secret"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <p class="mt-1 text-xs text-gray-500">Stored encrypted in database. Leave blank to keep existing.</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="paystack_sandbox" value="true"
                                {{ ($settingsByKey['paystack_sandbox'] ?? true) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Sandbox/Test Mode</span>
                        </label>
                    </div>
                    <div class="flex items-center">
                        <button formaction="{{ route('admin.settings.test-gateway', 'paystack') }}" formmethod="POST" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-vial mr-2"></i> Test Connection
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kora -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-blue-100 rounded-lg p-2 mr-3">
                            <i class="fas fa-university text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Kora</h3>
                            <p class="text-sm text-gray-500">Multi-currency payments</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="kora_enabled" value="true"
                                {{ ($settingsByKey['kora_enabled'] ?? false) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Enabled</span>
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="kora_public_key" class="block text-sm font-medium text-gray-700">
                                Public Key
                            </label>
                            <input type="text" name="kora_public_key" id="kora_public_key"
                                value="{{ old('kora_public_key', $settingsByKey['kora_public_key'] ?? '') }}"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="kora_secret_key" class="block text-sm font-medium text-gray-700">
                                Secret Key
                            </label>
                            <input type="password" name="kora_secret_key" id="kora_secret_key"
                                value=""
                                placeholder="Leave blank to keep existing secret"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <p class="mt-1 text-xs text-gray-500">Stored encrypted in database. Leave blank to keep existing.</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="kora_sandbox" value="true"
                                {{ ($settingsByKey['kora_sandbox'] ?? true) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Sandbox/Test Mode</span>
                        </label>
                    </div>
                    <div class="flex items-center">
                        <button formaction="{{ route('admin.settings.test-gateway', 'kora') }}" formmethod="POST" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-vial mr-2"></i> Test Connection
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stripe -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-indigo-100 rounded-lg p-2 mr-3">
                            <i class="fab fa-stripe text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Stripe</h3>
                            <p class="text-sm text-gray-500">USD & USDT payments</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="stripe_enabled" value="true"
                                {{ ($settingsByKey['stripe_enabled'] ?? false) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Enabled</span>
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="stripe_publishable_key" class="block text-sm font-medium text-gray-700">
                                Publishable Key
                            </label>
                            <input type="text" name="stripe_publishable_key" id="stripe_publishable_key"
                                value="{{ old('stripe_publishable_key', $settingsByKey['stripe_publishable_key'] ?? '') }}"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="stripe_secret_key" class="block text-sm font-medium text-gray-700">
                                Secret Key
                            </label>
                            <input type="password" name="stripe_secret_key" id="stripe_secret_key"
                                value=""
                                placeholder="Leave blank to keep existing secret"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <p class="mt-1 text-xs text-gray-500">Stored encrypted in database. Leave blank to keep existing.</p>
                        </div>
                        <div>
                            <label for="stripe_webhook_secret" class="block text-sm font-medium text-gray-700">
                                Webhook Secret
                            </label>
                            <input type="password" name="stripe_webhook_secret" id="stripe_webhook_secret"
                                value=""
                                placeholder="Leave blank to keep existing webhook secret"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <p class="mt-1 text-xs text-gray-500">Stored encrypted in database. Leave blank to keep existing.</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="stripe_sandbox" value="true"
                                {{ ($settingsByKey['stripe_sandbox'] ?? true) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Sandbox/Test Mode</span>
                        </label>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button formaction="{{ route('admin.settings.test-gateway', 'stripe') }}" formmethod="POST" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-vial mr-2"></i> Test Connection
                        </button>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Security Notice:</strong> All secret keys are encrypted before storage.
                            At least one payment gateway must be enabled for the platform to function.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="fas fa-save mr-2"></i> Save Payment Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
