@extends('layouts.app')

@section('title', 'My Purchases - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                My Purchases
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Download your purchased digital products</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Total Orders</h3>
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $orders->total() }}</div>
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Purchases</div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Completed</h3>
                    <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $orders->where('status', 'completed')->count() }}</div>
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Successful</div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Total Spent</h3>
                    <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                        <i class="fas fa-naira-sign text-purple-600 dark:text-purple-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">₦{{ number_format($orders->sum('amount'), 2) }}</div>
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Total investment</div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Downloads</h3>
                    <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center">
                        <i class="fas fa-download text-orange-600 dark:text-orange-400"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $orders->sum('download_count') }}</div>
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Files downloaded</div>
            </div>
        </div>

        @if($orders->isEmpty())
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-16 text-center">
                <div class="w-20 h-20 mx-auto bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mb-4">
                    <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No purchases yet</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Browse our marketplace to find digital products.</p>
                <a href="{{ route('digital-products.index') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all font-medium">
                    Browse Products
                </a>
            </div>
        @else
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-700">
                    <thead class="bg-gray-50 dark:bg-dark-800">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">License</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Downloads</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-dark-900 divide-y divide-gray-200 dark:divide-dark-700">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $order->order_number }}</span>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $order->created_at->format('M d, Y') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-12 w-12 rounded-xl bg-gray-100 dark:bg-dark-800 flex-shrink-0 overflow-hidden">
                                            @if($order->product->thumbnail)
                                                <img src="{{ Storage::url($order->product->thumbnail) }}" class="h-12 w-12 object-cover">
                                            @else
                                                <div class="h-12 w-12 flex items-center justify-center">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('digital-products.show', $order->product) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                                {{ $order->product->title }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-900 dark:text-white capitalize bg-gray-100 dark:bg-dark-800 px-3 py-1 rounded-full">{{ $order->license_type }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">₦{{ number_format($order->amount, 2) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @switch($order->status)
                                        @case('completed')
                                            <span class="px-3 py-1 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full">Completed</span>
                                            @break
                                        @case('pending')
                                            <span class="px-3 py-1 text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded-full">Pending</span>
                                            @break
                                        @case('failed')
                                            <span class="px-3 py-1 text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-full">Failed</span>
                                            @break
                                        @case('refunded')
                                            <span class="px-3 py-1 text-xs font-medium bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-full">Refunded</span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $order->download_count }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('digital-products.show', $order->product) }}" class="p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" title="View">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        @if($order->product && auth()->id() !== $order->product->user_id)
                                            <a href="{{ route('chat.open', ['type' => 'digital_product', 'referenceId' => $order->product_id, 'participantId' => $order->product->user_id]) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-purple-300 dark:border-purple-700 text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors" title="Chat">
                                                Chat
                                            </a>
                                        @endif
                                        @if($order->status === 'completed')
                                            <a href="#" class="p-2 text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors" title="Download">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
