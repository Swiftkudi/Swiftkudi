@extends('layouts.app')

@section('title', 'Activate Account - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Link -->
        <a href="{{ route('wallet.index') }}" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 mb-6 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Wallet
        </a>

        @if($wallet && $wallet->is_activated)
            <!-- Already Activated -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-8 text-white text-center">
                    <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-4xl"></i>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold">Account Already Activated!</h1>
                    @if($wallet->activated_at)
                        <p class="mt-2 opacity-90">Activated on {{ $wallet->activated_at->format('F d, Y') }}</p>
                    @endif
                </div>
                <div class="p-8">
                    <div class="text-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl font-semibold transition-all shadow-lg">
                            <i class="fas fa-home mr-2"></i>
                            Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Activation Form -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 sm:p-8 text-white text-center">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-rocket text-3xl sm:text-4xl"></i>
                    </div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold">Activate Your Account</h1>
                    <p class="mt-2 opacity-90 text-sm sm:text-base">Activation is optional. Continue now and activate anytime later.</p>
                </div>

                <!-- Current Balance -->
                @if($wallet)
                <div class="px-4 sm:px-8 pt-6">
                    <div class="bg-gray-50 dark:bg-dark-800 rounded-xl p-3 sm:p-4">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1 sm:gap-0">
                            <span class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Your Current Balance</span>
                            <span class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">
                                ₦{{ number_format($wallet->getTotalBalanceAttribute(), 2) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Benefits -->
                <div class="p-4 sm:p-8">
                    <h2 class="font-bold text-gray-900 dark:text-gray-100 mb-3 sm:mb-4 text-base sm:text-lg">What you get after activation:</h2>
                    <ul class="space-y-3 sm:space-y-4 mb-6 sm:mb-8">
                        <li class="flex items-start">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-green-100 dark:bg-green-500/20 flex items-center justify-center mr-2 sm:mr-3 mt-0.5 flex-shrink-0">
                                <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xs sm:text-sm"></i>
                            </div>
                            <span class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Create unlimited tasks to grow your social media</span>
                        </li>
                        <li class="flex items-start">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-green-100 dark:bg-green-500/20 flex items-center justify-center mr-2 sm:mr-3 mt-0.5 flex-shrink-0">
                                <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xs sm:text-sm"></i>
                            </div>
                            <span class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Complete tasks and earn real money</span>
                        </li>
                        <li class="flex items-start">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center mr-2 sm:mr-3 mt-0.5 flex-shrink-0">
                                <i class="fas fa-briefcase text-indigo-600 dark:text-indigo-400 text-xs sm:text-sm"></i>
                            </div>
                            <span class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Hire professionals for your projects</span>
                        </li>
                        <li class="flex items-start">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center mr-2 sm:mr-3 mt-0.5 flex-shrink-0">
                                <i class="fas fa-chart-line text-purple-600 dark:text-purple-400 text-xs sm:text-sm"></i>
                            </div>
                            <span class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Access growth listings to boost your brand</span>
                        </li>
                        <li class="flex items-start">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-pink-100 dark:bg-pink-500/20 flex items-center justify-center mr-2 sm:mr-3 mt-0.5 flex-shrink-0">
                                <i class="fas fa-shopping-bag text-pink-600 dark:text-pink-400 text-xs sm:text-sm"></i>
                            </div>
                            <span class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Buy and sell digital products</span>
                        </li>
                        <li class="flex items-start">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-green-100 dark:bg-green-500/20 flex items-center justify-center mr-2 sm:mr-3 mt-0.5 flex-shrink-0">
                                <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xs sm:text-sm"></i>
                            </div>
                            <span class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Withdraw earnings to your bank account</span>
                        </li>
                        <li class="flex items-start">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center mr-2 sm:mr-3 mt-0.5 flex-shrink-0">
                                <i class="fas fa-star text-yellow-600 dark:text-yellow-400 text-xs sm:text-sm"></i>
                            </div>
                            <span class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Level up and unlock premium features</span>
                        </li>
                    </ul>

                    <!-- Earning Opportunities Preview -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-500/10 dark:to-emerald-500/10 rounded-2xl p-4 sm:p-6 mb-8 border border-green-200 dark:border-green-500/30">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-500/20 flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-money-bill-wave text-green-600 dark:text-green-400"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm sm:text-base">Earning Opportunities Await!</h3>
                                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">You can earn from tasks like these after activation</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <div class="bg-white dark:bg-dark-800 rounded-xl p-3 sm:p-4 border border-green-100 dark:border-green-500/20">
                                <div class="text-lg sm:text-2xl font-bold text-green-600 dark:text-green-400">₦2,500</div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Micro Tasks</p>
                            </div>
                            <div class="bg-white dark:bg-dark-800 rounded-xl p-3 sm:p-4 border border-green-100 dark:border-green-500/20">
                                <div class="text-lg sm:text-2xl font-bold text-purple-600 dark:text-purple-400">₦5,000</div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">UGC Content</p>
                            </div>
                            <div class="bg-white dark:bg-dark-800 rounded-xl p-3 sm:p-4 border border-green-100 dark:border-green-500/20">
                                <div class="text-lg sm:text-2xl font-bold text-blue-600 dark:text-blue-400">₦3,000</div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Growth Tasks</p>
                            </div>
                            <div class="bg-white dark:bg-dark-800 rounded-xl p-3 sm:p-4 border border-green-100 dark:border-green-500/20">
                                <div class="text-lg sm:text-2xl font-bold text-yellow-600 dark:text-yellow-400">₦7,500</div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Premium Tasks</p>
                            </div>
                        </div>
                    </div>

                    <!-- Live Earning Feed -->
                    <div class="bg-gray-50 dark:bg-dark-800 rounded-xl p-4 mb-8">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-sm sm:text-base">Recent Earnings</h4>
                            <span class="flex items-center text-xs text-green-600 dark:text-green-400">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-1 animate-pulse"></span>
                                Live
                            </span>
                        </div>
                        <div class="space-y-2 text-xs sm:text-sm">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700 gap-2">
                                <span class="text-gray-600 dark:text-gray-400 truncate">User***4832</span>
                                <span class="font-semibold text-green-600 dark:text-green-400 flex-shrink-0">+₦1,500</span>
                                <span class="text-gray-400 text-xs flex-shrink-0 hidden sm:inline">2 min</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700 gap-2">
                                <span class="text-gray-600 dark:text-gray-400 truncate">User***7291</span>
                                <span class="font-semibold text-green-600 dark:text-green-400 flex-shrink-0">+₦2,800</span>
                                <span class="text-gray-400 text-xs flex-shrink-0 hidden sm:inline">15 min</span>
                            </div>
                            <div class="flex justify-between items-center py-2 gap-2">
                                <span class="text-gray-600 dark:text-gray-400 truncate">User***1056</span>
                                <span class="font-semibold text-green-600 dark:text-green-400 flex-shrink-0">+₦750</span>
                                <span class="text-gray-400 text-xs flex-shrink-0 hidden sm:inline">32 min</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-500/10 dark:to-purple-500/10 rounded-2xl p-4 sm:p-6 mb-8">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                            <span class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Activation Fee</span>
                            <span class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $activationFeeEnabled ? '₦' . number_format($actualFee, 0) : 'FREE' }}
                            </span>
                        </div>
                        @if($referredBy)
                        <p class="text-xs sm:text-sm text-indigo-600 dark:text-indigo-400">
                            <i class="fas fa-tag mr-1"></i>
                            Special rate because you were referred by {{ $referredBy->name }}
                        </p>
                        @endif
                        @if($activationFeeEnabled)
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                This fee will be deducted from your wallet balance
                            </p>
                        @else
                            <p class="text-xs sm:text-sm text-green-600 dark:text-green-400 mt-2">
                                <i class="fas fa-check-circle mr-1"></i>
                                Activation fee is currently disabled by admin
                            </p>
                        @endif
                    </div>

                    <!-- Deposit Link -->
                    @if($activationFeeEnabled && $wallet && $wallet->getTotalBalanceAttribute() < $actualFee)
                    <div class="bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/20 rounded-xl p-3 sm:p-4 mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                            <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400"></i>
                            <p class="text-yellow-700 dark:text-yellow-400 text-sm sm:text-base">
                                Insufficient balance. You need at least ₦{{ number_format($actualFee, 0) }} to activate.
                            </p>
                        </div>
                        <a href="{{ route('wallet.deposit') }}" class="mt-3 inline-flex items-center px-4 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-medium transition-colors text-sm sm:text-base">
                            <i class="fas fa-plus mr-2"></i>
                            Deposit Funds
                        </a>
                    </div>
                    @endif

                    <!-- Activate Form -->
                    @if(!$activationFeeEnabled || ($wallet && $wallet->getTotalBalanceAttribute() >= $actualFee))
                    <form action="{{ route('wallet.activate.process') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3 sm:py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-xl shadow-indigo-500/30 transition-all transform hover:scale-[1.02] text-sm sm:text-base">
                            <i class="fas fa-rocket mr-2"></i>
                            {{ $activationFeeEnabled ? 'Pay ₦' . number_format($actualFee, 0) . ' & Activate' : 'Activate Now (Free)' }}
                        </button>
                    </form>
                    @else
                    <button type="button" onclick="alert('Please deposit funds first')" class="w-full py-3 sm:py-4 bg-gray-300 dark:bg-dark-700 text-gray-500 dark:text-gray-400 font-bold rounded-xl cursor-not-allowed text-sm sm:text-base">
                        <i class="fas fa-lock mr-2"></i>
                        Insufficient Balance
                    </button>
                    @endif

                    <form action="{{ route('wallet.activate.skip') }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ url()->previous() }}">
                        <button type="submit" class="w-full py-3 sm:py-4 bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-600 text-gray-700 dark:text-gray-200 font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-dark-700 transition-colors text-sm sm:text-base">
                            Continue Without Activation
                        </button>
                    </form>

                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-4 text-center">
                        You can activate later from your wallet when you are ready.
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
