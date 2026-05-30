@extends('layouts.app')

@section('title', 'My Listings - Marketplace')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-white">
            <i class="fas fa-store mr-2 text-blue-400"></i>My Listings
        </h1>
        <a href="{{ route('marketplace.listings.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>New Listing
        </a>
    </div>

    <!-- Status Filters -->
    <div class="flex gap-3 mb-6">
        @foreach(['all' => 'All', 'draft' => 'Drafts', 'pending_review' => 'Pending', 'active' => 'Active', 'sold' => 'Sold'] as $status => $label)
            @if($status !== 'all')
            <a href="?status={{ $status }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('status') == $status ? 'bg-blue-500 text-white' : 'bg-dark-800 text-gray-400 hover:text-white border border-dark-700' }}">
                {{ $label }}
            </a>
            @endif
        @endforeach
    </div>

    @if($listings->isNotEmpty())
    <div class="space-y-4">
        @foreach($listings as $listing)
        <div class="bg-dark-800 rounded-2xl p-6 border border-dark-700 flex flex-col md:flex-row gap-6">
            <div class="md:w-48 md:h-48 rounded-xl overflow-hidden bg-dark-700 flex-shrink-0">
                @if($listing->images && count($listing->images) > 0)
                    <img src="{{ asset('storage/' . $listing->images[0]) }}" alt="{{ $listing->title }}"
                         class="w-full h-full object-cover"
                         onerror="this.src='https://via.placeholder.com/200x200/1e293b/475569?text=No+Image'">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-image text-3xl text-gray-600"></i>
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-white font-semibold text-lg">{{ $listing->title }}</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        {{ $listing->status == 'active' ? 'bg-green-500/10 text-green-400' : ($listing->status == 'sold' ? 'bg-yellow-500/10 text-yellow-400' : ($listing->status == 'pending_review' ? 'bg-blue-500/10 text-blue-400' : 'bg-gray-500/10 text-gray-400')) }}">
                        {{ str_replace('_', ' ', ucfirst($listing->status)) }}
                    </span>
                </div>
                <p class="text-gray-400 text-sm mb-3 line-clamp-2">{{ $listing->description }}</p>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-blue-400 font-bold">₦{{ number_format($listing->price, 2) }}</span>
                    <span class="text-gray-500 text-xs">
                        {{ $listing->orders()->count() }} order(s) • {{ $listing->favourites_count }} favourites • {{ $listing->views_count }} views
                    </span>
                </div>
                <div class="flex gap-3 mt-4">
                    @if($listing->status === 'draft')
                    <a href="{{ route('marketplace.listings.edit', $listing->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                    @endif
                    <a href="{{ route('marketplace.listings.show', $listing->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                    @if($listing->status !== 'sold')
                    <form method="POST" action="{{ route('marketplace.listings.destroy', $listing->id) }}"
                          onsubmit="return confirm('Are you sure you want to delete this listing?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8 flex justify-center">
        {{ $listings->appends(request()->query())->links() }}
    </div>
    @else
    <div class="text-center py-20 bg-dark-800 rounded-2xl border border-dark-700">
        <i class="fas fa-store text-6xl text-gray-600 mb-4"></i>
        <h3 class="text-xl text-gray-400 mb-2">No listings yet</h3>
        <p class="text-gray-500 mb-6">Start selling by creating your first listing.</p>
        <a href="{{ route('marketplace.listings.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>Create Your First Listing
        </a>
    </div>
    @endif
</div>
@endsection