@extends('layouts.app')

@section('title', 'Hire Professionals - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Hire Professionals</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Find skilled professionals for your projects</p>
                </div>
                <a href="{{ route('professional-services.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium hover:opacity-90 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-plus mr-2"></i> Create Service
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-2 mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('professional-services.index') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-th-large mr-2"></i> Browse
                </a>
                <a href="{{ route('professional-services.my-services') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-briefcase mr-2"></i> My Services
                </a>
                <a href="{{ route('professional-services.orders.index') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-shopping-cart mr-2"></i> My Orders
                </a>
                <a href="{{ route('professional-services.sales.index') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-chart-line mr-2"></i> Sales
                </a>
                <a href="{{ route('professional-services.edit-profile') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-user-cog mr-2"></i> Profile
                </a>
            </div>
        </div>

        <!-- Category Filters -->
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('professional-services.index') }}" class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ !request('category') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-dark-600' }}">
                All Services
            </a>
            @foreach($categories as $category)
                <a href="{{ route('professional-services.index', ['category' => $category->slug]) }}" class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ request('category') === $category->slug ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-dark-600' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <!-- Search -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 mb-8">
            <form action="{{ route('professional-services.index') }}" method="GET" class="flex gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search services..." 
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium hover:opacity-90 transition-all">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
            </form>
        </div>

        <!-- Services Grid -->
        @if($services->isEmpty())
            <div class="text-center py-12 bg-white dark:bg-dark-900 rounded-2xl shadow-lg">
                <div class="w-16 h-16 bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-briefcase text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No services found</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">Try adjusting your search or browse all categories</p>
                <a href="{{ route('professional-services.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-medium hover:opacity-90">
                    <i class="fas fa-plus mr-2"></i> Create a Service
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($services as $service)
                    <a href="{{ route('professional-services.show', $service->id) }}" class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl transition-all group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-briefcase text-white"></i>
                            </div>
                            <span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs rounded-full">
                                {{ $service->category->name ?? 'Uncategorized' }}
                            </span>
                        </div>
                        
                        <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-2 group-hover:text-indigo-600 transition-colors">
                            {{ $service->title }}
                        </h3>
                        
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-2">
                            {{ $service->description }}
                        </p>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                    ₦{{ number_format($service->price) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                <span><i class="fas fa-clock mr-1"></i> {{ $service->delivery_days }} days</span>
                                <span><i class="fas fa-redo mr-1"></i> {{ $service->revisions_included }} revisions</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                <x-pagination :paginator="$services" :showPerPage="true" />
            </div>
        @endif
    </div>
</div>
@endsection
