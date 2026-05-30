@extends('layouts.app')

@section('title', 'Marketplace Listing Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('marketplace.listings.index') }}" class="text-blue-400 hover:underline mb-4 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>Back to Marketplace
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-3xl border border-dark-700 bg-dark-800 shadow-sm">
                <div class="relative overflow-hidden rounded-3xl">
                    @if($listing->images && count($listing->images) > 0)
                    <img src="{{ asset('storage/' . $listing->images[0]) }}" alt="{{ $listing->title }}" class="h-96 w-full object-cover">
                    @else
                    <div class="h-96 bg-dark-700 flex items-center justify-center">
                        <i class="fas fa-image text-6xl text-gray-600"></i>
                    </div>
                    @endif
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <span class="rounded-full bg-slate-700 px-3 py-1 text-xs uppercase tracking-[0.2em] text-slate-300">{{ $listing->condition === 'like_new' ? 'Like New' : ucfirst($listing->condition) }}</span>
                        @if($listing->is_featured)
                        <span class="rounded-full bg-yellow-400 px-3 py-1 text-xs font-semibold text-slate-900">Featured</span>
                        @endif
                        @if($listing->negotiable)
                        <span class="rounded-full bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300">Price Negotiable</span>
                        @endif
                    </div>

                    <h1 class="text-3xl font-bold text-white mb-4">{{ $listing->title }}</h1>

                    <div class="grid gap-4 sm:grid-cols-2 text-sm text-gray-400 mb-6">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user text-blue-400"></i>
                            <span>{{ $listing->seller->name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-blue-400"></i>
                            <span>{{ $listing->location ?? 'Location not specified' }}</span>
                        </div>
                        @if($listing->category)
                        <div class="flex items-center gap-2">
                            <i class="fas fa-tag text-blue-400"></i>
                            <span>{{ $listing->category->name }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="mb-6 flex items-center gap-4">
                        <div>
                            <p class="text-sm text-gray-400">Price</p>
                            <p class="text-4xl font-bold text-white">₦{{ number_format($listing->price, 2) }}</p>
                        </div>
                        @if($listing->available_for_shipping)
                        <div class="rounded-3xl bg-dark-700 px-4 py-3 text-sm text-gray-300">
                            Shipping: ₦{{ number_format($listing->shipping_cost, 2) }}
                        </div>
                        @endif
                    </div>

                    <div class="prose prose-invert max-w-none mb-6 text-gray-300">
                        {!! nl2br(e($listing->description)) !!}
                    </div>

                    @if($listing->tags && count($listing->tags) > 0)
                    <div class="flex flex-wrap gap-2 mb-6">
                        @foreach($listing->tags as $tag)
                        <span class="rounded-full bg-dark-700 px-3 py-1 text-xs text-gray-300">#{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif

                    <div class="grid gap-3 sm:grid-cols-2">
                        @if($canPurchase)
                        <form method="POST" action="{{ route('marketplace.orders.store', $listing->id) }}" class="flex gap-3">
                            @csrf
                            @if($listing->available_for_shipping)
                            <input type="hidden" name="shipping_cost" value="{{ $listing->shipping_cost ?? 0 }}">
                            @else
                            <input type="hidden" name="shipping_cost" value="0">
                            @endif
                            <button type="submit" class="btn btn-primary w-full">
                                <i class="fas fa-shopping-cart mr-2"></i>Purchase Now
                            </button>
                        </form>
                        @elseif(!Auth::check())
                        <a href="{{ route('login') }}" class="btn btn-primary w-full text-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login to Purchase
                        </a>
                        @else
                        <button disabled class="btn btn-secondary w-full opacity-50 cursor-not-allowed">
                            <i class="fas fa-exclamation-circle mr-2"></i>Wallet Required
                        </button>
                        @endif
                        <a href="{{ route('marketplace.listings.index') }}" class="btn btn-secondary w-full">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Shop
                        </a>
                    </div>
                </div>
            </div>

            @if($similar && $similar->isNotEmpty())
            <div class="rounded-3xl border border-dark-700 bg-dark-800 p-6">
                <h3 class="text-xl font-semibold text-white mb-4">Similar Listings</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach($similar as $item)
                    <a href="{{ route('marketplace.listings.show', $item->id) }}" class="block overflow-hidden rounded-3xl border border-dark-700 bg-dark-700 transition hover:border-blue-500/50">
                        @if($item->images && count($item->images) > 0)
                        <img src="{{ asset('storage/' . $item->images[0]) }}" alt="{{ $item->title }}" class="h-40 w-full object-cover">
                        @else
                        <div class="h-40 bg-dark-800 flex items-center justify-center text-gray-500">
                            <i class="fas fa-image text-2xl"></i>
                        </div>
                        @endif
                        <div class="p-4">
                            <p class="text-sm font-semibold text-white truncate">{{ $item->title }}</p>
                            <p class="mt-1 text-sm text-blue-400">₦{{ number_format($item->price, 2) }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <aside class="space-y-6">
            <div class="rounded-3xl border border-dark-700 bg-dark-800 p-6 sticky top-24">
                <h3 class="text-white font-semibold text-lg mb-4">Seller Info</h3>
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-xl font-bold text-white">
                        {{ strtoupper(substr($listing->seller->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-white font-medium">{{ $listing->seller->name }}</p>
                        <div class="flex items-center gap-2 text-sm text-gray-400">
                            @if($listing->seller->seller_rating)
                            <i class="fas fa-star text-yellow-400"></i>
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

                <div class="space-y-3 text-sm text-gray-400">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-trophy text-yellow-500"></i>
                        <span>@if($listing->seller->marketplaceSellerVerified()) Premium Seller @else Regular Seller @endif</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-shopping-bag text-blue-400"></i>
                        <span>{{ $listing->seller->marketplaceListings()->count() }} listings</span>
                    </div>
                </div>

                @auth
                @if(Auth::id() !== $listing->user_id)
                <div class="mt-6 space-y-3">
                    <form method="POST" action="{{ route('marketplace.listings.favourite', $listing->id) }}">
                        @csrf
                        <button type="submit" class="w-full rounded-2xl border border-dark-700 bg-dark-700 px-4 py-3 text-sm font-semibold text-white transition hover:border-blue-500">
                            <i class="{{ $isFavourited ? 'fas fa-heart text-red-500' : 'far fa-heart text-gray-300' }} mr-2"></i>
                            {{ $isFavourited ? 'Favourited' : 'Add to Favourites' }}
                        </button>
                    </form>
                    <a href="{{ route('marketplace.chat.index') }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-500 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-400 transition">
                        <i class="fas fa-comment-alt mr-2"></i>Contact Seller
                    </a>
                </div>
                @endif
                @endauth
                @guest
                <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-500 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-400 transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login to Chat
                </a>
                @endguest
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faveForm = document.querySelector('form[action$="/favourite"]');
    if (!faveForm) {
        return;
    }

    faveForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const button = this.querySelector('button');

        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.favourited) {
                button.innerHTML = '<i class="fas fa-heart text-red-500 mr-2"></i>Favourited';
            } else {
                button.innerHTML = '<i class="far fa-heart text-gray-300 mr-2"></i>Add to Favourites';
            }
        });
    });
});
</script>
@endpush