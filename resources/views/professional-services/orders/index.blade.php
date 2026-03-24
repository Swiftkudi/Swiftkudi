@extends('layouts.app')

@section('title', 'My Orders - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">My Orders</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Services you have purchased</p>
        </div>

        @if(isset($activeOrders) && $activeOrders->count() > 0)
            <!-- Active Orders -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <i class="fas fa-clock text-yellow-500"></i> Active Orders
                </h2>
                <div class="space-y-4">
                    @foreach($activeOrders as $order)
                        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $order->service->title ?? 'Service' }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">From: {{ $order->seller->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Order #{{ $order->id }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">₦{{ number_format($order->total_amount) }}</p>
                                    <span class="px-2 py-1 text-xs rounded bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-4">
                                <a href="{{ route('professional-services.orders.show', $order->id) }}" 
                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                    View Details →
                                </a>
                                <a href="{{ route('chat.open', ['type' => 'professional_service', 'referenceId' => $order->service_id, 'participantId' => $order->seller_id]) }}"
                                   class="px-3 py-1.5 text-xs font-medium rounded-lg border border-purple-300 dark:border-purple-700 text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors">
                                    Chat
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(isset($completedOrders) && $completedOrders->count() > 0)
            <!-- Completed Orders -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-500"></i> Completed Orders
                </h2>
                <div class="space-y-4">
                    @foreach($completedOrders as $order)
                        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $order->service->title ?? 'Service' }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">From: {{ $order->seller->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Order #{{ $order->id }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">₦{{ number_format($order->total_amount) }}</p>
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-4">
                                <a href="{{ route('professional-services.orders.show', $order->id) }}" 
                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                    View Details →
                                </a>
                                <a href="{{ route('chat.open', ['type' => 'professional_service', 'referenceId' => $order->service_id, 'participantId' => $order->seller_id]) }}"
                                   class="px-3 py-1.5 text-xs font-medium rounded-lg border border-purple-300 dark:border-purple-700 text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors">
                                    Chat
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if((!isset($activeOrders) || $activeOrders->count() == 0) && (!isset($completedOrders) || $completedOrders->count() == 0))
            <div class="text-center py-12 bg-white dark:bg-dark-900 rounded-2xl shadow-lg">
                <div class="w-16 h-16 bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shopping-cart text-2xl text-gray-400"></i>
                </div>
                <p class="text-gray-500 dark:text-gray-400">No orders yet</p>
                <a href="{{ route('professional-services.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 mt-2 inline-block">
                    Browse services
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
