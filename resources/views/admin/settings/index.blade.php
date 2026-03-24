@extends('layouts.admin')

@section('title', 'General Settings')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">System Settings</h1>
                <p class="mt-1 text-sm text-gray-500">Manage platform configuration and preferences</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <form action="{{ route('admin.settings.initialize') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-sync-alt mr-2"></i> Initialize Defaults
                    </button>
                </form>
                <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-cache mr-2"></i> Clear Cache
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Settings Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- General Settings -->
            <a href="{{ route('admin.settings.group', 'general') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                            <i class="fas fa-cog text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-900">General</dt>
                                <dd class="text-sm text-gray-500">Site name, URL, branding</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                            {{ $settingsCounts['general'] ?? 0 }} settings
                        </span>
                    </div>
                </div>
            </a>

            <!-- Registration Settings -->
            <a href="{{ route('admin.settings.group', 'registration') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                            <i class="fas fa-user-plus text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-900">Registration</dt>
                                <dd class="text-sm text-gray-500">User signup & activation</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                            {{ $settingsCounts['registration'] ?? 0 }} settings
                        </span>
                    </div>
                </div>
            </a>

            <!-- Commission Settings -->
            <a href="{{ route('admin.settings.group', 'commission') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                            <i class="fas fa-percentage text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-900">Commission & Earnings</dt>
                                <dd class="text-sm text-gray-500">Platform fees & splits</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">
                            {{ $settingsCounts['commission'] ?? 0 }} settings
                        </span>
                    </div>
                </div>
            </a>

            <!-- Payment Gateways -->
            <a href="{{ route('admin.settings.group', 'payment') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                            <i class="fas fa-credit-card text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-900">Payment Gateways</dt>
                                <dd class="text-sm text-gray-500">Paystack, Kora, Stripe</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">
                            {{ $settingsCounts['payment'] ?? 0 }} settings
                        </span>
                    </div>
                </div>
            </a>

            <!-- Currency Settings -->
            <a href="{{ route('admin.settings.group', 'currency') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                            <i class="fas fa-money-bill-wave text-indigo-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-900">Currency</dt>
                                <dd class="text-sm text-gray-500">NGN, USD, USDT settings</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full">
                            {{ $settingsCounts['currency'] ?? 0 }} settings
                        </span>
                    </div>
                </div>
            </a>

            <!-- SMTP / Email -->
            <a href="{{ route('admin.settings.group', 'smtp') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                            <i class="fas fa-envelope text-indigo-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-900">Email / SMTP</dt>
                                <dd class="text-sm text-gray-500">Mail configuration</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full">
                            {{ $settingsCounts['smtp'] ?? 0 }} settings
                        </span>
                    </div>
                </div>
            </a>

            <!-- Security -->
            <a href="{{ route('admin.settings.group', 'security') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gray-100 rounded-md p-3">
                            <i class="fas fa-shield-alt text-gray-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-900">Security</dt>
                                <dd class="text-sm text-gray-500">Fraud prevention & limits</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                            {{ $settingsCounts['security'] ?? 0 }} settings
                        </span>
                    </div>
                </div>
            </a>

            <!-- Cron Jobs -->
            <a href="{{ route('admin.settings.group', 'cron') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-orange-100 rounded-md p-3">
                            <i class="fas fa-clock text-orange-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-900">Cron Jobs</dt>
                                <dd class="text-sm text-gray-500">Scheduled tasks control</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">
                            {{ $settingsCounts['cron'] ?? 0 }} settings
                        </span>
                    </div>
                </div>
            </a>

            <!-- Notifications -->
            <a href="{{ route('admin.settings.group', 'notification') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-pink-100 rounded-md p-3">
                            <i class="fas fa-bell text-pink-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-900">Notifications</dt>
                                <dd class="text-sm text-gray-500">Email & alert preferences</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        <span class="bg-pink-100 text-pink-800 text-xs px-2 py-1 rounded-full">
                            {{ $settingsCounts['notification'] ?? 0 }} settings
                        </span>
                    </div>
                </div>
            </a>

            <!-- Maintenance Mode -->
            <a href="{{ route('admin.settings.group', 'maintenance') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                            <i class="fas fa-tools text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-900">Maintenance</dt>
                                <dd class="text-sm text-gray-500">System maintenance mode</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        @php
                            $maintenance = \App\Models\SystemSetting::isMaintenanceModeEnabled();
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs {{ $maintenance ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $maintenance ? 'Maintenance ON' : 'Maintenance OFF' }}
                        </span>
                    </div>
                </div>
            </a>

            <!-- Audit Logs -->
            <a href="{{ route('admin.settings.audit') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-teal-100 rounded-md p-3">
                            <i class="fas fa-history text-teal-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-900">Audit Logs</dt>
                                <dd class="text-sm text-gray-500">Settings change history</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        <span class="text-indigo-600 hover:text-indigo-900">
                            View all logs →
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <!-- System Info -->
        <div class="mt-8 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">System Information</h3>
            </div>
            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Laravel Version</dt>
                        <dd class="text-sm text-gray-900">{{ app()->version() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">PHP Version</dt>
                        <dd class="text-sm text-gray-900">{{ PHP_VERSION }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Environment</dt>
                        <dd class="text-sm text-gray-900">{{ app()->environment() }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
