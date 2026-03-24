@extends('layouts.app')

@section('title', 'My Sales - Growth Marketplace')

@section('content')
<div class="py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">My Sales</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Orders for your growth services</p>
        </div>

        @php
            $allSales = ($activeSales ?? collect())->merge($completedSales ?? collect());
        @endphp

        @if($allSales->isEmpty())
            <div class="text-center py-12 bg-white dark:bg-dark-900 rounded-2xl shadow-lg">
                <div class="w-16 h-16 bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-line text-2xl text-gray-400"></i>
                </div>
                <p class="text-gray-500 dark:text-gray-400">No sales yet</p>
                <a href="{{ route('growth.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 mt-2 inline-block">
                    Create a listing to start selling
                </a>
            </div>
        @else
            <!-- Active Sales -->
            @if(isset($activeSales) && $activeSales->count() > 0)
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Active Orders</h2>
                <div class="space-y-4 mb-8">
                    @foreach($activeSales as $order)
                        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                                <div>
                                    <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $order->listing->title ?? 'Growth Service' }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Buyer: {{ $order->buyer->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Order #{{ $order->id }}</p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">₦{{ number_format($order->total_amount ?? 0) }}</p>
                                    <span class="px-2 py-1 text-xs rounded bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">
                                        {{ ucfirst($order->status ?? 'pending') }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="flex items-center gap-4">
                                    <a href="{{ route('growth.orders.show', $order->id) }}" 
                                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                        Manage Order →
                                    </a>
                                    <a href="{{ route('chat.open', ['type' => 'growth_service', 'referenceId' => $order->listing_id, 'participantId' => $order->buyer_id]) }}"
                                        class="text-sm text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300">
                                        Go to Messages →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Completed Sales -->
            @if(isset($completedSales) && $completedSales->count() > 0)
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Completed Orders</h2>
                <div class="space-y-4">
                    @foreach($completedSales as $order)
                        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                                <div>
                                    <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $order->listing->title ?? 'Growth Service' }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Buyer: {{ $order->buyer->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Order #{{ $order->id }}</p>
                                    <a href="{{ route('chat.open', ['type' => 'growth_service', 'referenceId' => $order->listing_id, 'participantId' => $order->buyer_id]) }}"
                                        class="inline-block mt-2 text-sm text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300">
                                        Go to Messages →
                                    </a>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">₦{{ number_format($order->total_amount ?? 0) }}</p>
                                    <p class="text-sm text-green-600 dark:text-green-400">Earned: ₦{{ number_format($order->seller_payout ?? 0) }}</p>
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                        Completed
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
