@extends('layouts.app')

@section('title', 'Featured Products - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Featured Products</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Hand-picked premium digital products</p>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex flex-wrap gap-2 mb-8 border-b border-gray-200 dark:border-dark-700 pb-4">
            <a href="{{ route('digital-products.index') }}" class="px-4 py-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-800 transition-colors">
                <i class="fas fa-th-large mr-2"></i>Browse
            </a>
            <a href="{{ route('digital-products.my-products') }}" class="px-4 py-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-800 transition-colors">
                <i class="fas fa-box mr-2"></i>My Products
            </a>
            <a href="{{ route('digital-products.featured') }}" class="px-4 py-2 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-medium">
                <i class="fas fa-star mr-2"></i>Featured
            </a>
        </div>

        @if($products->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden hover:shadow-xl transition-all transform hover:-translate-y-1">
                        <!-- Thumbnail -->
                        <div class="aspect-video bg-gradient-to-br from-indigo-500 to-purple-600 relative">
                            @if($product->thumbnail)
                                <img src="{{ Storage::disk('public')->url($product->thumbnail) }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-file-download text-white text-4xl opacity-50"></i>
                                </div>
                            @endif
                            @if($product->is_featured)
                                <span class="absolute top-2 right-2 px-2 py-1 bg-yellow-500 text-white text-xs font-bold rounded">
                                    <i class="fas fa-star mr-1"></i>FEATURED
                                </span>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1 line-clamp-1">{{ $product->title }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">{{ $product->description }}</p>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    @if($product->sale_price && $product->sale_price < $product->price)
                                        <span class="text-sm text-gray-400 line-through">₦{{ number_format($product->price) }}</span>
                                        <span class="text-lg font-bold text-green-600 dark:text-green-400">₦{{ number_format($product->sale_price) }}</span>
                                    @else
                                        <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                            @if($product->is_free) Free @else ₦{{ number_format($product->price) }} @endif
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center text-yellow-500">
                                    <i class="fas fa-star text-sm"></i>
                                    <span class="ml-1 text-sm text-gray-600 dark:text-gray-400">{{ number_format($product->rating, 1) }}</span>
                                </div>
                            </div>

                            <a href="{{ route('digital-products.show', $product->id) }}" class="mt-3 block w-full text-center py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                View Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if(method_exists($products, 'hasPages') && $products->hasPages())
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-16 bg-white dark:bg-dark-900 rounded-2xl shadow-lg">
                <div class="w-20 h-20 bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-star text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">No Featured Products</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Check back later for featured products</p>
                <a href="{{ route('digital-products.index') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-th-large mr-2"></i>Browse All Products
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
