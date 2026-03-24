@extends('layouts.admin')

@section('title', 'Referral Details')

@section('content')
<div class="py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Referral Details</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and manage referral information</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <a href="{{ route('admin.referrals') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-all transform hover:scale-105">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Referrals
                </a>
                @if($referral->reward_earned == 0)
                    <form action="{{ route('admin.referrals.approve', $referral) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-all transform hover:scale-105">
                            <i class="fas fa-gift mr-2"></i>
                            Approve Bonus
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-200 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-200 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Referrer Information -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                        <div class="bg-blue-100 dark:bg-blue-900 rounded-lg p-2 mr-3">
                            <i class="fas fa-user text-blue-600 dark:text-blue-300"></i>
                        </div>
                        Referrer Information
                    </h3>
                </div>
                <div class="p-6">
                    @if($referral->user)
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xl font-bold">
                                    {{ substr($referral->user->name, 0, 2) }}
                                </div>
                                <div class="ml-4">
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $referral->user->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $referral->user->email }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">User ID</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">#{{ $referral->user->id }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Member Since</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $referral->user->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Referral Code</p>
                                <p class="font-mono text-lg text-indigo-600 dark:text-indigo-400">{{ $referral->user->referral_code }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">User not found (deleted account)</p>
                    @endif
                </div>
            </div>

            <!-- Referred User Information -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                        <div class="bg-green-100 dark:bg-green-900 rounded-lg p-2 mr-3">
                            <i class="fas fa-user-plus text-green-600 dark:text-green-300"></i>
                        </div>
                        Referred User Information
                    </h3>
                </div>
                <div class="p-6">
                    @if($referral->referredUser)
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center text-white text-xl font-bold">
                                    {{ substr($referral->referredUser->name, 0, 2) }}
                                </div>
                                <div class="ml-4">
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $referral->referredUser->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $referral->referredUser->email }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">User ID</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">#{{ $referral->referredUser->id }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Member Since</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $referral->referredUser->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                                @if($referral->referredUser->hasVerifiedEmail())
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <i class="fas fa-check-circle mr-1"></i> Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Unverified
                                    </span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-gray-500 to-gray-600 flex items-center justify-center text-white text-xl font-bold">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">Email Registration</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $referral->referred_email }}</p>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    <i class="fas fa-clock mr-1"></i> Pending Registration
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Referral Status & Rewards -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg lg:col-span-2">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                        <div class="bg-purple-100 dark:bg-purple-900 rounded-lg p-2 mr-3">
                            <i class="fas fa-award text-purple-600 dark:text-purple-300"></i>
                        </div>
                        Referral Status & Rewards
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                            @if($referral->status === 'completed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 mt-2">
                                    <i class="fas fa-check-circle mr-1"></i> Completed
                                </span>
                            @elseif($referral->status === 'pending')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 mt-2">
                                    <i class="fas fa-clock mr-1"></i> Pending
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-300 mt-2">
                                    {{ ucfirst($referral->status) }}
                                </span>
                            @endif
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Bonus Earned</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-2">
                                ₦{{ number_format($referral->reward_earned, 2) }}
                            </p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Referred Tasks</p>
                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-2">
                                {{ $referral->tasks_count ?? 0 }}
                            </p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Created At</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 mt-2">
                                {{ $referral->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    </div>

                    <!-- Bonus Information -->
                    <div class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-900 rounded-lg">
                        <h4 class="font-semibold text-indigo-900 dark:text-indigo-100 mb-2">
                            <i class="fas fa-info-circle mr-2"></i>How Referral Bonuses Work
                        </h4>
                        <p class="text-sm text-indigo-700 dark:text-indigo-300">
                            When a referred user completes their first task and earns money, the referrer receives a bonus. 
                            The bonus amount is configured in system settings. Click "Approve Bonus" to manually credit the referrer's wallet.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
