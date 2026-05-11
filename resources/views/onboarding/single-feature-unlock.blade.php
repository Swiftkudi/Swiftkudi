@extends('layouts.app')

@section('title', 'Unlock Feature - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-2xl mx-auto px-4">
        @if(session('unlock_prompt'))
            @php $prompt = session('unlock_prompt') @endphp
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-500/10 dark:to-purple-500/10 rounded-2xl p-6 border border-indigo-100 dark:border-indigo-500/20 mb-6">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ ucfirst(str_replace('_', ' ', $prompt['feature'] ?? $feature)) }}
                    </h2>
                    @if(isset($prompt['message']))
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            {{ $prompt['message'] }}
                        </p>
                    @endif
                </div>
            </div>
        @endif

        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas {{ $isUnlocked ? 'fa-check text-white' : 'fa-lock text-white' }} text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                {{ $label }}
            </h1>
            @if($description)
                <p class="text-gray-600 dark:text-gray-400">{{ $description }}</p>
            @endif
        </div>

        <!-- Feature Status -->
        @if($isUnlocked)
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 mb-6 text-center">
                <p class="text-green-800 dark:text-green-400">
                    <i class="fas fa-clock mr-2"></i>
                    Currently active until {{ $expiresAt->format('F j, Y') }}
                </p>
            </div>
        @elseif(isset($isExpired) && $isExpired)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 mb-6 text-center">
                    <p class="text-yellow-800 dark:text-yellow-400">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Your access expired on {{ $expiresAt->format('F j, Y') }}. Renew to continue using this feature.
                    </p>
                </div>
        @else
            <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-6 text-center">
                <p class="text-gray-600 dark:text-gray-400">
                    <i class="fas fa-lock mr-2"></i>
                    This feature is locked. Unlock to gain full access.{{ $feature }}
                </p>
            </div>
        @endif

        <!-- Unlock/Renew Options -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                {{ $showRenewOptions ? 'Renew Your Plan' : 'Unlock This Feature' }}
            </h3>
            
            <div class="space-y-3">
                @foreach($periods as $periodKey => $periodData)
                    @php
                        $cost = $periodData['cost'];
                        $months = $periodData['months'];
                        
                        // Determine which periods to show based on state
                        // If feature has active access, show only renewal options (monthly, quarterly)
                        // If feature is new or expired, show only initial unlock
                        $isRenewPeriod = in_array($periodKey, ['monthly', 'quarterly']);
                        
                        if ($showRenewOptions && !$isRenewPeriod) {
                            continue; // Skip initial period when showing renew options
                        }
                        if (!$showRenewOptions && $isRenewPeriod) {
                            continue; // Skip renew periods when showing initial unlock
                        }
                        
                        $buttonText = match($periodKey) {
                            'initial' => "Unlock for ₦" . number_format($cost) . " / {$months} months",
                            'monthly' => "Renew ₦" . number_format($cost) . " / month",
                            'quarterly' => "Renew ₦" . number_format($cost) . " / quarter",
                        };
                        $buttonClass = match($periodKey) {
                            'initial' => 'w-full px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-2',
                            'monthly' => 'w-full px-3 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg flex items-center justify-center gap-1',
                            'quarterly' => 'w-full px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg flex items-center justify-center gap-1',
                        };
                    @endphp
                    
                    <form method="POST" action="{{ $formAction }}">
                        @csrf
                        <input type="hidden" name="feature" value="{{ $feature }}">
                        <input type="hidden" name="period" value="{{ $periodKey }}">
                        <button type="submit" class="{{ $buttonClass }}">
                            <i class="fas {{ $showRenewOptions ? 'fa-redo' : 'fa-unlock' }}"></i>
                            {{ $buttonText }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        @if($isUnlocked && $expiresAt)
        <div class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            <p>Current plan expires: <strong>{{ $expiresAt->format('F j, Y') }}</strong></p>
        </div>
        @endif

        <div class="text-center space-x-4">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-indigo-600 font-medium">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
            <a href="{{ route('onboarding.features') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-indigo-600 font-medium">
                <i class="fas fa-th-large"></i>
                View All Features
            </a>
        </div>
    </div>
</div>
@endsection
