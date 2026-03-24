@extends('admin.layout')

@section('title', 'Feature Toggles - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.marketplace.index') }}" class="text-indigo-600 hover:text-indigo-500 flex items-center gap-2 mb-4">
                <i class="fas fa-arrow-left"></i> Back to Marketplace
            </a>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                Feature Toggles
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Enable or disable marketplace features and modules
            </p>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($features as $key => $feature)
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $feature['name'] }}</h3>
                            <p class="mt-2 text-gray-500 dark:text-gray-400 text-sm">{{ $feature['description'] }}</p>
                            
                            <div class="mt-4 flex items-center gap-2">
                                @if($feature['enabled'])
                                    <span class="px-3 py-1 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i> Active
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-dark-700 dark:text-gray-400 rounded-full">
                                        <i class="fas fa-times-circle mr-1"></i> Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <form method="POST" action="{{ route('admin.marketplace.toggle-feature') }}" class="ml-4">
                            @csrf
                            <input type="hidden" name="feature" value="{{ $key }}">
                            <input type="hidden" name="enabled" value="{{ $feature['enabled'] ? 'false' : 'true' }}">
                            <button type="submit" 
                                class="relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                {{ $feature['enabled'] ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-dark-700' }}">
                                <span class="sr-only">Enable {{ $feature['name'] }}</span>
                                <span class="inline-block h-6 w-6 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out
                                    {{ $feature['enabled'] ? 'translate-x-7' : 'translate-x-1' }}">
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Info Box -->
        <div class="mt-8 p-6 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-100 dark:border-blue-800">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-300">Feature Toggle Information</h3>
                    <p class="mt-2 text-blue-700 dark:text-blue-400">
                        Disabling a feature will hide it from the platform but preserve all associated data. 
                        Users will no longer be able to access or use disabled features. Re-enabling will restore full functionality.
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.settings') }}" class="bg-white dark:bg-dark-900 rounded-xl p-4 text-center border border-gray-200 dark:border-dark-700 hover:border-indigo-500 transition-all">
                <i class="fas fa-cog text-indigo-600 text-2xl mb-2"></i>
                <p class="font-medium text-gray-900 dark:text-white">Platform Settings</p>
            </a>
            <a href="{{ route('admin.analytics') }}" class="bg-white dark:bg-dark-900 rounded-xl p-4 text-center border border-gray-200 dark:border-dark-700 hover:border-indigo-500 transition-all">
                <i class="fas fa-chart-pie text-purple-600 text-2xl mb-2"></i>
                <p class="font-medium text-gray-900 dark:text-white">Analytics</p>
            </a>
            <a href="{{ route('admin.settings.notifications') }}" class="bg-white dark:bg-dark-900 rounded-xl p-4 text-center border border-gray-200 dark:border-dark-700 hover:border-indigo-500 transition-all">
                <i class="fas fa-bell text-green-600 text-2xl mb-2"></i>
                <p class="font-medium text-gray-900 dark:text-white">Notifications</p>
            </a>
        </div>
    </div>
</div>
@endsection
