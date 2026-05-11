@extends('layouts.app')

@section('title', 'Order Details - Professional Services')

@section('content')
<div class="py-4 lg:py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('professional-services.orders.index') }}" class="inline-flex items-center text-indigo-400 hover:text-indigo-300 text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Orders
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-white">Order #{{ $order->id }}</h2>
                        <span class="px-3 py-1 text-xs rounded-full font-medium
                            @switch($order->status)
                                @case('completed') bg-green-500/20 text-green-400 @break
                                @case('paid') bg-blue-500/20 text-blue-400 @break
                                @case('delivered') bg-yellow-500/20 text-yellow-400 @break
                                @case('revision') bg-amber-500/20 text-amber-400 @break
                                @case('cancelled') bg-red-500/20 text-red-400 @break
                                @default bg-gray-500/20 text-gray-400 @break
                            @endswitch">
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </div>

                    <h3 class="font-semibold text-white mb-2">{{ $order->service->title ?? 'Service Removed' }}</h3>
                    <p class="text-gray-400 text-sm mb-4">{{ $order->service->description ?? 'No description available' }}</p>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Seller:</span>
                            <span class="text-white ml-2">{{ $order->seller->name ?? 'Unknown' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Created:</span>
                            <span class="text-white ml-2">{{ $order->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Delivery:</span>
                            <span class="text-white ml-2">{{ $order->service->delivery_days ?? 'N/A' }} days</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Revisions:</span>
                            <span class="text-white ml-2">{{ $order->revisions_requested ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                @if($order->requirements)
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6">
                    <h3 class="font-semibold text-white mb-3">Requirements</h3>
                    <p class="text-gray-400 text-sm whitespace-pre-line">{{ $order->requirements }}</p>
                </div>
                @endif

                @if($order->revision_notes)
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-amber-500/30 p-4 lg:p-6">
                    <h3 class="font-semibold text-white mb-3 flex items-center">
                        <i class="fas fa-undo mr-2 text-amber-400"></i>
                        Revision Request Notes
                    </h3>
                    <p class="text-gray-400 text-sm whitespace-pre-line">{{ $order->revision_notes }}</p>
                    @if($order->revisions_requested > 0)
                        <p class="text-xs text-gray-500 mt-2">
                            Revision #{{ $order->revisions_requested }} requested
                        </p>
                    @endif
                </div>
                @endif

                @if($order->delivery_notes)
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6">
                    <h3 class="font-semibold text-white mb-3">Delivery Notes</h3>
                    <div class="text-gray-400 text-sm whitespace-pre-line">{{ $order->delivery_notes }}</div>
                </div>
                @endif

                @if($order->messages && $order->messages->count() > 0)
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6">
                    <h3 class="font-semibold text-white mb-3">Messages</h3>
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        @foreach($order->messages as $message)
                            <div class="p-3 rounded-lg {{ $message->sender_id === auth()->id() ? 'bg-indigo-500/20 border border-indigo-500/30' : 'bg-dark-800 border border-dark-700' }}">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-gray-300">{{ $message->sender->name ?? 'User' }}</span>
                                    <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-gray-200 whitespace-pre-line">{{ $message->message }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="lg:col-span-1">
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6 mb-6">
                    <h3 class="font-semibold text-white mb-4">Payment Summary</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Service</span>
                            <span class="text-white">₦{{ number_format($order->service_price, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Add-ons</span>
                            <span class="text-white">₦{{ number_format($order->addons_total, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Platform Fee</span>
                            <span class="text-white">₦{{ number_format($order->platform_commission, 2) }}</span>
                        </div>
                        <div class="border-t border-dark-700 pt-3 flex justify-between font-semibold">
                            <span class="text-white">Total Paid</span>
                            <span class="text-green-400">₦{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>

                    @if(auth()->id() === $order->seller_id || auth()->id() === $order->buyer_id)
                    <div class="mt-4 pt-4 border-t border-dark-700">
                        <a href="{{ route('chat.open', ['type' => 'professional_service', 'referenceId' => $order->service_id, 'participantId' => auth()->id() === $order->seller_id ? $order->buyer_id : $order->seller_id]) }}"
                           class="w-full inline-flex items-center justify-center bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 py-2 rounded-lg transition-colors">
                            <i class="fas fa-comments mr-2"></i>Go to Messages
                        </a>
                    </div>
                    @endif
                </div>

                @if(auth()->id() === $order->seller_id && in_array($order->status, ['paid', 'in_progress']))
                <div id="seller-delivery" class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6 space-y-3">
                    <h3 class="font-semibold text-white mb-4">Deliver Service</h3>
                    <form action="{{ route('professional-services.orders.deliver', $order) }}" method="POST" class="delivery-form">
                        @csrf
                        <textarea name="notes" required minlength="10" placeholder="Delivery notes - describe what you've delivered" class="w-full mb-2 px-3 py-2 rounded bg-dark-800 border border-dark-700 text-gray-200 text-sm"></textarea>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i>Mark as Delivered
                        </button>
                    </form>
                </div>
                @endif

                @if(auth()->id() === $order->buyer_id && in_array($order->status, ['paid', 'delivered', 'revision']))
                <div id="order-actions" class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6 space-y-3">
                    <h3 class="font-semibold text-white mb-4">Buyer Actions</h3>

                    @if(in_array($order->status, ['delivered', 'revision']))
                    <form action="{{ route('professional-services.orders.approve', $order) }}" method="POST" class="action-form">
                        @csrf
                        <label class="block text-xs text-gray-400 mb-1">Rating (1-5)</label>
                        <select name="rating" required class="w-full mb-2 px-3 py-2 rounded bg-dark-800 border border-dark-700 text-gray-200 text-sm">
                            <option value="">Select rating</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Good</option>
                            <option value="3">3 - Okay</option>
                            <option value="2">2 - Poor</option>
                            <option value="1">1 - Bad</option>
                        </select>
                        <textarea name="comment" required minlength="10" placeholder="Leave a quick review before releasing payment" class="w-full mb-2 px-3 py-2 rounded bg-dark-800 border border-dark-700 text-gray-200 text-sm"></textarea>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg transition-colors">
                            <i class="fas fa-check mr-2"></i>Confirm Service & Release Payment
                        </button>
                    </form>

                    <form action="{{ route('professional-services.orders.revision', $order) }}" method="POST" class="action-form">
                        @csrf
                        <input type="text" name="notes" required placeholder="Revision notes" class="w-full mb-2 px-3 py-2 rounded bg-dark-800 border border-dark-700 text-gray-200 text-sm">
                        <button type="submit" class="w-full bg-amber-600/30 hover:bg-amber-600/40 text-amber-300 py-2 rounded-lg transition-colors">
                            <i class="fas fa-undo mr-2"></i>Request Revision
                        </button>
                    </form>
                    @endif

                    @if(in_array($order->status, ['paid', 'delivered']))
                    <form action="{{ route('professional-services.orders.cancel', $order) }}" method="POST" class="action-form">
                        @csrf
                        <button type="submit" class="w-full bg-red-600/20 hover:bg-red-600/30 text-red-400 py-2 rounded-lg transition-colors" onclick="return confirm('Cancel this order?')">
                            <i class="fas fa-times mr-2"></i>Cancel Order
                        </button>
                    </form>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.querySelectorAll('.action-form, .delivery-form').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                
                // Disable all submit buttons in this form to prevent double-click
                const submitButtons = form.querySelectorAll('button[type="submit"]');
                submitButtons.forEach(btn => {
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                    const originalText = btn.innerHTML;
                    btn.setAttribute('data-original-text', originalText);
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
                });

                try {
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
                        // Show success notification if possible
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message || 'Action completed successfully',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            window.location.reload();
                        }
                        return;
                    }
                    
                    // Handle validation errors
                    if (response.status === 422 || data.errors || data.error_list) {
                        // Collect error messages
                        let errorMsg = data.message || 'Please correct the following errors:';
                        if (data.errors) {
                            errorMsg += '\n\n' + Object.values(data.errors).flat().join('\n');
                        }
                        if (data.error_list) {
                            errorMsg += '\n\n' + Object.values(data.error_list).join('\n');
                        }
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                html: errorMsg.replace(/\n/g, '<br>'),
                            });
                        } else {
                            alert(errorMsg);
                        }
                    } else {
                        // General error
                        const msg = data.message || 'Action failed. Please try again.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg,
                            });
                        } else {
                            alert(msg);
                        }
                    }
                } catch (err) {
                    console.error('Form submission error:', err);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Unable to connect to the server. Please check your connection and try again.',
                        });
                    } else {
                        alert('An error occurred while submitting. Please try again.');
                    }
                } finally {
                    // Re-enable buttons
                    submitButtons.forEach(btn => {
                        btn.disabled = false;
                        btn.classList.remove('opacity-50', 'cursor-not-allowed');
                        const originalText = btn.getAttribute('data-original-text');
                        if (originalText) {
                            btn.innerHTML = originalText;
                        }
                    });
                }
            });
        });
    </script>
@endpush
@endsection
