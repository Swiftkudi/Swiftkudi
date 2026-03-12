@extends('layouts.admin')

@section('title', 'Digital Product Details')

@section('content')
<div class="py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm mb-6">
            <a href="{{ route('admin.digital-products') }}" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i class="fas fa-box mr-1"></i> Digital Products
            </a>
            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            <span class="text-gray-900 dark:text-gray-100 font-medium truncate max-w-[200px]">{{ $product->title }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Product Info -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $product->title }}</h2>
                        <span class="px-3 py-1 text-sm rounded-full font-medium
                            @if($product->is_active)
                                bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300
                            @else
                                bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300
                            @endif">
                            {{ $product->is_active ? 'Active' : 'Pending' }}
                        </span>
                    </div>

                    <!-- Product Image -->
                    @if($product->thumbnail)
                        <div class="mb-6">
                            <img src="{{ Storage::disk('public')->url($product->thumbnail) }}" alt="{{ $product->title }}" class="w-full h-48 object-cover rounded-xl">
                        </div>
                    @endif

                    <!-- Category Badge -->
                    <div class="mb-4">
                        <span class="px-3 py-1 text-xs rounded-full font-medium bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300">
                            <i class="fas fa-folder mr-1"></i>
                            {{ $product->category->name ?? 'Uncategorized' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="p-4 bg-gray-50 dark:bg-dark-800 rounded-xl">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Price</div>
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">₦{{ number_format($product->price, 2) }}</div>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-dark-800 rounded-xl">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Sales</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $product->total_sales ?? $product->orders()->count() }}</div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Description</h3>
                        <div class="p-4 bg-gray-50 dark:bg-dark-800 rounded-xl text-gray-600 dark:text-gray-400 whitespace-pre-line">
                            {{ $product->description }}
                        </div>
                    </div>

                    <!-- File Info -->
                    @if($product->file_path)
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Product File</h3>
                            <div class="p-4 bg-gray-50 dark:bg-dark-800 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-500/20 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-download text-indigo-600 dark:text-indigo-400"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ basename($product->file_path) }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            @if($product->file_size)
                                                {{ round($product->file_size / 1024, 2) }} KB
                                            @else
                                                File attached
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Seller Info -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Seller Information</h3>
                    @if($product->user)
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($product->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $product->user->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $product->user->email }}</div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Member since {{ $product->user->created_at->format('M d, Y') }}
                        </div>
                        <a href="{{ route('admin.user-details', $product->user) }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors text-sm">
                            <i class="fas fa-user mr-2"></i>View Profile
                        </a>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">Seller information not available</p>
                    @endif
                </div>

                <!-- Stats -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Product Stats</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Created</span>
                            <span class="text-gray-900 dark:text-gray-100">{{ $product->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Total Orders</span>
                            <span class="text-gray-900 dark:text-gray-100">{{ $product->orders()->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Featured</span>
                            <span class="text-gray-900 dark:text-gray-100">{{ $product->is_featured ? 'Yes' : 'No' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Downloads</span>
                            <span class="text-gray-900 dark:text-gray-100">{{ $product->downloads ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                @if(!$product->is_active)
                    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Actions</h3>
                        <form action="{{ route('admin.digital-products.approve', $product) }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="w-full py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all shadow-lg shadow-green-500/30" onclick="return confirm('Are you sure you want to approve this product?')">
                                <i class="fas fa-check mr-2"></i>Approve Product
                            </button>
                        </form>

                        <button type="button" class="w-full py-3 bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300 font-semibold rounded-xl hover:bg-red-200 dark:hover:bg-red-500/30 transition-colors" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times mr-2"></i>Reject Product
                        </button>
                    </div>
                @endif

                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                    <h3 class="text-lg font-semibold text-red-700 dark:text-red-300 mb-4">Danger Zone</h3>
                    <form action="{{ route('admin.digital-products.delete', $product) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-3 bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300 font-semibold rounded-xl hover:bg-red-200 dark:hover:bg-red-500/30 transition-colors" onclick="return confirm('Delete this product? This action cannot be undone.')">
                            <i class="fas fa-trash mr-2"></i>Delete Product
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-white dark:bg-dark-900 rounded-2xl">
            <form action="{{ route('admin.digital-products.reject', $product) }}" method="POST">
                @csrf
                <div class="p-6 border-b border-gray-200 dark:border-dark-700">
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Reject Product</h5>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rejection Reason <span class="text-red-500">*</span></label>
                        <textarea name="rejection_reason" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="4" required placeholder="Please provide a clear reason for rejection..."></textarea>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">This will be shared with the seller.</p>
                    </div>
                </div>
                <div class="p-6 border-t border-gray-200 dark:border-dark-700 flex justify-end gap-3">
                    <button type="button" class="px-4 py-2 bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors">Reject Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
