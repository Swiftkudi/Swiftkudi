@extends('layouts.app')

@section('title', 'Escrow - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Escrow</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Secure payments and transaction management</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">In Escrow</h3>
                    <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center">
                        <i class="fas fa-shield-alt text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">₦{{ number_format($totalInEscrow ?? 0, 2) }}</div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Held in secure escrow</p>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Released</h3>
                    <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">₦{{ number_format($totalReleased ?? 0, 2) }}</div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total released to sellers</p>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Total Transactions</h3>
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                        <i class="fas fa-exchange-alt text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $escrows->total() }}</div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">All escrow transactions</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-dark-700">
                <nav class="flex -mb-px">
                    <a href="{{ route('wallet.escrow') }}" class="py-4 px-6 text-center border-b-2 font-medium text-sm 
                        {{ request()->routeIs('wallet.escrow') || request()->routeIs('escrow.index') ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        All
                    </a>
                    <a href="{{ route('escrow.active') }}" class="py-4 px-6 text-center border-b-2 font-medium text-sm 
                        {{ request()->routeIs('escrow.active') ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        <i class="fas fa-lock mr-2"></i>Active
                    </a>
                    <a href="{{ route('escrow.released') }}" class="py-4 px-6 text-center border-b-2 font-medium text-sm 
                        {{ request()->routeIs('escrow.released') ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        <i class="fas fa-check mr-2"></i>Released
                    </a>
                    <a href="{{ route('escrow.disputed') }}" class="py-4 px-6 text-center border-b-2 font-medium text-sm 
                        {{ request()->routeIs('escrow.disputed') ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Disputed
                    </a>
                </nav>
            </div>

            <!-- Escrow List -->
            @if($escrows->count() > 0)
                <div class="divide-y divide-gray-200 dark:divide-dark-700">
                    @foreach($escrows as $escrow)
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                            @if($escrow->order && isset($escrow->order->title))
                                                {{ $escrow->order->title }}
                                            @else
                                                Escrow Transaction #{{ $escrow->id }}
                                            @endif
                                        </h3>
                                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                                            @if(in_array($escrow->status, ['pending', 'funded'])) bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400
                                            @elseif($escrow->status === 'released') bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400
                                            @elseif($escrow->status === 'disputed') bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400
                                            @else bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400 @endif">
                                            {{ ucfirst($escrow->status) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span>
                                            <i class="fas fa-user mr-1"></i>
                                            @if($escrow->payer_id === Auth::id())
                                                To: {{ $escrow->payee->name ?? 'N/A' }}
                                            @else
                                                From: {{ $escrow->payer->name ?? 'N/A' }}
                                            @endif
                                        </span>
                                        <span><i class="fas fa-clock mr-1"></i>{{ $escrow->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100">₦{{ number_format($escrow->amount, 2) }}</div>
                                    @if(in_array($escrow->status, ['pending', 'funded']) && $escrow->payer_id === Auth::id())
                                        <form action="{{ route('escrow.release', $escrow) }}" method="POST" class="mt-2">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors" onclick="return confirm('Confirm satisfaction and release payment?')">
                                                <i class="fas fa-check mr-1"></i>Confirm & Release
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="p-6 border-t border-gray-200 dark:border-dark-700">
                    {{ $escrows->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shield-alt text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No escrow transactions</h3>
                    <p class="text-gray-500 dark:text-gray-400">Your escrow transactions will appear here</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
