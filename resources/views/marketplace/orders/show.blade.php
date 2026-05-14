@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('marketplace.orders.index') }}" class="text-blue-400 hover:underline mb-4 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>Back to Orders
    </a>

    <div class="bg-dark-800 rounded-2xl p-8 border border-dark-700">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Order #{{ $order->id }}</h1>
                <p class="text-gray-400 text-sm mt-1">{{ $order->created_at->format('M d, Y @ h:i A') }}</p>
            </div>
            <span class="px-4 py-2 rounded-lg text-sm font-semibold
                {{ $order->status == 'completed' ? 'bg-green-500/10 text-green-400' : ($order->status == 'delivered' ? 'bg-blue-500/10 text-blue-400' : ($order->status == 'in_progress' ? 'bg-yellow-500/10 text-yellow-400' : ($order->status == 'paid' ? 'bg-purple-500/10 text-purple-400' : ($order->status == 'cancelled' ? 'bg-red-500/10 text-red-400' : 'bg-gray-500/10 text-gray-400')))) }}">
                {{ strtoupper(str_replace('_', ' ', $order->status)) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-sm font-semibold text-gray-400 uppercase mb-3">Order Details</h3>
                <div class="space-y-3">
                    @if($order->listing)
                    <div class="flex items-center gap-3">
                        <img src="{{ $order->listing->thumbnail ? asset('storage/' . $order->listing->thumbnail) : 'https://via.placeholder.com/60x60/1e293b/475569?text=N/A' }}"
                             alt="{{ $order->listing->title }}"
                             class="w-16 h-16 rounded-lg object-cover"
                             onerror="this.src='https://via.placeholder.com/60x60/1e293b/475569?text=N/A'">
                        <div>
                            <p class="text-white font-medium">{{ $order->listing->title }}</p>
                            <p class="text-gray-500 text-sm">{{ $order->seller->name }}</p>
                        </div>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Item Amount</span>
                        <span class="text-white">₦{{ number_format($order->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Shipping</span>
                        <span class="text-white">₦{{ number_format($order->shipping_cost, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm border-t border-dark-700 pt-3">
                        <span class="text-gray-400 font-semibold">Total Paid</span>
                        <span class="text-white font-bold">₦{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Platform Fee</span>
                        <span class="text-gray-400">₦{{ number_format($order->platform_fee, 2) }}</span>
                    </div>
                    @if($order->buyer_notes)
                    <div class="bg-dark-700 rounded-lg p-3">
                        <p class="text-gray-400 text-xs mb-1 uppercase font-semibold">Buyer Notes</p>
                        <p class="text-gray-300 text-sm">{{ $order->buyer_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-400 uppercase mb-3">Payment & Escrow</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Escrow Status</span>
                        @if($order->escrow)
                        <span class="text-sm font-medium {{ $order->escrow->isReleased() ? 'text-green-400' : ($order->escrow->isFunded() ? 'text-yellow-400' : 'text-gray-400') }}">
                            {{ ucfirst(str_replace('_', ' ', $order->escrow->status)) }}
                        </span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Buyer</span>
                        <span class="text-white">{{ $order->buyer->name }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Seller</span>
                        <span class="text-white">{{ $order->seller->name }}</span>
                    </div>
                    @if($order->paid_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Paid At</span>
                        <span class="text-white">{{ $order->paid_at->format('M d, Y @ h:i A') }}</span>
                    </div>
                    @endif
                    @if($order->delivered_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Delivered At</span>
                        <span class="text-white">{{ $order->delivered_at->format('M d, Y @ h:i A') }}</span>
                    </div>
                    @endif
                    @if($order->completed_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Completed At</span>
                        <span class="text-white">{{ $order->completed_at->format('M d, Y @ h:i A') }}</span>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="pt-4 flex flex-col gap-2">
                        @if($order->status === 'delivered' && $order->buyer_id === Auth::id())
                        <form method="POST" action="{{ route('marketplace.orders.confirm-receipt', $order->id) }}" class="w-full">
                            @csrf
                            <button type="submit" class="btn btn-primary w-full">
                                <i class="fas fa-check mr-2"></i>Confirm Receipt & Release Payment
                            </button>
                        </form>
                        @endif

                        @if(in_array($order->status, ['pending', 'paid']) && $order->buyer_id === Auth::id())
                        <form method="POST" action="{{ route('marketplace.orders.cancel', $order->id) }}"
                              onsubmit="return confirm('Cancel this order? You will receive a full refund.');" class="w-full">
                            @csrf
                            <button type="submit" class="btn btn-danger w-full">
                                <i class="fas fa-times mr-2"></i>Cancel Order
                            </button>
                        </form>
                        @endif

                        @if($order->status === 'paid' && $order->seller_id === Auth::id())
                        <form method="POST" action="{{ route('marketplace.orders.ship', $order->id) }}" class="w-full">
                            @csrf
                            <button type="submit" class="btn btn-secondary w-full">
                                <i class="fas fa-truck mr-2"></i>Mark as Shipped
                            </button>
                        </form>
                        @endif

                        @if($order->status === 'in_progress' && $order->buyer_id === Auth::id())
                        <form method="POST" action="{{ route('marketplace.orders.deliver', $order->id) }}" class="w-full">
                            @csrf
                            <button type="submit" class="btn btn-success w-full">
                                <i class="fas fa-check-circle mr-2"></i>Confirm Delivery
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews -->
        @if($order->status === 'delivered' || $order->reviews()->exists())
        <div class="mt-8 pt-6 border-t border-dark-700">
            <h3 class="text-lg font-semibold text-white mb-4">
                <i class="fas fa-star mr-2 text-yellow-400"></i>Reviews
            </h3>
            @if($order->reviews->isNotEmpty())
            <div class="space-y-4">
                @foreach($order->reviews as $review)
                <div class="bg-dark-700 rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold">
                            {{ strtoupper(substr($review->reviewer->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-white font-medium text-sm">{{ $review->reviewer->name }}</p>
                            <div class="flex items-center text-xs text-yellow-400">
                                @for($i = 0; $i < 5; $i++)
                                    <i class="fas fa-star {{ $i < $review->rating ? '' : 'far' }}"></i>
                                @endfor
                                <span class="text-gray-400 ml-2">({{ $review->rating }}/5)</span>
                            </div>
                        </div>
                    </div>
                    @if($review->comment)
                    <p class="text-gray-300 text-sm">{{ $review->comment }}</p>
                    @endif
                    @if($review->images)
                    <div class="flex gap-2 mt-3">
                        @foreach($review->images as $image)
                        <img src="{{ asset('storage/' . $image) }}" alt="Review image"
                             class="w-20 h-20 object-cover rounded-lg"
                             onerror="this.src='https://via.placeholder.com/80x80/1e293b/475569?text=No+Image'">
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-400 text-sm">No reviews yet (reviews are visible after approval).</p>
            @endif

            @if($order->buyer_id === Auth::id() && !$order->reviews()->where('reviewer_id', Auth::id())->exists())
            <a href="{{ route('marketplace.reviews.create', $order->id) }}" class="btn btn-primary mt-4">
                <i class="fas fa-pen mr-2"></i>Write a Review
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cancelButtons = document.querySelectorAll('.cancel-order-btn');
    cancelButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            return confirm('Are you sure you want to cancel this order? You will receive a full refund.');
        });
    });
});
</script>
@endpush