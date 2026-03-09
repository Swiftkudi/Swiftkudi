@extends('layouts.app')

@section('title', $product->title . ' - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-6">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('digital-products.index') }}" class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">Products</a></li>
                <li class="text-gray-400">/</li>
                @if($product->category)
                <li><a href="{{ route('digital-products.index', ['category' => $product->category_id]) }}" class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">{{ $product->category->name }}</a></li>
                <li class="text-gray-400">/</li>
                @endif
                <li class="text-gray-900 dark:text-white font-medium">{{ Str::limit($product->title, 30) }}</li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Product Image -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden mb-6">
                    <div class="aspect-video bg-gray-100 dark:bg-dark-800">
                        @if($product->thumbnail)
                            <img src="{{ Storage::url($product->thumbnail) }}" alt="{{ $product->title }}" class="w-full h-full object-contain">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Description</h2>
                    <div class="prose dark:prose-invert max-w-none">
                        {!! nl2br(e($product->description)) !!}
                    </div>

                    @if($product->requirements)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-dark-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                            <i class="fas fa-info-circle text-indigo-600"></i> Requirements
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $product->requirements }}</p>
                    </div>
                    @endif

                    @if($product->changelog)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-dark-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                            <i class="fas fa-list-alt text-green-600"></i> Changelog
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $product->changelog }}</p>
                    </div>
                    @endif

                    @if($product->tags && count($product->tags) > 0)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-dark-700 flex flex-wrap gap-2">
                        @foreach($product->tags as $tag)
                            <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm rounded-full font-medium">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Reviews -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 mt-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Reviews ({{ $product->rating_count }})</h2>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="h-5 w-5 {{ $i <= round($product->rating) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                            </div>
                            <span class="ml-2 text-lg font-bold text-gray-900 dark:text-white">{{ number_format($product->rating, 1) }}</span>
                        </div>
                    </div>

                    @if($product->reviews->isNotEmpty())
                        <div class="space-y-6">
                            @foreach($product->reviews as $review)
                                <div class="border-b border-gray-200 dark:border-dark-700 pb-6 last:border-0 last:pb-0">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0">
                                            <div class="h-12 w-12 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-500 flex items-center justify-center">
                                                <span class="text-white font-semibold">{{ substr($review->user->name, 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $review->user->name }}</span>
                                                    @if($review->is_verified_purchase)
                                                        <span class="ml-2 px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs rounded-full font-medium">Verified Purchase</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                        </svg>
                                                    @endfor
                                                </div>
                                            </div>
                                            @if($review->comment)
                                                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $review->comment }}</p>
                                            @endif
                                            <p class="mt-2 text-sm text-gray-500">{{ $review->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <p class="mt-4 text-gray-500 dark:text-gray-400">No reviews yet. Be the first to review this product!</p>
                        </div>
                    @endif

                    @auth
                        @if(\App\Models\DigitalProductOrder::where('product_id', $product->id)->where('buyer_id', auth()->id())->where('status', 'completed')->exists())
                            <form method="POST" action="{{ route('digital-products.review', $product) }}" class="mt-8 pt-6 border-t border-gray-200 dark:border-dark-700">
                                @csrf
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Write a Review</h3>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rating</label>
                                    <div class="flex gap-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <button type="button" class="star-btn text-gray-300 dark:text-gray-600 hover:text-amber-400 transition-colors" data-rating="{{ $i }}">
                                                <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            </button>
                                        @endfor
                                        <input type="hidden" name="rating" id="rating" value="5" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Review</label>
                                    <textarea name="comment" rows="4" class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Share your experience with this product..."></textarea>
                                </div>
                                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all font-medium">
                                    Submit Review
                                </button>
                            </form>
                        @else
                            <p class="mt-6 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-dark-800 rounded-xl p-4">Purchase this product to leave a review.</p>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Purchase Card -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 sticky top-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $product->title }}</h1>
                    
                    <div class="flex items-center mb-4">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="h-5 w-5 {{ $i <= round($product->rating) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-gray-600 dark:text-gray-400">{{ number_format($product->rating, 1) }} ({{ $product->rating_count }})</span>
                        </div>
                    </div>

                    <div class="mb-6">
                        @if($product->sale_price)
                            <span class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">₦{{ number_format($product->sale_price, 2) }}</span>
                            <span class="ml-3 text-xl text-gray-400 line-through">₦{{ number_format($product->price, 2) }}</span>
                            <span class="ml-2 px-3 py-1 bg-red-500 text-white text-sm font-medium rounded-full">-{{ $product->discount_percentage }}%</span>
                        @else
                            <span class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $product->is_free ? 'Free' : '₦' . number_format($product->price, 2) }}
                            </span>
                        @endif
                    </div>

                    <!-- License Options -->
                    <div class="space-y-3 mb-6">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">License Type</label>
                        <div class="space-y-2">
                            <label class="flex items-center p-4 border border-gray-200 dark:border-dark-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20">
                                <input type="radio" name="license" value="1" checked class="text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900 dark:text-white">Personal License</span>
                                    <span class="block text-xs text-gray-500">For single user only</span>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border border-gray-200 dark:border-dark-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors">
                                <input type="radio" name="license" value="2" class="text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900 dark:text-white">Commercial License</span>
                                    <span class="block text-xs text-gray-500">For multiple users / projects</span>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border border-gray-200 dark:border-dark-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors">
                                <input type="radio" name="license" value="3" class="text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900 dark:text-white">Extended License</span>
                                    <span class="block text-xs text-gray-500">Unlimited usage / resale</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    @auth
                        @if($product->user_id === auth()->id())
                            <a href="{{ route('digital-products.edit', $product) }}" class="block w-full text-center px-4 py-3 bg-gray-800 dark:bg-dark-800 text-white rounded-xl hover:bg-gray-700 dark:hover:bg-dark-700 mb-3 font-medium transition-colors">
                                <i class="fas fa-edit mr-2"></i> Edit Product
                            </a>
                            <a href="{{ route('chat.index') }}" class="block w-full text-center px-4 py-3 border border-purple-300 dark:border-purple-700 text-purple-600 dark:text-purple-400 rounded-xl hover:bg-purple-50 dark:hover:bg-purple-900/20 mb-3 font-medium transition-colors">
                                <i class="fas fa-comments mr-2"></i> Open Messages
                            </a>
                        @else
                            <form method="POST" action="{{ route('digital-products.purchase', $product) }}">
                                @csrf
                                <button type="submit" class="block w-full text-center px-4 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all mb-3">
                                    {{ $product->is_free ? 'Download Now' : 'Buy Now' }}
                                </button>
                            </form>
                            <a href="{{ route('chat.open', ['type' => 'digital_product', 'referenceId' => $product->id, 'participantId' => $product->user_id]) }}" class="block w-full text-center px-4 py-3 border border-purple-300 dark:border-purple-700 text-purple-600 dark:text-purple-400 rounded-xl hover:bg-purple-50 dark:hover:bg-purple-900/20 mb-3 font-medium transition-colors">
                                <i class="fas fa-comments mr-2"></i> Chat with Seller
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block w-full text-center px-4 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
                            Login to Purchase
                        </a>
                    @endauth

                    <!-- Product Info -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-dark-700 space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Version</span>
                            <span class="text-gray-900 dark:text-white font-medium">{{ $product->version }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">File Size</span>
                            <span class="text-gray-900 dark:text-white font-medium">{{ $product->file_size ? number_format($product->file_size / 1024, 1) . ' KB' : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Total Sales</span>
                            <span class="text-gray-900 dark:text-white font-medium">{{ $product->total_sales }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Last Updated</span>
                            <span class="text-gray-900 dark:text-white font-medium">{{ $product->updated_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <!-- Seller Info -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-dark-700">
                        <div class="flex items-center gap-3">
                            <div class="h-12 w-12 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-500 flex items-center justify-center">
                                <span class="text-white font-bold">{{ substr($product->user->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $product->user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Seller</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts->isNotEmpty())
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Related Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $related)
                    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden hover:shadow-xl hover:border-indigo-300 dark:hover:border-indigo-500 transition-all transform hover:-translate-y-1 group">
                        <a href="{{ route('digital-products.show', $related) }}">
                            <div class="aspect-video bg-gray-100 dark:bg-dark-800">
                                @if($related->thumbnail)
                                    <img src="{{ Storage::url($related->thumbnail) }}" alt="{{ $related->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </a>
                        <div class="p-4">
                            <a href="{{ route('digital-products.show', $related) }}">
                                <h3 class="font-semibold text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $related->title }}</h3>
                            </a>
                            <div class="mt-2">
                                <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $related->is_free ? 'Free' : '₦' . number_format($related->current_price, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
