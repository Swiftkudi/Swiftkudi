@extends('layouts.admin')

@section('title', 'Maintenance Mode')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Maintenance Mode</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Put the platform in maintenance mode for updates</p>
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

        @if(session('error'))
            <div class="mb-6 bg-red-100 dark:bg-red-500/20 border border-red-400 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.settings.update', 'maintenance') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Maintenance Mode Status -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-tools text-gray-600 dark:text-gray-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Maintenance Status</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Current maintenance mode status</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5">
                    @php
                        $maintenanceEnabled = \App\Models\SystemSetting::isMaintenanceModeEnabled();
                    @endphp
                    <div class="flex items-center justify-between p-4 rounded-xl {{ $maintenanceEnabled ? 'bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30' : 'bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($maintenanceEnabled)
                                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                                @else
                                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                                @endif
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium {{ $maintenanceEnabled ? 'text-red-800 dark:text-red-400' : 'text-green-800 dark:text-green-400' }}">
                                    {{ $maintenanceEnabled ? 'Maintenance Mode is ENABLED' : 'System is Running Normally' }}
                                </h4>
                                <p class="text-sm {{ $maintenanceEnabled ? 'text-red-600 dark:text-red-300' : 'text-green-600 dark:text-green-300' }}">
                                    @if($maintenanceEnabled)
                                        The platform is currently in maintenance mode. Regular users cannot access it.
                                    @else
                                        The platform is live and accessible to all users.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="maintenance_mode_enabled" value="true"
                                    {{ $maintenanceEnabled ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-14 h-8 bg-gray-200 dark:bg-dark-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-7 after:w-7 after:transition-all peer-checked:bg-red-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Enable</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Message -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-comment-alt text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Maintenance Message</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Custom message displayed to users during maintenance</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div>
                        <label for="maintenance_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Message
                        </label>
                        <textarea id="maintenance_message" name="maintenance_message" rows="3"
                            class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white"
                            placeholder="We are performing scheduled maintenance. Please check back shortly.">{{ old('maintenance_message', $settingsByKey['maintenance_message'] ?? 'We are performing scheduled maintenance. Please check back shortly.') }}</textarea>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">This message will be displayed to all users (except admins) when maintenance mode is enabled.</p>
                    </div>
                </div>
            </div>

            <!-- Admin Access -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-user-shield text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Admin Access</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Configure admin access during maintenance mode</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-500/10 rounded-xl border border-blue-200 dark:border-blue-500/30">
                        <div>
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300">Always Allow Admin Login</h4>
                            <p class="text-sm text-blue-600 dark:text-blue-400">Administrators can always access the admin panel even during maintenance mode.</p>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-user-shield text-blue-500 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 {{ $maintenanceEnabled ? 'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700' : 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700' }} text-white font-semibold rounded-xl transition-all shadow-lg {{ $maintenanceEnabled ? 'shadow-green-500/30' : 'shadow-indigo-500/30' }}">
                    <i class="fas fa-save mr-2"></i>{{ $maintenanceEnabled ? 'Update Maintenance Settings' : 'Save & Enable Maintenance' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
