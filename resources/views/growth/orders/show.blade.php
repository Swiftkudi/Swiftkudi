@extends('layouts.app')

@section('title', 'Order Details - Growth Marketplace')

@section('content')
<div class="py-4 lg:py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('growth.orders.index') }}" class="inline-flex items-center text-indigo-400 hover:text-indigo-300 text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Orders
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Order Info -->
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-white">Order #{{ $order->id }}</h2>
                        <span class="px-3 py-1 text-xs rounded-full font-medium
                            @switch($order->status)
                                @case('completed') bg-green-500/20 text-green-400 @break
                                @case('pending') bg-yellow-500/20 text-yellow-400 @break
                                @case('in_progress') bg-blue-500/20 text-blue-400 @break
                                @case('cancelled') bg-red-500/20 text-red-400 @break
                                @default bg-gray-500/20 text-gray-400 @break
                            @endswitch">
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </div>

                    <!-- Listing Info -->
                    <div class="border-t border-dark-700 pt-4">
                        <h3 class="font-semibold text-white mb-2">{{ $order->listing->title ?? 'Listing Removed' }}</h3>
                        <p class="text-gray-400 text-sm mb-4">{{ $order->listing->description ?? 'No description available' }}</p>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Type:</span>
                                <span class="text-white ml-2">{{ ucfirst($order->listing->type ?? 'N/A') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Amount:</span>
                                <span class="text-green-400 ml-2">₦{{ number_format($order->amount, 2) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Created:</span>
                                <span class="text-white ml-2">{{ $order->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Delivery:</span>
                                <span class="text-white ml-2">{{ $order->listing->delivery_days ?? 'N/A' }} days</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Requirements -->
                @if($order->proof_notes)
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6 mb-6">
                    <h3 class="font-semibold text-white mb-3">Proof / Notes</h3>
                    <p class="text-gray-400 text-sm whitespace-pre-line">{{ $order->proof_notes }}</p>
                </div>
                @endif

                <!-- Proof Data -->
                @if($order->proof_data)
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6 mb-6">
                    <h3 class="font-semibold text-white mb-3">Delivery Proof</h3>
                    <pre class="text-gray-400 text-xs whitespace-pre-wrap">{{ json_encode($order->proof_data, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Payment Summary -->
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6 mb-6">
                    <h3 class="font-semibold text-white mb-4">Payment Summary</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Subtotal</span>
                            <span class="text-white">₦{{ number_format($order->amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Platform Fee</span>
                            <span class="text-white">₦{{ number_format($order->platform_commission ?? 0, 2) }}</span>
                        </div>
                        <div class="border-t border-dark-700 pt-3 flex justify-between font-semibold">
                            <span class="text-white">Total</span>
                            <span class="text-green-400">₦{{ number_format($order->amount, 2) }}</span>
                        </div>
                    </div>

                    @if(auth()->id() === $order->seller_id)
                    <div class="mt-4 pt-4 border-t border-dark-700">
                        <a href="{{ route('chat.open', ['type' => 'growth_service', 'referenceId' => $order->listing_id, 'participantId' => $order->buyer_id]) }}"
                           class="w-full inline-flex items-center justify-center bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 py-2 rounded-lg transition-colors">
                            <i class="fas fa-comments mr-2"></i>Go to Messages
                        </a>
                    </div>
                    @endif
                </div>

                <!-- Seller Info -->
                @if($order->seller)
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6 mb-6">
                    <h3 class="font-semibold text-white mb-4">Seller</h3>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($order->seller->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-white">{{ $order->seller->name ?? 'Unknown' }}</p>
                            <p class="text-sm text-gray-400">Seller</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                @if(in_array($order->status, ['paid', 'in_progress', 'delivered', 'revision']))
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6">
                    <h3 class="font-semibold text-white mb-4">Actions</h3>
                    <div class="space-y-3">
                        @if(in_array($order->status, ['delivered', 'revision']))
                        <form action="{{ route('growth.orders.approve', $order) }}" method="POST" class="action-form">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg transition-colors">
                                <i class="fas fa-check mr-2"></i>Approve Delivery
                            </button>
                        </form>

                        <form action="{{ route('growth.orders.revision', $order) }}" method="POST" class="action-form">
                            @csrf
                            <input type="text" name="notes" required placeholder="Revision notes" class="w-full mb-2 px-3 py-2 rounded bg-dark-800 border border-dark-700 text-gray-200 text-sm">
                            <button type="submit" class="w-full bg-yellow-600/20 hover:bg-yellow-600/30 text-yellow-400 py-2 rounded-lg transition-colors">
                                <i class="fas fa-undo mr-2"></i>Request Revision
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('growth.orders.cancel', $order) }}" method="POST" class="action-form">
                            @csrf
                            <button type="submit" class="w-full bg-red-600/20 hover:bg-red-600/30 text-red-400 py-2 rounded-lg transition-colors" onclick="return confirm('Are you sure you want to cancel this order?')">
                                <i class="fas fa-times mr-2"></i>Cancel Order
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.action-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            });
            const data = await response.json();
            if (data.success) {
                window.location.reload();
                return;
            }
            alert(data.message || 'Action failed.');
        });
    });
</script>
@endpush
@endsection
