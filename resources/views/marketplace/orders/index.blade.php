@extends('layouts.app')

@section('title', 'Orders - Marketplace')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-white mb-6">
        <i class="fas fa-shopping-bag mr-2 text-blue-400"></i>My Orders
    </h1>

    @if($orders->isNotEmpty())
    <div class="space-y-4">
        @foreach($orders as $order)
        <div class="bg-dark-800 rounded-2xl p-6 border border-dark-700">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-white font-semibold text-lg">Order #{{ $order->id }}</h3>
                    <p class="text-gray-400 text-sm">{{ $order->created_at->diffForHumans() }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    {{ $order->status == 'completed' ? 'bg-green-500/10 text-green-400' : ($order->status == 'delivered' ? 'bg-blue-500/10 text-blue-400' : ($order->status == 'in_progress' ? 'bg-yellow-500/10 text-yellow-400' : ($order->status == 'paid' ? 'bg-purple-500/10 text-purple-400' : ($order->status == 'cancelled' ? 'bg-red-500/10 text-red-400' : 'bg-gray-500/10 text-gray-400')))) }}">
                    {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>

            @if($order->listing)
            <div class="flex gap-4">
                @if($order->listing->images && count($order->listing->images) > 0)
                <img src="{{ asset('storage/' . $order->listing->images[0]) }}" alt="{{ $order->listing->title }}"
                     class="w-20 h-20 object-cover rounded-lg"
                     onerror="this.src='https://via.placeholder.com/80x80/1e293b/475569?text=No+Image'">
                @else
                <div class="w-20 h-20 bg-dark-700 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-gray-600"></i>
                </div>
                @endif
                <div class="flex-1">
                    <p class="text-white font-medium">{{ $order->listing->title }}</p>
                    <p class="text-gray-400 text-sm">Seller: {{ $order->seller->name }}</p>
                    <p class="text-gray-500 text-xs mt-1">
                        Amount: ₦{{ number_format($order->amount, 2) }} • Shipping: ₦{{ number_format($order->shipping_cost, 2) }}
                    </p>
                    <p class="text-gray-500 text-xs">Total: ₦{{ number_format($order->total_amount, 2) }}</p>
                </div>
            </div>
            @endif

            <div class="flex gap-3 mt-4">
                <a href="{{ route('marketplace.orders.show', $order->id) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-eye mr-1"></i>View Details
                </a>
                @if($order->status === 'delivered')
                <form method="POST" action="{{ route('marketplace.orders.confirm-receipt', $order->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-check mr-1"></i>Confirm Receipt
                    </button>
                </form>
                @endif
                @if(in_array($order->status, ['pending', 'paid']))
                <form method="POST" action="{{ route('marketplace.orders.cancel', $order->id) }}"
                      onsubmit="return confirm('Are you sure you want to cancel this order?')" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-times mr-1"></i>Cancel Order
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8 flex justify-center">
        {{ $orders->appends(request()->query())->links() }}
    </div>
    @else
    <div class="text-center py-20 bg-dark-800 rounded-2xl border border-dark-700">
        <i class="fas fa-shopping-bag text-6xl text-gray-600 mb-4"></i>
        <h3 class="text-xl text-gray-400 mb-2">No orders yet</h3>
        <p class="text-gray-500 mb-6">Browse the marketplace and place your first order.</p>
        <a href="{{ route('marketplace.listings.index') }}" class="btn btn-primary">
            <i class="fas fa-shopping-cart mr-2"></i>Browse Marketplace
        </a>
    </div>
    @endif
</div>
@endsection