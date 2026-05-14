@extends('layouts.app')

@section('title', 'Marketplace Listing Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('marketplace.listings.index') }}" class="text-blue-400 hover:underline mb-4 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>Back to Marketplace
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-dark-800 rounded-2xl overflow-hidden border border-dark-700">
                @if($listing->images && count($listing->images) > 0)
                    <img src="{{ asset('storage/' . $listing->images[0]) }}" alt="{{ $listing->title }}"
                         class="w-full h-96 object-cover"
                         onerror="this.src='https://via.placeholder.com/800x400/1e293b/475569?text=No+Image'">
                @else
                    <div class="w-full h-96 bg-dark-700 flex items-center justify-center">
                        <i class="fas fa-image text-6xl text-gray-600"></i>
                    </div>
                @endif
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="condition-badge condition-{{ $listing->condition }}">
                            {{ str_replace('_', ' ', $listing->condition) }}
                        </span>
                        @if($listing->is_featured)
                        <span class="bg-yellow-500/10 text-yellow-400 px-2 py-1 rounded-full text-xs font-semibold">
                            <i class="fas fa-star mr-1"></i>Featured
                        </span>
                        @endif
                    </div>

                    <h1 class="text-2xl font-bold text-white mb-2">{{ $listing->title }}</h1>

                    <div class="flex items-center gap-4 text-sm text-gray-400 mb-4">
                        <span class="flex items-center">
                            <i class="fas fa-user mr-1"></i>
                            {{ $listing->seller->name }}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            {{ $listing->location ?? 'Location not specified' }}
                        </span>
                        @if($listing->category)
                        <span class="flex items-center">
                            <i class="fas fa-tag mr-1"></i>
                            {{ $listing->category->name }}
                        </span>
                        @endif
                    </div>

                    @if($listing->negotiable)
                    <span class="inline-block bg-blue-500/10 text-blue-400 px-3 py-1 rounded-full text-xs font-semibold mb-4">
                        <i class="fas fa-handshake mr-1"></i>Price Negotiable
                    </span>
                    @endif

                    <div class="text-3xl font-bold text-white mb-6">
                        ₦{{ number_format($listing->price, 2) }}
                        @if($listing->available_for_shipping)
                        <span class="text-base text-gray-400 font-normal">+ ₦{{ number_format($listing->shipping_cost, 2) }} shipping</span>
                        @endif
                    </div>

                    <div class="prose prose-invert max-w-none mb-6">
                        {!! nl2br(e($listing->description)) !!}
                    </div>

                    @if($listing->tags && count($listing->tags) > 0)
                    <div class="flex flex-wrap gap-2 mb-6">
                        @foreach($listing->tags as $tag)
                        <span class="bg-dark-700 text-gray-300 px-3 py-1 rounded-full text-xs">
                            #{{ $tag }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    @if($canPurchase)
                    <form method="POST" action="{{ route('marketplace.orders.store', $listing->slug) }}" class="flex gap-3">
                        @csrf
                        <button type="submit" class="btn btn-primary flex-1">
                            <i class="fas fa-shopping-cart mr-2"></i>Purchase Now
                        </button>
                    </form>
                    @elseif(!Auth::check())
                    <a href="{{ route('login') }}" class="btn btn-primary flex-1 text-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login to Purchase
                    </a>
                    @else
                    <button disabled class="btn btn-secondary flex-1 opacity-50 cursor-not-allowed">
                        <i class="fas fa-exclamation-circle mr-2"></i>Wallet Required
                    </button>
                    @endif
                </div>
            </div>

            <!-- Similar Listings -->
            @if($similar && $similar->isNotEmpty())
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-white mb-4">Similar Listings</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach($similar as $item)
                    <a href="{{ route('marketplace.listings.show', $item->slug) }}"
                       class="bg-dark-800 rounded-xl overflow-hidden border border-dark-700 hover:border-blue-500/50 transition-all">
                        @if($item->images && count($item->images) > 0)
                            <img src="{{ asset('storage/' . $item->images[0]) }}" alt="{{ $item->title }}"
                                 class="w-full h-32 object-cover"
                                 onerror="this.src='https://via.placeholder.com/200x150/1e293b/475569?text=No+Image'">
                        @else
                            <div class="w-full h-32 bg-dark-700 flex items-center justify-center">
                                <i class="fas fa-image text-2xl text-gray-600"></i>
                            </div>
                        @endif
                        <div class="p-3">
                            <p class="text-white font-medium text-sm truncate">{{ $item->title }}</p>
                            <p class="text-blue-400 text-sm font-semibold">₦{{ number_format($item->price, 2) }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div>
            <div class="bg-dark-800 rounded-2xl p-6 border border-dark-700 sticky top-24">
                <h3 class="text-white font-semibold text-lg mb-4">Seller Info</h3>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($listing->seller->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-white font-medium">{{ $listing->seller->name }}</p>
                        <div class="flex items-center text-sm text-gray-400">
                            @if($listing->seller->seller_rating)
                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                            <span>{{ number_format($listing->seller->seller_rating, 1) }} ({{ $listing->seller->seller_rating_count }} reviews)</span>
                            @else
                            <span>No ratings yet</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($listing->seller->marketplace_bio)
                <p class="text-gray-400 text-sm mb-4">{{ $listing->seller->marketplace_bio }}</p>
                @endif

                <div class="space-y-2 text-sm">
                    <div class="flex items-center text-gray-400">
                        <i class="fas fa-trophy mr-2 text-yellow-500 w-5"></i>
                        <span>
                            @if($listing->seller->isPremiumSeller())
                            Premium Seller
                            @else
                            Regular Seller
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center text-gray-400">
                        <i class="fas fa-shopping-bag mr-2 text-blue-400 w-5"></i>
                        <span>{{ $listing->seller->marketplaceListings()->count() }} listings</span>
                    </div>
                </div>

                @auth
                @if(Auth::id() !== $listing->user_id)
                <div class="mt-6">
                    @component('components.chat-button', ['userId' => $listing->seller->id, 'listingId' => $listing->id])
                    @endcomponent
                </div>
                @endif
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faveBtn = document.getElementById('toggle-favourite');
    if (faveBtn) {
        faveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetch(this.href, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                const icon = this.querySelector('i');
                if (data.favourited) {
                    icon.className = 'fas fa-heart text-red-500';
                    this.querySelector('span').textContent = 'Favourited';
                } else {
                    icon.className = 'far fa-heart text-gray-400';
                    this.querySelector('span').textContent = 'Add to Favourites';
                }
            });
        });
    }
});
</script>
@endpush