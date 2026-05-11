@extends('layouts.app')

@section('title', 'Unlock Feature Access')

@section('content')
<div class="py-8">
    <div class="max-w-2xl mx-auto px-4">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-lock text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold">Unlock Feature Access</h1>
            <p class="text-gray-600 mt-2">Discover all the amazing features SwiftKudi has to offer</p>
        </div>

        @if(session('unlock_prompt'))
            @php $prompt = session('unlock_prompt') @endphp
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-500/10 dark:to-purple-500/10 rounded-2xl p-6 border border-indigo-100 dark:border-indigo-500/20 mb-6">
                <div class="text-center">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ ucfirst(str_replace('_', ' ', $prompt['feature'])) }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        {{ $prompt['message'] }}
                    </p>
                    <a href="{{ route('onboarding.select') }}"
                       class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-all">
                        <i class="fas fa-unlock mr-2"></i>
                        {{ $prompt['action'] }}
                    </a>
                </div>
            </div>
        @endif

        <!-- Feature Showcase -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-gray-100 dark:border-dark-700 p-6">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-500/20 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-tasks text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">Task Creation</h3>
                <p class="text-gray-500 text-sm mb-4">Post tasks and hire talented workers to complete your projects.</p>
                <span class="text-xs bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 px-2 py-1 rounded-full">Available for Task Creators</span>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-gray-100 dark:border-dark-700 p-6">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-500/20 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-briefcase text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">Professional Services</h3>
                <p class="text-gray-500 text-sm mb-4">Hire freelancers or offer your professional services.</p>
                <span class="text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400 px-2 py-1 rounded-full">Available for All Roles</span>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-gray-100 dark:border-dark-700 p-6">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-500/20 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-chart-line text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">Growth Marketplace</h3>
                <p class="text-gray-500 text-sm mb-4">Buy and sell backlinks, leads, and growth services.</p>
                <span class="text-xs bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400 px-2 py-1 rounded-full">Available for All Roles</span>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-gray-100 dark:border-dark-700 p-6">
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-500/20 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-download text-orange-600 dark:text-orange-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">Digital Products</h3>
                <p class="text-gray-500 text-sm mb-4">Sell templates, plugins, and digital assets.</p>
                <span class="text-xs bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-400 px-2 py-1 rounded-full">Available for All Roles</span>
            </div>
        </div>

        <!-- Account Type Switcher -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl border border-gray-100 dark:border-dark-700 p-6 mb-6">
            <h3 class="text-xl font-bold mb-4">Choose Your Path</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Switch account types to unlock different features and capabilities.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('onboarding.select') }}" class="block p-4 border border-gray-200 dark:border-dark-600 rounded-xl hover:border-indigo-300 dark:hover:border-indigo-500 transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exchange-alt text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold">Change Account Type</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Switch to access different features</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('onboarding.features') }}" class="block p-4 border border-gray-200 dark:border-dark-600 rounded-xl hover:border-indigo-300 dark:hover:border-indigo-500 transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-unlock text-green-600 dark:text-green-400"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold">Unlock Features</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Get access to additional capabilities</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-indigo-600">Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection