@extends('layouts.app')

@section('title', 'Disputes - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text text-transparent">Disputes</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Manage and track your dispute cases</p>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 mb-6">
            <div class="border-b border-gray-200 dark:border-dark-700">
                <nav class="flex -mb-px">
                    <a href="{{ route('disputes.index') }}" class="py-4 px-6 text-center border-b-2 font-medium text-sm 
                        {{ !request('status') ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        All Disputes
                    </a>
                    <a href="{{ route('disputes.index', ['status' => 'open']) }}" class="py-4 px-6 text-center border-b-2 font-medium text-sm 
                        {{ request('status') === 'open' ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        <i class="fas fa-exclamation-circle mr-2"></i>Open
                    </a>
                    <a href="{{ route('disputes.index', ['status' => 'resolved']) }}" class="py-4 px-6 text-center border-b-2 font-medium text-sm 
                        {{ request('status') === 'resolved' ? 'border-green-500 text-green-600 dark:text-green-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        <i class="fas fa-check-circle mr-2"></i>Resolved
                    </a>
                </nav>
            </div>

            <!-- Disputes List -->
            @if($disputes->count() > 0)
                <div class="divide-y divide-gray-200 dark:divide-dark-700">
                    @foreach($disputes as $dispute)
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $dispute->subject }}</h3>
                                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                                            @if($dispute->status === 'open') bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400
                                            @elseif($dispute->status === 'resolved') bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400
                                            @else bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400 @endif">
                                            {{ ucfirst($dispute->status) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">{{ $dispute->description }}</p>
                                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                        <span><i class="fas fa-tag mr-1"></i>{{ ucfirst($dispute->order_type) }}</span>
                                        <span><i class="fas fa-clock mr-1"></i>{{ $dispute->created_at->diffForHumans() }}</span>
                                        @if($dispute->resolved_at)
                                            <span><i class="fas fa-check mr-1"></i>Resolved {{ $dispute->resolved_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('disputes.show', $dispute) }}" class="ml-4 px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="p-6 border-t border-gray-200 dark:border-dark-700">
                    {{ $disputes->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-circle text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No disputes found</h3>
                    <p class="text-gray-500 dark:text-gray-400">You don't have any disputes</p>
                </div>
            @endif
        </div>

        <!-- How it Works -->
        <div class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/10 dark:to-orange-900/10 rounded-2xl p-6">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">How Disputes Work</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex gap-4">
                    <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                        <span class="text-red-600 dark:text-red-400 font-bold">1</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Submit Dispute</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Provide details and evidence for your case</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                        <span class="text-red-600 dark:text-red-400 font-bold">2</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Review Process</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Our team reviews within 48 hours</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                        <span class="text-red-600 dark:text-red-400 font-bold">3</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Resolution</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Fair resolution with refund if applicable</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
