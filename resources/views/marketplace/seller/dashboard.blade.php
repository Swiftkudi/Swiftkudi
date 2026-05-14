@extends('layouts.app')

@section('title', 'Seller Dashboard - Marketplace')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-white mb-8">
        <i class="fas fa-tachometer-alt mr-2 text-blue-400"></i>Seller Dashboard
    </h1>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-dark-800 rounded-2xl p-6 border border-dark-700">
            <div class="text-gray-400 text-sm mb-1"><i class="fas fa-store mr-1"></i> Total Listings</div>
            <div class="text-3xl font-bold text-white">{{ $stats['total_listings'] ?? 0 }}</div>
        </div>
        <div class="bg-dark-800 rounded-2xl p-6 border border-dark-700">
            <div class="text-gray-400 text-sm mb-1"><i class="fas fa-check-circle mr-1 text-green-400"></i> Active Listings</div>
            <div class="text-3xl font-bold text-green-400">{{ $stats['active_listings'] ?? 0 }}</div>
        </div>
        <div class="bg-dark-800 rounded-2xl p-6 border border-dark-700">
            <div class="text-gray-400 text-sm mb-1"><i class="fas fa-shopping-cart mr-1"></i> Total Sales</div>
            <div class="text-3xl font-bold text-purple-400">{{ $stats['total_sales'] ?? 0 }}</div>
        </div>
        <div class="bg-dark-800 rounded-2xl p-6 border border-dark-700">
            <div class="text-gray-400 text-sm mb-1"><i class="fas fa-naira-sign mr-1"></i> Total Revenue</div>
            <div class="text-3xl font-bold text-yellow-400">₦{{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Today's Orders -->
        <div class="lg:col-span-2">
            <div class="bg-dark-800 rounded-2xl p-6 border border-dark-700">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-white font-semibold text-lg">
                        <i class="fas fa-clock mr-2 text-blue-400"></i>Today's Orders
                    </h3>
                    <a href="{{ route('marketplace.seller.orders') }}" class="text-blue-400 text-sm hover:underline">View All</a>
                </div>

                @if($recentOrders->isNotEmpty())
                <div class="space-y-3">
                    @foreach($recentOrders as $order)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-dark-700">
                        <div class="flex items-center gap-3">
                            @if($order->listing && $order->listing->thumbnail)
                            <img src="{{ asset('storage/' . $order->listing->thumbnail) }}" alt=""
                                 class="w-10 h-10 rounded object-cover">
                            @else
                            <div class="w-10 h-10 rounded bg-dark-600 flex items-center justify-center">
                                <i class="fas fa-box text-gray-500 text-sm"></i>
                            </div>
                            @endif
                            <div>
                                <p class="text-white text-sm font-medium">{{ $order->listing->title ?? 'Unknown' }}</p>
                                <p class="text-gray-500 text-xs">{{ $order->buyer->name }} • {{ $order->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-white font-medium text-sm">₦{{ number_format($order->amount, 2) }}</span>
                            <span class="text-xs {{ $order->status == 'paid' ? 'text-purple-400' : ($order->status == 'in_progress' ? 'text-yellow-400' : 'text-green-400') }} block">
                                {{ strtoupper($order->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-center py-4">No orders today</p>
                @endif
            </div>
        </div>

        <!-- Rating Card -->
        <div>
            <div class="bg-dark-800 rounded-2xl p-6 border border-dark-700 text-center">
                <div class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl font-bold
                    {{ ($stats['rating'] ?? 0) >= 4 ? 'bg-green-500/10 text-green-400' : ($stats['rating'] ?? 0) >= 3 ? 'bg-yellow-500/10 text-yellow-400' : 'bg-gray-700 text-gray-400' }}">
                    {{ number_format($stats['rating'] ?? 0, 1) }}
                </div>
                <p class="text-white font-semibold">Seller Rating</p>
                <p class="text-gray-400 text-sm">{{ $stats['rating_count'] ?? 0 }} reviews</p>
                <div class="flex items-center justify-center mt-3 text-sm">
                    <div class="flex text-yellow-400">
                        @for($i = 0; $i < 5; $i++)
                            @if($i < round($stats['rating'] ?? 0))
                            <i class="fas fa-star"></i>
                            @else
                            <i class="far fa-star"></i>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Subscription Status -->
            @if(!$isPremium)
            <div class="bg-dark-800 rounded-2xl p-6 border border-dark-700 mt-6 text-center">
                <i class="fas fa-crown text-yellow-400 text-4xl mb-3"></i>
                <h3 class="text-white font-semibold mb-2">Go Premium</h3>
                <p class="text-gray-400 text-sm mb-4">Lower commission rates, priority support, and more visibility.</p>
                <a href="{{ route('marketplace.subscription.index') }}" class="btn btn-primary btn-sm w-full">
                    Upgrade Now
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection