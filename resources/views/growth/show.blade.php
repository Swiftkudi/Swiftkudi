@extends('layouts.app')

@section('title', $listing->title . ' - Growth Marketplace')

@section('content')
<div class="py-4 lg:py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('growth.index') }}" class="inline-flex items-center text-indigo-400 hover:text-indigo-300 text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Marketplace
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6">
                    <span class="px-3 py-1 text-xs rounded-full mb-4 inline-block font-medium
                        @switch($listing->type)
                            @case('backlinks') bg-blue-500/20 text-blue-400 @break
                            @case('influencer') bg-pink-500/20 text-pink-400 @break
                            @case('newsletter') bg-purple-500/20 text-purple-400 @break
                            @case('leads') bg-green-500/20 text-green-400 @break
                            @default bg-gray-500/20 text-gray-400 @break
                        @endswitch">
                        <i class="fas 
                            @switch($listing->type)
                                @case('backlinks') fa-link @break
                                @case('influencer') fa-user-check @break
                                @case('newsletter') fa-envelope @break
                                @case('leads') fa-users @break
                                @default fa-tag @break
                            @endswitch mr-1"></i>
                        {{ ucfirst($listing->type) }}
                    </span>

                    <h1 class="text-xl lg:text-2xl font-bold text-white mb-4">{{ $listing->title }}</h1>
                    <p class="text-gray-400 mb-6 text-sm lg:text-base">{{ $listing->description }}</p>

                    @if($listing->specs)
                    @php $specs = is_string($listing->specs) ? json_decode($listing->specs, true) : $listing->specs; @endphp
                    @if(is_array($specs) && count($specs) > 0)
                    <div class="mb-6">
                        <h3 class="font-semibold text-white mb-3">Specifications</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($specs as $key => $value)
                            <div class="p-3 lg:p-4 bg-dark-800 rounded-xl">
                                <div class="text-xs text-gray-500 mb-1">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
                                <div class="font-medium text-white text-sm lg:text-base">{{ is_array($value) ? json_encode($value) : $value }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @endif
                    
                    <!-- Seller Info -->
                    <div class="border-t border-dark-700 pt-6 mt-6">
                        <h3 class="font-semibold text-white mb-3">Seller Information</h3>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($listing->user->name ?? 'U', 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-medium text-white">{{ $listing->user->name ?? 'Unknown' }}</p>
                                <p class="text-sm text-gray-400">Member since {{ $listing->user && $listing->user->created_at ? $listing->user->created_at->format('M Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6 sticky top-20">
                    <div class="text-center mb-6">
                        <div class="text-3xl lg:text-4xl font-bold text-green-400 mb-1">₦{{ number_format($listing->price, 2) }}</div>
                        <div class="flex items-center justify-center gap-2 text-sm text-gray-400">
                            <i class="fas fa-clock"></i>
                            <span>{{ $listing->delivery_days }} days delivery</span>
                        </div>
                    </div>

                    @if($listing->status === 'active')
                        <button onclick="createOrder()" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-3 rounded-xl font-semibold hover:from-green-600 hover:to-emerald-700 transition-all shadow-lg shadow-green-500/30">
                            <i class="fas fa-shopping-cart mr-2"></i> Order Now
                        </button>
                    @else
                        <div class="w-full bg-dark-800 text-gray-400 py-3 rounded-xl text-center">
                            <i class="fas fa-ban mr-2"></i> Not Available
                        </div>
                    @endif
                    
                    <!-- Contact Seller Button -->
                    @auth
                        <button onclick="showContactModal()" class="mt-3 w-full py-3 border-2 border-indigo-500/30 text-indigo-400 font-medium rounded-xl flex items-center justify-center gap-2 hover:bg-indigo-500/10 transition-colors">
                            <i class="fas fa-comment-dots"></i>
                            Contact Seller
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="mt-3 w-full py-3 border-2 border-indigo-500/30 text-indigo-400 font-medium rounded-xl flex items-center justify-center gap-2 hover:bg-indigo-500/10 transition-colors">
                            <i class="fas fa-sign-in-alt"></i>
                            Login to Contact
                        </a>
                    @endauth
                    
                    <div class="mt-6 pt-6 border-t border-dark-700">
                        <div class="flex items-center gap-2 text-sm text-gray-400 mb-3">
                            <i class="fas fa-shield-alt text-green-400"></i>
                            <span>Secure payment via escrow</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-400">
                            <i class="fas fa-undo text-blue-400"></i>
                            <span>Money-back guarantee</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Seller Modal -->
@auth
<div id="contact-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-dark-900 rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-dark-700">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 rounded-t-3xl">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">Contact Seller</h2>
                <button onclick="hideContactModal()" class="text-white/80 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <p class="text-indigo-200 mt-1">Send a message to {{ $listing->user->name ?? 'the seller' }}</p>
        </div>

        <!-- Modal Body -->
        <form id="contact-form" class="p-6 space-y-6">
            @csrf
            <input type="hidden" name="recipient_id" value="{{ $listing->user_id }}">
            
            <div>
                <label class="block text-sm font-semibold text-gray-300 mb-2">
                    Subject
                </label>
                <input type="text" name="subject" 
                       class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="What's this about?" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-300 mb-2">
                    Message
                </label>
                <textarea name="message" rows="5" 
                          class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                          placeholder="Write your message here..." required></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="hideContactModal()" 
                        class="flex-1 py-3 bg-dark-700 text-gray-300 rounded-xl font-medium hover:bg-dark-600 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-paper-plane mr-2"></i>Send
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
async function createOrder() {
    const form = document.querySelector('form');
    try {
        const response = await fetch('{{ route("growth.order", $listing->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        if(data.success) {
            alert('Order placed successfully!');
            window.location.href = data.redirect;
        } else {
            // Check if it's an insufficient balance error
            if(data.message && data.message.toLowerCase().includes('insufficient')) {
                if(confirm(data.message + '\n\nWould you like to deposit funds now?')) {
                    // Store the current page to return to after deposit
                    sessionStorage.setItem('return_after_deposit', window.location.href);
                    sessionStorage.setItem('deposit_amount', {{ $listing->price }});
                    window.location.href = '{{ route("wallet.deposit") }}';
                }
            } else {
                if ((response.status === 422 || data.errors || data.error_list) && window.SwiftkudiFormFeedback && form) {
                    window.SwiftkudiFormFeedback.showValidationErrors(form, data, {
                        boxId: 'growth-order-error-box',
                    });
                } else {
                    alert(data.message || 'Failed to place order');
                }
            }
        }
    } catch(err) {
        if (window.SwiftkudiFormFeedback && form) {
            window.SwiftkudiFormFeedback.showValidationErrors(form, {
                message: 'An error occurred while placing your order. Please try again.',
            }, {
                boxId: 'growth-order-error-box',
            });
        } else {
            alert('An error occurred. Please try again.');
        }
    }
}

// Contact Seller Modal Functions
@if(auth()->check())
function showContactModal() {
    document.getElementById('contact-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideContactModal() {
    document.getElementById('contact-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Handle contact form submission
document.getElementById('contact-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    
    fetch('{{ route("growth.contact") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success && (data.errors || data.error_list) && window.SwiftkudiFormFeedback) {
            window.SwiftkudiFormFeedback.showValidationErrors(form, data, {
                boxId: 'growth-contact-error-box',
            });
            return;
        }

        alert(data.message);
        if (data.success) {
            hideContactModal();
            document.getElementById('contact-form').reset();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.SwiftkudiFormFeedback) {
            window.SwiftkudiFormFeedback.showValidationErrors(form, {
                message: 'An error occurred while sending your message. Please try again.',
            }, {
                boxId: 'growth-contact-error-box',
            });
        } else {
            alert('An error occurred. Please try again.');
        }
    });
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideContactModal();
    }
});

// Close modal on backdrop click
document.getElementById('contact-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        hideContactModal();
    }
});
@endif
</script>
@endsection
