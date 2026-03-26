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

            <!-- Development Settings -->
            <div class="bg-white shadow rounded-lg mb-6 mt-6">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-green-100 rounded-lg p-2 mr-3">
                            <i class="fas fa-tools text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Development Settings</h3>
                            <p class="text-sm text-gray-500">Local development and testing configuration</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <!-- Mock Mode -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-flask mr-1 text-purple-500"></i> Mock Mode
                            </label>
                            <p class="text-xs text-gray-500 mt-1">
                                Enable to simulate payments without calling real gateway APIs. 
                                Perfect for local development and testing.
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="payment_mock_enabled" value="true"
                                {{ ($settingsByKey['payment_mock_enabled'] ?? false) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Enabled</span>
                        </label>
                    </div>

                    <!-- Auto Sandbox -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-cloud mr-1 text-yellow-500"></i> Auto Sandbox Mode
                            </label>
                            <p class="text-xs text-gray-500 mt-1">
                                Automatically use sandbox mode when running on local environment (localhost).
                                Disable this to use live API in local development.
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="payment_sandbox_auto" value="true"
                                {{ ($settingsByKey['payment_sandbox_auto'] ?? true) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Enabled</span>
                        </label>
                    </div>

                    <!-- Custom Callback URL -->
                    <div>
                        <label for="payment_callback_url" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-link mr-1 text-blue-500"></i> Custom Callback URL
                        </label>
                        <p class="text-xs text-gray-500 mt-1 mb-2">
                            Override the default payment callback URL. Useful for local testing with ngrok or similar.
                        </p>
                        <input type="text" name="payment_callback_url" id="payment_callback_url"
                            value="{{ old('payment_callback_url', $settingsByKey['payment_callback_url'] ?? '') }}"
                            placeholder="https://your-domain.com/payment/callback"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <!-- Mode Status Indicator -->
                    @php
                        $paymentService = app(\App\Services\PaymentGatewayService::class);
                        $isMock = $paymentService->isMockMode();
                        $currentGateway = $paymentService->getGateway();
                        $currentMode = $paymentService->getMode();
                    @endphp
                    <div class="p-4 rounded-lg {{ $isMock ? 'bg-purple-50 border border-purple-200' : (app()->environment('local') ? 'bg-yellow-50 border border-yellow-200' : 'bg-green-50 border border-green-200') }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($isMock)
                                    <i class="fas fa-flask text-purple-500"></i>
                                @elseif(app()->environment('local'))
                                    <i class="fas fa-cloud text-yellow-500"></i>
                                @else
                                    <i class="fas fa-check-circle text-green-500"></i>
                                @endif
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">
                                    <strong>Current Mode:</strong> 
                                    @if($isMock)
                                        <span class="text-purple-600">MOCK (Simulated Payments)</span>
                                    @elseif(app()->environment('local'))
                                        <span class="text-yellow-600">SANDBOX (Test API)</span>
                                    @else
                                        <span class="text-green-600">LIVE (Production)</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Environment: {{ app()->environment() }} | 
                                    Gateway: {{ $currentGateway }} | 
                                    Mode: {{ $currentMode }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button (repeated for Development Settings) -->
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="fas fa-save mr-2"></i> Save Payment Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
