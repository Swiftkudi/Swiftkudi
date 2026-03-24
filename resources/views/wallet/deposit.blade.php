@extends('layouts.app')

@section('title', 'Deposit Funds - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Link -->
        <a href="{{ route('wallet.index') }}" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 mb-6 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Wallet
        </a>

        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-500 to-teal-600 p-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">Deposit Funds</h1>
                        <p class="mt-1 opacity-90">Add money to your wallet</p>
                    </div>
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-wallet text-3xl"></i>
                    </div>
                </div>
            </div>

            <!-- Context Alert -->
            @if(session('insufficient_balance_required'))
                <div class="bg-blue-50 dark:bg-blue-500/10 border-l-4 border-blue-500 p-6 text-blue-800 dark:text-blue-300">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle mt-1 mr-3 flex-shrink-0"></i>
                        <div>
                            <p class="font-semibold">Task Creation on Hold</p>
                            <p class="text-sm mt-1">You need to deposit at least <strong>₦{{ number_format(session('insufficient_balance_required'), 0) }}</strong> to create your task. After depositing, your task will be created automatically!</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Deposit Form -->
            <div class="p-8">
                <form action="{{ route('wallet.process-deposit') }}" method="POST">
                    @csrf

                    <!-- Current Balance -->
                    <div class="bg-gray-50 dark:bg-dark-800 rounded-2xl p-5 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Current Balance:</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                ₦{{ number_format($wallet ? $wallet->getTotalBalanceAttribute() : 0, 2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Amount Input -->
                    <div class="mb-6">
                        <label for="amount" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                            Amount (₦)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                                <span class="text-gray-500 dark:text-gray-400 text-lg font-medium">₦</span>
                            </div>
                            <input type="number" name="amount" id="amount" min="100" step="1"
                                class="block w-full pl-10 pr-4 py-4 text-lg bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Enter amount" 
                                value="{{ session('insufficient_balance_required') ? session('insufficient_balance_required') : old('amount', 0) }}"
                                required>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Minimum deposit: ₦100</p>
                    </div>

                    <!-- Quick Amounts -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 mb-6">
                        @foreach([500, 1000, 2000, 5000] as $amount)
                        <button type="button" onclick="(function(){var el=document.getElementById('amount'); el.value={{ $amount }}; el.dispatchEvent(new Event('input')); })()"
                            class="py-3 px-3 bg-gray-100 dark:bg-dark-800 hover:bg-green-100 dark:hover:bg-green-500/20 text-gray-700 dark:text-gray-300 hover:text-green-700 dark:hover:text-green-400 rounded-xl font-medium transition-colors text-sm whitespace-nowrap">
                            ₦{{ number_format($amount) }}
                        </button>
                        @endforeach
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700 text-white font-bold rounded-xl shadow-xl shadow-green-500/30 transition-all transform hover:scale-[1.02]">
                        <i class="fas fa-arrow-down mr-2"></i>
                        Deposit ₦<span id="deposit-amount">0</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const amtEl = document.getElementById('amount');
        const display = document.getElementById('deposit-amount');
        function updateDisplay() {
            const v = Number(amtEl.value || 0);
            display.textContent = v.toLocaleString();
        }
        amtEl.addEventListener('input', updateDisplay);
        // initialize on load
        updateDisplay();
    });
</script>
@endpush
@endsection
