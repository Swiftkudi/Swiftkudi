@extends('layouts.admin')

@section('title', 'Digital Products Management')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Digital Products Management</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Review and approve pending digital products for the marketplace</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-500/20 flex items-center justify-center">
                        <i class="fas fa-box text-gray-600 dark:text-gray-400"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Active</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['active'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-check text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Rejected</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['rejected'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-500/20 flex items-center justify-center">
                        <i class="fas fa-times text-red-600 dark:text-red-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-4 mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.digital-products', ['status' => 'pending']) }}" 
                   class="px-4 py-2 rounded-xl text-sm font-medium {{ request('status') === 'pending' || !request('status') ? 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300' : 'bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                    Pending
                </a>
                <a href="{{ route('admin.digital-products', ['status' => 'active']) }}" 
                   class="px-4 py-2 rounded-xl text-sm font-medium {{ request('status') === 'active' ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                    Active
                </a>
                <a href="{{ route('admin.digital-products', ['status' => 'rejected']) }}" 
                   class="px-4 py-2 rounded-xl text-sm font-medium {{ request('status') === 'rejected' ? 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300' : 'bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                    Rejected
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-100 dark:bg-green-500/20 border border-green-400 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <!-- Products Table -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 overflow-hidden">
            @if($products->count() > 0)
                <form id="bulk-form-digital-products" action="{{ route('admin.digital-products.bulk-delete') }}" method="POST">@csrf</form>
                <div id="bulk-toolbar-digital-products" class="hidden px-6 py-3 bg-red-100 dark:bg-red-500/10 border-b border-red-200 dark:border-red-500/20 flex items-center justify-between">
                    <span class="text-sm text-red-600 dark:text-red-400 font-medium"><span id="bulk-count-digital-products">0</span> selected</span>
                    <button type="button" onclick="submitBulkDelete('digital-products')" class="px-4 py-1.5 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-500/30 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-trash mr-2"></i>Delete Selected
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-dark-800">
                            <tr>
                                <th class="px-4 py-4 w-10">
                                    <input type="checkbox" id="select-all-digital-products" class="bulk-select-all w-4 h-4 rounded cursor-pointer" data-target="bulk-cb-digital-products">
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Seller</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-dark-700">
                            @foreach($products as $product)
                                <tr class="hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors">
                                    <td class="px-4 py-4">
                                        <input type="checkbox" name="ids[]" value="{{ $product->id }}" class="bulk-cb-digital-products w-4 h-4 rounded cursor-pointer">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($product->thumbnail)
                                                <img src="{{ Storage::disk('public')->url($product->thumbnail) }}" alt="{{ $product->title }}" class="w-10 h-10 rounded-lg object-cover">
                                            @else
                                                <div class="w-10 h-10 bg-gray-100 dark:bg-dark-800 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-file text-gray-400"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $product->title }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ Str::limit($product->description, 50) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 text-xs rounded-full font-medium bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300">
                                            {{ $product->category->name ?? 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ $product->user->name ?? 'Unknown' }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $product->user->email ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-green-600 dark:text-green-400">₦{{ number_format($product->price, 2) }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 text-xs rounded-full font-medium
                                            @if($product->is_active)
                                                bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300
                                            @else
                                                bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300
                                            @endif">
                                            {{ $product->is_active ? 'Active' : 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $product->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('admin.digital-products.show', $product) }}" 
                                               class="inline-flex items-center px-3 py-1.5 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm font-medium hover:bg-indigo-200 dark:hover:bg-indigo-500/30 transition-colors">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                            <form action="{{ route('admin.digital-products.delete', $product) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300 rounded-lg text-sm font-medium hover:bg-red-200 dark:hover:bg-red-500/30 transition-colors" onclick="return confirm('Delete this product? This action cannot be undone.')">
                                                    <i class="fas fa-trash mr-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-dark-700">
                        {{ $products->links() }}
                    </div>
                @endif
            @else
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box-open text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">No Products Found</h3>
                    <p class="text-gray-500 dark:text-gray-400">No digital products match the current filter.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
