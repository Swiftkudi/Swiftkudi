@extends('layouts.app')

@section('title', 'My Sales - Marketplace')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-white mb-6">
        <i class="fas fa-store mr-2 text-green-400"></i>My Sales
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
                    {{ $order->status == 'completed' ? 'bg-green-500/10 text-green-400' : ($order->status == 'in_progress' ? 'bg-yellow-500/10 text-yellow-400' : ($order->status == 'paid' ? 'bg-purple-500/10 text-purple-400' : ($order->status == 'delivered' ? 'bg-blue-500/10 text-blue-400' : 'bg-gray-500/10 text-gray-400')) }}">
                    {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>

            @if($order->listing)
            <div class="flex gap-4 items-center">
                @if($order->listing->images && count($order->listing->images) > 0)
                <img src="{{ asset('storage/' . $order->listing->images[0]) }}" alt="{{ $order->listing->title }}"
                     class="w-16 h-16 object-cover rounded-lg"
                     onerror="this.src='https://via.placeholder.com/64x64/1e293b/475569?text=N/A'">
                @else
                <div class="w-16 h-16 bg-dark-700 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-gray-600"></i>
                </div>
                @endif
                <div class="flex-1">
                    <p class="text-white font-medium">{{ $order->listing->title }}</p>
                    <p class="text-gray-500 text-sm">
                        Buyer: {{ $order->buyer->name }} •
                        Qty: 1 •
                        @if($order->shipping_cost > 0)
                        Shipping: ₦{{ number_format($order->shipping_cost, 2) }}
                        @endif
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-white font-bold">₦{{ number_format($order->amount, 2) }}</p>
                    <p class="text-gray-500 text-xs">escrowed: ₦{{ number_format($order->escrow_amount, 2) }}</p>
                </div>
            </div>
            @endif

            @if($order->seller_notes)
            <div class="mt-3 bg-dark-700 rounded-lg p-3">
                <p class="text-gray-400 text-xs mb-1 uppercase font-semibold">Your Notes</p>
                <p class="text-gray-300 text-sm">{{ $order->seller_notes }}</p>
            </div>
            @endif

            <div class="flex gap-3 mt-4">
                @if($order->status === 'paid')
                <form method="POST" action="{{ route('marketplace.orders.ship', $order->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <i class="fas fa-truck mr-1"></i>Mark as Shipped
                    </button>
                </form>
                @endif

                @if($order->status === 'in_progress')
                <form method="POST" action="{{ route('marketplace.orders.deliver', $order->id) }}"
                      onsubmit="return confirm('Confirm that you have delivered this order?');" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check-circle mr-1"></i>Mark as Delivered
                    </button>
                </form>
                @endif

                <a href="{{ route('marketplace.orders.show', $order->id) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-eye mr-1"></i>View Details
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8 flex justify-center">
        {{ $orders->appends(request()->query())->links() }}
    </div>
    @else
    <div class="text-center py-20 bg-dark-800 rounded-2xl border border-dark-700">
        <i class="fas fa-store text-6xl text-gray-600 mb-4"></i>
        <h3 class="text-xl text-gray-400 mb-2">No sales yet</h3>
        <p class="text-gray-500 mb-6">Orders from buyers will appear here.</p>
        <a href="{{ route('marketplace.listings.index') }}" class="btn btn-primary">
            <i class="fas fa-shopping-cart mr-2"></i>View Marketplace
        </a>
    </div>
    @endif
</div>
@endsection