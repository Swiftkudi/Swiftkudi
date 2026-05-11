@extends('layouts.app')

@section('title', 'Earner Activation')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold">Earner Activation</h1>
            <p class="text-gray-600">Complete activation to start earning on SwiftKudi.</p>
        </div>

        @if(session('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-700">{{ session('error') }}</div>
        @endif
        @if(session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-700">{{ session('success') }}</div>
        @endif

        <?php
        $user = Auth::user();
        $wallet = $user->wallet;
        $isActivated = $wallet && $wallet->is_activated;
        ?>

        @if($isActivated)
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6 mb-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-green-600 mb-2">Account Activated!</h2>
                <p class="text-gray-600 mb-4">Your wallet is now activated and ready to earn. You can start taking tasks and earning rewards.</p>
                <a href="{{ route('dashboard') }}" class="inline-block px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg">Continue to Dashboard</a>
            </div>
        </div>
        @else
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6 mb-6">
            <h2 class="text-xl font-semibold mb-2">Wallet Activation Required</h2>
            <p class="text-gray-500 mb-4">You need to activate your wallet to start earning. This enables withdrawals and full access to earning features.</p>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="border rounded-lg p-3">
                    <p class="text-sm text-gray-500">Wallet Balance</p>
                    <p class="text-2xl font-bold">₦{{ number_format($wallet ? $wallet->withdrawable_balance : 0, 2) }}</p>
                </div>
                <div class="border rounded-lg p-3">
                    <p class="text-sm text-gray-500">Activation Status</p>
                    <p class="text-2xl font-bold text-red-600">Not Activated</p>
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">How Activation Works</h3>
                <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                    <li>• Pay a one-time activation fee to unlock your wallet</li>
                    <li>• Once activated, you can withdraw earnings and access all earning features</li>
                    <li>• The activation fee may be discounted based on referral status</li>
                </ul>
            </div>

            <a href="{{ route('wallet.activate') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Proceed to Wallet Activation
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
