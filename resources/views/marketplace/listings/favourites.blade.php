@extends('layouts.app')

@section('title', 'My Favourites - Marketplace')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-white mb-6">
        <i class="fas fa-heart mr-2 text-red-400"></i>My Favourites
    </h1>

    @if($favourites->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($favourites as $favourite)
        @php
            $listing = $favourite->listing;
        @endphp
        @if($listing)
        <a href="{{ route('marketplace.listings.show', $listing->id) }}"
           class="listing-card bg-dark-800 rounded-2xl overflow-hidden border border-dark-700 hover:border-red-500/50 transition-all">
            @if($listing->images && count($listing->images) > 0)
                <img src="{{ asset('storage/' . $listing->images[0]) }}" alt="{{ $listing->title }}"
                     class="w-full h-48 object-cover"
                     onerror="this.src='https://via.placeholder.com/400x300/1e293b/475569?text=No+Image'">
            @else
                <div class="w-full h-48 bg-dark-700 flex items-center justify-center">
                    <i class="fas fa-image text-3xl text-gray-600"></i>
                </div>
            @endif
            <div class="p-4">
                <h3 class="text-white font-semibold text-lg truncate mb-1">{{ $listing->title }}</h3>
                <p class="text-gray-400 text-sm line-clamp-2">{{ $listing->description }}</p>
                <div class="flex justify-between items-center mt-3">
                    <span class="text-blue-400 font-bold">₦{{ number_format($listing->price, 2) }}</span>
                    <button class="text-red-400 hover:text-red-300" form="unfave-{{ $listing->id }}">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
            </div>
        </a>
        <form id="unfave-{{ $listing->id }}" method="POST" action="{{ route('marketplace.listings.favourite', $listing->id) }}" class="hidden">
            @csrf
        </form>
        @endif
        @endforeach
    </div>
    @else
    <div class="text-center py-20">
        <i class="fas fa-heart-broken text-6xl text-gray-600 mb-4"></i>
        <h3 class="text-xl text-gray-400 mb-2">No favourites yet</h3>
        <p class="text-gray-500">Browse the marketplace and add listings to your favourites!</p>
    </div>
    @endif
</div>
@endsection