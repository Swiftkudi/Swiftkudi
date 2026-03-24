@extends('layouts.app')

@section('title', 'Withdraw Funds - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Link -->
        <a href="{{ route('wallet.index') }}" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 mb-6 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Wallet
        </a>

        @if(!$wallet || !$wallet->is_activated)
            <!-- Not Activated -->
            <div class="bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/20 rounded-2xl p-8 text-center">
                <div class="w-20 h-20 rounded-full bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-yellow-800 dark:text-yellow-400 mb-2">Account Not Activated</h2>
                <p class="text-yellow-600 dark:text-yellow-400 mb-6">You need to activate your account before you can withdraw funds.</p>
                <a href="{{ route('wallet.activate') }}" class="inline-flex items-center px-6 py-3 bg-yellow-600 hover:bg-yellow-700 text-white rounded-xl font-semibold transition-all">
                    <i class="fas fa-rocket mr-2"></i>
                    Activate Now
                </a>
            </div>
        @elseif(!$canWithdraw)
            <!-- Cannot Withdraw -->
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-2xl p-8 text-center">
                <div class="w-20 h-20 rounded-full bg-red-100 dark:bg-red-500/20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-red-800 dark:text-red-400 mb-2">Cannot Withdraw</h2>
                <p class="text-red-600 dark:text-red-400 mb-6">Your wallet balance is less than the minimum withdrawal amount.</p>
                <div class="bg-white dark:bg-dark-800 rounded-2xl p-6 max-w-sm mx-auto">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-600 dark:text-gray-400">Withdrawable Balance</span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">₦{{ number_format($wallet->withdrawable_balance, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm mb-2">
                        <span class="text-gray-500 dark:text-gray-400">Promo Credits</span>
                        <span class="text-gray-700 dark:text-gray-300">₦{{ number_format($wallet->promo_credit_balance, 2) }}</span>
                    </div>
                    <hr class="border-gray-200 dark:border-gray-700 my-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Total Balance</span>
                        <span class="text-gray-700 dark:text-gray-300">₦{{ number_format($wallet->getTotalBalanceAttribute(), 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm mt-4">
                        <span class="text-gray-500 dark:text-gray-400">Minimum Withdrawal</span>
                        <span class="text-gray-700 dark:text-gray-300">₦1,000</span>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold">Withdraw Funds</h1>
                            <p class="mt-1 opacity-90">Cash out to your bank account</p>
                        </div>
                        <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-building text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Withdrawal Form -->
                <div class="p-8">
                    @if(session('error'))
                        <div class="mb-6 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl text-red-700 dark:text-red-400">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-green-700 dark:text-green-400">
                            <i class="fas fa-check-circle mr-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('wallet.process-withdrawal') }}" method="POST">
                        @csrf

                        <!-- Current Balance -->
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-500/10 dark:to-purple-500/10 rounded-2xl p-5 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Available Balance</span>
                                <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                    ₦{{ number_format($wallet->getTotalBalanceAttribute(), 2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Bank Info -->
                        @if($wallet->bank_name)
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-dark-800 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center">
                                        <i class="fas fa-building text-indigo-600 dark:text-indigo-400"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $wallet->bank_name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $wallet->account_number }} - {{ $wallet->account_name }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('wallet.activate') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                    <i class="fas fa-pen"></i>
                                </a>
                            </div>
                        </div>
                        @else
                        <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/20 rounded-xl">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400"></i>
                                <p class="text-yellow-700 dark:text-yellow-400">Please update your bank details in the activation page.</p>
                            </div>
                            <a href="{{ route('wallet.activate') }}" class="mt-2 inline-flex items-center text-sm text-yellow-700 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300">
                                Update Bank Details <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        @endif

                        <!-- Amount Input -->
                        <div class="mb-6">
                            <label for="amount" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                                Withdrawal Amount (₦)
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">₦</span>
                                </div>
                                <input type="number" name="amount" id="amount" min="1000" max="{{ $wallet->getTotalBalanceAttribute() }}" step="1"
                                    class="block w-full pl-8 pr-12 py-4 text-lg bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Enter amount" required>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">NGN</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Minimum withdrawal: ₦1,000</p>
                        </div>

                        <!-- Withdrawal Method -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                                Withdrawal Method
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="method" value="bank" class="peer sr-only" checked>
                                    <div class="p-4 bg-gray-50 dark:bg-dark-800 border-2 border-gray-200 dark:border-dark-600 rounded-xl peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-500/10 transition-all">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center">
                                                <i class="fas fa-building text-indigo-600 dark:text-indigo-400"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-gray-100">Bank Transfer</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">1-2 business days</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="method" value="usdt" class="peer sr-only">
                                    <div class="p-4 bg-gray-50 dark:bg-dark-800 border-2 border-gray-200 dark:border-dark-600 rounded-xl peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-500/10 transition-all">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                                                <i class="fas fa-coins text-green-600 dark:text-green-400"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-gray-100">USDT</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Instant</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Quick Amounts -->
                        <div class="grid grid-cols-4 gap-3 mb-6">
                            @foreach([1000, 2000, 5000, 10000] as $amount)
                            <button type="button" onclick="document.getElementById('amount').value = {{ $amount }}"
                                class="py-2 px-4 bg-gray-100 dark:bg-dark-800 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 text-gray-700 dark:text-gray-300 hover:text-indigo-700 dark:hover:text-indigo-400 rounded-xl font-medium transition-colors text-sm">
                                ₦{{ number_format($amount) }}
                            </button>
                            @endforeach
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-xl shadow-indigo-500/30 transition-all transform hover:scale-[1.02]">
                            <i class="fas fa-building mr-2"></i>
                            Withdraw ₦<span id="withdraw-amount">0</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('amount').addEventListener('input', function() {
        document.getElementById('withdraw-amount').textContent = Number(this.value).toLocaleString();
    });
</script>
@endpush
@endsection
