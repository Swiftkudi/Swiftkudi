@extends('layouts.app')

@section('title', 'Referrals - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Referral Program</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Invite friends and earn ₦{{ number_format($bonusAmount ?? 0, 2) }} for each activation</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center">
                        <i class="fas fa-users text-indigo-600 dark:text-indigo-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Referrals</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_referrals'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-user-check text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Registered</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['registered'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                        <i class="fas fa-check-circle text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Activated</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['activated'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center">
                        <i class="fas fa-naira-sign text-yellow-600 dark:text-yellow-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Earned</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">₦{{ number_format($stats['total_earned'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Referral Link Card -->
        <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl shadow-xl shadow-indigo-500/30 p-8 mb-8 text-white">
            <h2 class="text-xl font-bold mb-4">Your Referral Link</h2>
            <p class="text-indigo-100 mb-6">Share this link with your friends. When they activate their account, you earn ₦{{ number_format($bonusAmount ?? 0, 2) }}!</p>
            <div class="flex flex-col sm:flex-row gap-4">
                <input type="text" readonly value="{{ route('ref.redirect', ['code' => $referralCode]) }}" class="flex-1 px-4 py-3 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-indigo-200">
                <button onclick="copyReferralLink()" class="px-6 py-3 bg-white text-indigo-600 font-bold rounded-xl hover:bg-indigo-50 transition-all shadow-lg">
                    <i class="fas fa-copy mr-2"></i>Copy
                </button>
            </div>
        </div>

        <!-- How Referrals Work -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">How It Works</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-share-alt text-indigo-600 dark:text-indigo-400 text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 mb-2">1. Share Link</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Share your unique referral link with friends and family</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 rounded-2xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-plus text-green-600 dark:text-green-400 text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 mb-2">2. They Register</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Friends sign up using your referral link</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 rounded-2xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-gift text-purple-600 dark:text-purple-400 text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 mb-2">3. Get Paid</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Earn ₦{{ number_format($bonusAmount ?? 0, 2) }} when they activate their account</p>
                </div>
            </div>
        </div>

        <!-- Referral History -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Referral History</h2>
            @if(count($referrals) > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-sm text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-dark-700">
                            <th class="pb-4 font-medium">User</th>
                            <th class="pb-4 font-medium">Status</th>
                            <th class="pb-4 font-medium">Earned</th>
                            <th class="pb-4 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-dark-700">
                        @foreach($referrals as $referral)
                        <tr>
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr(optional($referral->referredUser)->name ?? ($referral->referred_email ?? '--'), 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ optional($referral->referredUser)->name ?? ($referral->referred_email ?? 'Unknown') }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ optional($referral->referredUser)->email ?? ($referral->referred_email ?? '') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">
                                @if($referral->status === 'activated')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400">Activated</span>
                                @elseif($referral->status === 'registered')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400">Registered</span>
                                @else
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">Pending</span>
                                @endif
                            </td>
                            <td class="py-4">
                                @if((float) $referral->reward_earned > 0)
                                <span class="font-bold text-green-600 dark:text-green-400">₦{{ number_format($referral->reward_earned, 2) }}</span>
                                @else
                                <span class="text-gray-400 dark:text-gray-500">₦0</span>
                                @endif
                            </td>
                            <td class="py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $referral->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-12">
                <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No referrals yet</h3>
                <p class="text-gray-500 dark:text-gray-400">Start sharing your referral link to earn money!</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function copyReferralLink() {
    const input = document.querySelector('input[readonly]');
    if (input) {
        input.select();
        document.execCommand('copy');
        alert('Referral link copied!');
    }
}
</script>
@endsection
