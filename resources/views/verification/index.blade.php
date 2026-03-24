@extends('layouts.app')

@section('title', 'Verification Center - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-green-600 to-teal-600 bg-clip-text text-transparent">Verification Center</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Verify your identity to build trust and unlock features</p>
        </div>

        <!-- Verification Cards -->
        <div class="space-y-6">
            <!-- Email Verification -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <i class="fas fa-envelope text-blue-600 dark:text-blue-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Email Verification</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                @if(Auth::user()->hasVerifiedEmail())
                                    <span class="text-green-600 dark:text-green-400"><i class="fas fa-check-circle mr-1"></i>Your email is verified</span>
                                @else
                                    Verify your email address to secure your account
                                @endif
                            </p>
                        </div>
                    </div>
                    @if(!Auth::user()->hasVerifiedEmail())
                        <form action="{{ route('verification.email') }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Verify Email
                            </button>
                        </form>
                    @else
                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-sm font-medium rounded-full">
                            <i class="fas fa-check mr-1"></i>Verified
                        </span>
                    @endif
                </div>
            </div>

            <!-- Phone Verification -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                            <i class="fas fa-phone text-purple-600 dark:text-purple-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Phone Verification</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                @php
                                    $phoneVerification = $verifications->where('type', 'phone')->first();
                                @endphp
                                @if($phoneVerification && $phoneVerification->status === 'approved')
                                    <span class="text-green-600 dark:text-green-400"><i class="fas fa-check-circle mr-1"></i>Your phone is verified</span>
                                @else
                                    Add and verify your phone number
                                @endif
                            </p>
                        </div>
                    </div>
                    <button type="button" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors" onclick="document.getElementById('phone-modal').classList.remove('hidden')">
                        {{ $phoneVerification && $phoneVerification->status === 'approved' ? 'Update' : 'Verify' }}
                    </button>
                </div>
            </div>

            <!-- Identity Verification -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                            <i class="fas fa-id-card text-indigo-600 dark:text-indigo-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Identity Verification</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                @php
                                    $identityVerification = $verifications->where('type', 'identity')->first();
                                @endphp
                                @if($identityVerification && $identityVerification->status === 'approved')
                                    <span class="text-green-600 dark:text-green-400"><i class="fas fa-check-circle mr-1"></i>Your identity is verified</span>
                                @elseif($identityVerification && $identityVerification->status === 'pending')
                                    <span class="text-yellow-600 dark:text-yellow-400"><i class="fas fa-clock mr-1"></i>Verification pending review</span>
                                @else
                                    Upload a government-issued ID to verify your identity
                                @endif
                            </p>
                        </div>
                    </div>
                    <button type="button" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors" onclick="document.getElementById('identity-modal').classList.remove('hidden')">
                        {{ $identityVerification && $identityVerification->status === 'approved' ? 'Update' : ($identityVerification && $identityVerification->status === 'pending' ? 'Pending' : 'Verify') }}
                    </button>
                </div>
            </div>

            <!-- Address Verification -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
                            <i class="fas fa-home text-teal-600 dark:text-teal-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Address Verification</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                @php
                                    $addressVerification = $verifications->where('type', 'address')->first();
                                @endphp
                                @if($addressVerification && $addressVerification->status === 'approved')
                                    <span class="text-green-600 dark:text-green-400"><i class="fas fa-check-circle mr-1"></i>Your address is verified</span>
                                @elseif($addressVerification && $addressVerification->status === 'pending')
                                    <span class="text-yellow-600 dark:text-yellow-400"><i class="fas fa-clock mr-1"></i>Verification pending review</span>
                                @else
                                    Verify your residential address with a utility bill
                                @endif
                            </p>
                        </div>
                    </div>
                    <button type="button" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors" onclick="document.getElementById('address-modal').classList.remove('hidden')">
                        {{ $addressVerification && $addressVerification->status === 'approved' ? 'Update' : ($addressVerification && $addressVerification->status === 'pending' ? 'Pending' : 'Verify') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Benefits Section -->
        <div class="mt-8 bg-gradient-to-r from-green-50 to-teal-50 dark:from-green-900/10 dark:to-teal-900/10 rounded-2xl p-6">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Benefits of Verification</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Build trust with buyers and sellers</span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Increase your chances of getting hired</span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Unlock withdrawal limits</span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Access to more marketplace features</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
