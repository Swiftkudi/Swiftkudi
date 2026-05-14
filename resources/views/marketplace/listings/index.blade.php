@extends('layouts.app')

@section('title', 'Marketplace - Browse Listings')

@push('styles')
<style>
    .listing-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .listing-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.3);
    }
    .listing-img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 0.75rem 0.75rem 0 0;
    }
    .condition-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .condition-new { background: #d1fae5; color: #065f46; }
    .condition-like_new { background: #dbeafe; color: #1e40af; }
    .condition-good { background: #fef3c7; color: #92400e; }
    .condition-fair { background: #fee2e2; color: #991b1b; }
    .condition-used { background: #f3f4f6; color: #374151; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Marketplace</h1>
            <p class="text-gray-400 mt-1">Buy and sell with fellow students</p>
        </div>
        @auth
        <a href="{{ route('marketplace.listings.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>Post a Listing
        </a>
        @endauth
    </div>

    <!-- Search & Filters -->
    <div class="bg-dark-800 rounded-2xl p-6 mb-8 border border-dark-700">
        <form method="GET" action="{{ route('marketplace.listings.search') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="form-label">Search</label>
                <input type="text" name="q" value="{{ request('q') }}"
                       class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="form-label">Category</label>
                <select name="category" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-dark-600 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Condition</label>
                <select name="condition" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-dark-600 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    <option value="">All Conditions</option>
                    <option value="new" {{ request('condition') == 'new' ? 'selected' : '' }}>New</option>
                    <option value="like_new" {{ request('condition') == 'like_new' ? 'selected' : '' }}>Like New</option>
                    <option value="good" {{ request('condition') == 'good' ? 'selected' : '' }}>Good</option>
                    <option value="fair" {{ request('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                    <option value="used" {{ request('condition') == 'used' ? 'selected' : '' }}>Used</option>
                </select>
            </div>
            <div>
                <label class="form-label">Sort By</label>
                <select name="sort" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-dark-600 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    <option value="newest" {{ request('sort') == 'newest' || !request('sort') ? 'selected' : '' }}>Newest</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                    <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>Featured</option>
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="{{ route('marketplace.listings.index') }}" class="btn btn-secondary ml-2">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Listings Grid -->
    @if($listings->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($listings as $listing)
        <a href="{{ route('marketplace.listings.show', $listing->slug) }}"
           class="listing-card bg-dark-800 rounded-2xl overflow-hidden border border-dark-700 hover:border-blue-500/50">
            @if($listing->images && count($listing->images) > 0)
                <img src="{{ asset('storage/' . $listing->images[0]) }}" alt="{{ $listing->title }}" class="listing-img"
                     onerror="this.src='https://via.placeholder.com/400x300/1e293b/475569?text=No+Image'">
            @else
                <div class="listing-img bg-dark-700 flex items-center justify-center">
                    <i class="fas fa-image text-4xl text-gray-600"></i>
                </div>
            @endif
            <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-white font-semibold text-lg truncate flex-1">{{ $listing->title }}</h3>
                    <span class="condition-badge condition-{{ $listing->condition }}">
                        {{ str_replace('_', ' ', $listing->condition) }}
                    </span>
                </div>
                <p class="text-gray-400 text-sm mb-3 line-clamp-2">{{ $listing->description }}</p>
                <div class="flex justify-between items-center">
                    <span class="text-blue-400 font-bold text-lg">₦{{ number_format($listing->price, 2) }}</span>
                    <span class="text-gray-500 text-xs flex items-center">
                        <i class="fas fa-heart mr-1"></i>{{ $listing->favourites_count }}
                    </span>
                </div>
                @if($listing->is_featured)
                <div class="mt-2 flex items-center text-yellow-400 text-xs">
                    <i class="fas fa-star mr-1"></i> Featured
                </div>
                @endif
            </div>
        </a>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8 flex justify-center">
        {{ $listings->appends(request()->query())->links() }}
    </div>
    @else
    <div class="text-center py-20">
        <i class="fas fa-box-open text-6xl text-gray-600 mb-4"></i>
        <h3 class="text-xl text-gray-400 mb-2">No listings found</h3>
        <p class="text-gray-500">Be the first to post a listing on the marketplace!</p>
    </div>
    @endif
</div>
@endsection