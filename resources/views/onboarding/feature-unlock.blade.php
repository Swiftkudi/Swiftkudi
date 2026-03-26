@extends('layouts.app')

@section('title', 'Unlock Features - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
                Unlock Additional Features
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Upgrade your account to access more features and capabilities.
            </p>
        </div>

        <!-- Feature Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            @foreach($features as $key => $feature)
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $feature['label'] }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            @if($feature['unlocked'] && $feature['expires'])
                                <span class="text-green-600">Unlocked until {{ $feature['expires']->format('M j, Y') }}</span>
                            @else
                                <span>Not activated</span>
                            @endif
                        </p>
                    </div>
                    @if($feature['unlocked'])
                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                    @else
                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <i class="fas fa-lock text-gray-500"></i>
                    </div>
                    @endif
                </div>

                <div class="border-t border-gray-200 dark:border-dark-700 pt-4">
                    @if(!$feature['unlocked'])
                    <form method="POST" action="{{ $unlockRoute }}">
                        @csrf
                        <input type="hidden" name="feature" value="{{ $key }}">
                        <input type="hidden" name="period" value="initial">
                        <button type="submit" 
                                class="w-full px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-unlock"></i>
                            Unlock for ₦1,000 / 3 months
                        </button>
                    </form>
                    @else
                    <div class="flex flex-col gap-2">
                        <form method="POST" action="{{ $unlockRoute }}">
                            @csrf
                            <input type="hidden" name="feature" value="{{ $key }}">
                            <input type="hidden" name="period" value="monthly">
                            <button type="submit" 
                                    class="w-full px-3 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg flex items-center justify-center gap-1">
                                <i class="fas fa-redo"></i>
                                Renew ₦500/month
                            </button>
                        </form>
                        <form method="POST" action="{{ $unlockRoute }}">
                            @csrf
                            <input type="hidden" name="feature" value="{{ $key }}">
                            <input type="hidden" name="period" value="quarterly">
                            <button type="submit" 
                                    class="w-full px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg flex items-center justify-center gap-1">
                                <i class="fas fa-calendar"></i>
                                Renew ₦1,000/quarter
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Back to Dashboard -->
        <div class="text-center">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>
