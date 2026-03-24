@extends('layouts.app')

@section('title', 'Products Marketplace - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Products Marketplace
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Digital downloads, physical items & more
                    </p>
                </div>
                <a href="{{ route('digital-products.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
                    <i class="fas fa-plus mr-2"></i> Sell Product
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-2 mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('digital-products.index') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-th-large mr-2"></i> Browse
                </a>
                <a href="{{ route('digital-products.my-products') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-box mr-2"></i> My Products
                </a>
                <a href="{{ route('digital-products.my-purchases') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-shopping-bag mr-2"></i> My Purchases
                </a>
                <a href="{{ route('digital-products.featured') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-star mr-2"></i> Featured
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Total Products</h3>
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center">
                        <i class="fas fa-box text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $products->total() }}</div>
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Available items</div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Categories</h3>
                    <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                        <i class="fas fa-tags text-purple-600 dark:text-purple-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $categories->count() }}</div>
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Product types</div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Featured</h3>
                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center">
                        <i class="fas fa-star text-amber-600 dark:text-amber-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $products->where('is_featured', true)->count() }}</div>
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Premium listings</div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">On Sale</h3>
                    <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-percentage text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $products->where('discount_percentage', '>', 0)->count() }}</div>
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Discounted items</div>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 mb-8">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." 
                        class="w-full pl-10 pr-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <select name="category" class="px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <select name="sort" class="px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="latest">Latest</option>
                    <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Popular</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                </select>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all font-medium">
                    Filter
                </button>
            </form>
        </div>

        <!-- Products Grid -->
        @if($products->isEmpty())
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-16 text-center">
                <div class="w-20 h-20 mx-auto bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mb-4">
                    <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No products found</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Try adjusting your search or filters</p>
                <a href="{{ route('digital-products.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all font-medium">
                    <i class="fas fa-plus mr-2"></i> Be the first to sell
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden hover:shadow-xl hover:border-indigo-300 dark:hover:border-indigo-500 transition-all transform hover:-translate-y-1 group">
                        <a href="{{ route('digital-products.show', $product) }}">
                            <div class="aspect-video bg-gray-100 dark:bg-dark-800 relative overflow-hidden">
                                @if($product->thumbnail)
                                    <img src="{{ Storage::disk('public')->url($product->thumbnail) }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                @if($product->is_featured)
                                    <span class="absolute top-3 left-3 px-3 py-1 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs font-medium rounded-full shadow-lg">
                                        <i class="fas fa-star mr-1"></i>Featured
                                    </span>
                                @endif
                                @if($product->discount_percentage > 0)
                                    <span class="absolute top-3 right-3 px-3 py-1 bg-red-500 text-white text-xs font-medium rounded-full shadow-lg">
                                        -{{ $product->discount_percentage }}%
                                    </span>
                                @endif
                            </div>
                        </a>
                        <div class="p-5">
                            <a href="{{ route('digital-products.show', $product) }}">
                                <h3 class="font-bold text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ $product->title }}
                                </h3>
                            </a>
                            <div class="flex items-center mt-2 mb-3">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="h-4 w-4 {{ $i <= round($product->rating) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ number_format($product->rating, 1) }} ({{ $product->rating_count }})</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    @if($product->discount_percentage > 0)
                                        <span class="text-lg font-bold text-gray-400 line-through">₦{{ number_format($product->price, 2) }}</span>
                                        <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">₦{{ number_format($product->discounted_price, 2) }}</span>
                                    @else
                                        <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">₦{{ number_format($product->price, 2) }}</span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-dark-800 px-2 py-1 rounded-full">
                                    {{ $product->license_type }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
