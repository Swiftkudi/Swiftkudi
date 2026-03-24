@extends('layouts.app')

@section('title', 'Growth Marketplace - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Growth Marketplace</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Backlinks, Influencers, Newsletters & Leads to grow your business</p>
                </div>
                <a href="{{ route('growth.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium hover:opacity-90 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-plus mr-2"></i> Create Listing
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-2 mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('growth.index') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-th-large mr-2"></i> Browse
                </a>
                <a href="{{ route('growth.my-listings') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-list mr-2"></i> My Listings
                </a>
                <a href="{{ route('growth.orders.index') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-shopping-cart mr-2"></i> My Orders
                </a>
                <a href="{{ route('growth.sales.index') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-chart-line mr-2"></i> Sales
                </a>
            </div>
        </div>

        <!-- Type Filters -->
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('growth.index') }}" class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ !$type ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-dark-600' }}">
                All
            </a>
            @foreach($types as $key => $label)
                <a href="{{ route('growth.type', $key) }}" class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ $type === $key ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-dark-600' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Search -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 mb-8">
            <form action="{{ route('growth.index') }}" method="GET" class="flex gap-4">
                @if($type)
                    <input type="hidden" name="type" value="{{ $type }}">
                @endif
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search listings..." 
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium hover:opacity-90 transition-all">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
            </form>
        </div>

        <!-- Listings Grid -->
        @if($listings->isEmpty())
            <div class="text-center py-12 bg-white dark:bg-dark-900 rounded-2xl shadow-lg">
                <div class="w-16 h-16 bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-line text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No listings found</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">Try adjusting your search or browse all types</p>
                <a href="{{ route('growth.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-medium hover:opacity-90">
                    <i class="fas fa-plus mr-2"></i> Create a Listing
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($listings as $listing)
                    <a href="{{ route('growth.show', $listing->id) }}" class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl transition-all group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-{{ $listing->type === 'backlinks' ? 'link' : ($listing->type === 'influencer' ? 'user-star' : ($listing->type === 'newsletter' ? 'envelope' : 'address-card')) }} text-white"></i>
                            </div>
                            <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs rounded-full uppercase">
                                {{ $listing->type }}
                            </span>
                        </div>
                        
                        <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-2 group-hover:text-indigo-600 transition-colors">
                            {{ $listing->title }}
                        </h3>
                        
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-2">
                            {{ $listing->description }}
                        </p>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                    ₦{{ number_format($listing->price) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                <span><i class="fas fa-clock mr-1"></i> {{ $listing->delivery_days }} days</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                {{ $listings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
