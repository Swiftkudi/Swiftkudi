@extends('layouts.app')

@section('title', 'My Orders - Growth Marketplace')

@section('content')
<div class="py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">My Orders</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Orders you've placed for growth services</p>
        </div>

        @if(isset($orders) && $orders->count() > 0)
            <div class="space-y-4">
                @foreach($orders as $order)
                    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                            <div>
                                <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $order->listing->title ?? 'Growth Service' }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Seller: {{ $order->seller->name ?? 'Unknown' }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Order #{{ $order->id }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">₦{{ number_format($order->total_amount ?? 0) }}</p>
                                <span class="px-2 py-1 text-xs rounded 
                                    @if(($order->status ?? '') === 'completed') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400
                                    @elseif(($order->status ?? '') === 'cancelled') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400
                                    @else bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400 @endif">
                                    {{ ucfirst($order->status ?? 'pending') }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-4">
                            <a href="{{ route('growth.orders.show', $order->id) }}" 
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                View Details →
                            </a>
                            <a href="{{ route('chat.open', ['type' => 'growth_service', 'referenceId' => $order->listing_id, 'participantId' => $order->seller_id]) }}"
                               class="px-3 py-1.5 text-xs font-medium rounded-lg border border-purple-300 dark:border-purple-700 text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors">
                                Chat
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 bg-white dark:bg-dark-900 rounded-2xl shadow-lg">
                <div class="w-16 h-16 bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shopping-cart text-2xl text-gray-400"></i>
                </div>
                <p class="text-gray-500 dark:text-gray-400">No orders yet</p>
                <a href="{{ route('growth.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 mt-2 inline-block">
                    Browse growth services
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
