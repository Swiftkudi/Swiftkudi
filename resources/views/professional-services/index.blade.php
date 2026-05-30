@extends('layouts.app')

@section('title', 'Hire Professionals - SwiftKudi')

@section('content')
@php
$user = auth()->user();
$accountType = $user->account_type ?? '';
@endphp
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="rounded-[2rem] border border-gray-100 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-lg p-8">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
                    <div class="max-w-3xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.35em] text-indigo-600">Professional services</p>
                        <h1 class="mt-4 text-4xl sm:text-5xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Hire experts to complete your most important work</h1>
                        <p class="mt-4 text-gray-600 dark:text-gray-400 text-lg max-w-2xl">Browse verified services, connect with top sellers, and launch your project with confidence.</p>
                    </div>
                    <div class="inline-flex shrink-0 rounded-3xl bg-indigo-600/5 p-4">
                        <a href="{{ route('professional-services.create') }}" class="inline-flex items-center gap-3 rounded-2xl bg-indigo-600 px-5 py-3 text-white font-semibold shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-700">
                            <i class="fas fa-plus"></i>
                            Create service
                        </a>
                    </div>
                </div>

                <div class="mt-10 grid gap-4 lg:grid-cols-[1fr_auto] items-center">
                    <div class="rounded-3xl bg-gray-50 dark:bg-dark-800 p-6 shadow-sm border border-gray-100 dark:border-dark-700">
                        <form action="{{ route('professional-services.index') }}" method="GET" class="relative">
                            <label for="marketplace-search" class="sr-only">Search services</label>
                            <input id="marketplace-search" name="search" value="{{ request('search') }}" placeholder="Search services, skills or expertise"
                                class="w-full rounded-3xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-900 px-5 py-4 pr-14 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:border-indigo-400 dark:focus:ring-indigo-900/30"
                            />
                            <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 inline-flex items-center justify-center rounded-full bg-indigo-600 p-3 text-white shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    <div class="rounded-3xl bg-white dark:bg-dark-900 p-6 shadow-sm border border-gray-100 dark:border-dark-700">
                        <p class="text-sm uppercase tracking-[0.28em] text-gray-500 dark:text-gray-400">Top categories</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($categories->take(6) as $category)
                                <a href="{{ route('professional-services.index', ['category' => $category->slug]) }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-dark-700 bg-gray-50 dark:bg-dark-800 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition">{{ $category->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mt-6 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-3xl bg-gray-50 dark:bg-dark-800 p-6 shadow-sm border border-gray-100 dark:border-dark-700">
                        <p class="text-sm uppercase tracking-[0.28em] text-gray-500 dark:text-gray-400">Services live</p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $services->total() }}</p>
                    </div>
                    <div class="rounded-3xl bg-gray-50 dark:bg-dark-800 p-6 shadow-sm border border-gray-100 dark:border-dark-700">
                        <p class="text-sm uppercase tracking-[0.28em] text-gray-500 dark:text-gray-400">Categories</p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $categories->count() }}</p>
                    </div>
                    <div class="rounded-3xl bg-gray-50 dark:bg-dark-800 p-6 shadow-sm border border-gray-100 dark:border-dark-700">
                        <p class="text-sm uppercase tracking-[0.28em] text-gray-500 dark:text-gray-400">Trusted sellers</p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900 dark:text-gray-100">Top rated talent</p>
                    </div>
                </div>

                <div class="mt-8">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">A curated marketplace for talented professionals</p>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 max-w-2xl">Search by category, skill, or delivery speed and connect with trusted service providers who can deliver your project on time.</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-2 mb-6">
            <div class="flex flex-wrap gap-2">
               
                <a href="{{ route('professional-services.index') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-th-large mr-2"></i> Browse
                </a>
                <a href="{{ route('professional-services.orders.index') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-shopping-cart mr-2"></i> My Orders
                </a>
               
                <a href="{{ route('professional-services.my-services') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-briefcase mr-2"></i> My Services
                </a>
                <a href="{{ route('professional-services.sales.index') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-chart-line mr-2"></i> Sales
                </a>
                <a href="{{ route('professional-services.edit-profile') }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-user-cog mr-2"></i> Profile
                </a>
               
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="grid gap-8 xl:grid-cols-[320px_minmax(0,1fr)] mb-8">
            <aside class="space-y-6 xl:sticky xl:top-24 xl:self-start">
                <div class="bg-white dark:bg-dark-900 rounded-3xl border border-gray-100 dark:border-dark-700 shadow-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Search services</h2>
                    <form action="{{ route('professional-services.index') }}" method="GET" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search services or skills" 
                                class="w-full rounded-2xl border border-gray-200 dark:border-dark-600 bg-gray-50 dark:bg-dark-800 px-4 py-3 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                            <select name="category" class="w-full rounded-2xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 px-4 py-3 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">All categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full inline-flex justify-center items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-3 text-white font-semibold hover:opacity-90 transition">Search</button>
                    </form>
                </div>

                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-dark-900 dark:to-dark-800 rounded-3xl p-6 border border-indigo-100 dark:border-purple-800 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Why hire here?</h3>
                    <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex gap-3"><span class="mt-1 text-indigo-600"><i class="fas fa-check-circle"></i></span> Verified sellers</li>
                        <li class="flex gap-3"><span class="mt-1 text-indigo-600"><i class="fas fa-shield-alt"></i></span> Secure payments</li>
                        <li class="flex gap-3"><span class="mt-1 text-indigo-600"><i class="fas fa-bolt"></i></span> Fast delivery options</li>
                    </ul>
                </div>
            </aside>

            <div class="space-y-6">
                @php
                    $featuredServices = collect($services->items())->where('is_featured', true)->take(3);
                @endphp

                @if($featuredServices->isNotEmpty())
                    <section class="space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-indigo-600">Featured services</p>
                                <h2 class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">Top curated gigs</h2>
                            </div>
                            <a href="{{ route('professional-services.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-300 hover:underline">Browse all services</a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @foreach($featuredServices as $service)
                                <a href="{{ route('professional-services.show', $service->id) }}" class="group block bg-white dark:bg-dark-900 rounded-3xl border border-gray-100 dark:border-dark-700 shadow-lg hover:shadow-xl transition-all p-6 h-full">
                                    <div class="flex items-start justify-between mb-4 gap-3">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.3em] font-semibold text-indigo-600">{{ $service->category->name ?? 'General' }}</p>
                                            <h3 class="mt-3 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $service->title }}</h3>
                                        </div>
                                        @if($service->is_featured)
                                            <span class="rounded-full bg-yellow-100 text-yellow-800 px-3 py-1 text-xs font-semibold">Featured</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-4 mb-5">{{ $service->description }}</p>
                                    <div class="grid gap-3 text-sm text-gray-500 dark:text-gray-400 mb-6 sm:grid-cols-2">
                                        <span class="inline-flex items-center gap-2"><i class="fas fa-clock"></i> {{ $service->delivery_days }}d delivery</span>
                                        <span class="inline-flex items-center gap-2"><i class="fas fa-redo"></i> {{ $service->revisions_included }} revisions</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 text-sm text-gray-600 dark:text-gray-400">
                                        <span class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₦{{ number_format($service->price) }}</span>
                                        <span class="inline-flex items-center gap-2"><i class="fas fa-star text-yellow-400"></i> {{ number_format($service->seller->seller_rating ?? 0, 1) }} ({{ $service->seller->seller_rating_count ?? 0 }})</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-gray-500 dark:text-gray-400">Browse results</p>
                            <h2 class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">Service listings</h2>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Showing {{ $services->count() }} of {{ $services->total() }} services</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 dark:bg-dark-800 px-4 py-2 text-sm text-gray-600 dark:text-gray-300">Sort: Featured first</span>
                        </div>
                    </div>

                    @if($services->isEmpty())
                        <div class="text-center rounded-3xl bg-white dark:bg-dark-900 border border-gray-100 dark:border-dark-700 shadow-lg p-14">
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">No services found</p>
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Try a broader search or explore a different category.</p>
                            <a href="{{ route('professional-services.create') }}" class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3 text-white font-semibold hover:opacity-90 transition">Create Service</a>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($services as $service)
                                <a href="{{ route('professional-services.show', $service->id) }}" class="group block bg-white dark:bg-dark-900 rounded-3xl border border-gray-100 dark:border-dark-700 shadow-lg hover:shadow-xl transition-all p-6">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="space-y-4">
                                            <div class="flex flex-wrap items-center gap-3">
                                                <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 text-indigo-700 px-3 py-1 text-xs font-semibold">{{ $service->category->name ?? 'General' }}</span>
                                                @if($service->is_featured)
                                                    <span class="inline-flex items-center gap-2 rounded-full bg-yellow-100 text-yellow-800 px-3 py-1 text-xs font-semibold">Featured</span>
                                                @endif
                                            </div>
                                            <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $service->title }}</h3>
                                            <p class="max-w-3xl text-sm text-gray-600 dark:text-gray-400 line-clamp-3">{{ $service->description }}</p>
                                            <div class="flex flex-wrap gap-3 text-sm text-gray-500 dark:text-gray-400">
                                                <span class="inline-flex items-center gap-2"><i class="fas fa-clock"></i> {{ $service->delivery_days }}d delivery</span>
                                                <span class="inline-flex items-center gap-2"><i class="fas fa-redo"></i> {{ $service->revisions_included }} revisions</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-4 text-right shrink-0 w-full sm:w-auto">
                                            <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">₦{{ number_format($service->price) }}</div>
                                            <div class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 justify-end">
                                                <span class="inline-flex items-center gap-2"><i class="fas fa-star text-yellow-400"></i> {{ number_format($service->seller->seller_rating ?? 0, 1) }}</span>
                                                <span>({{ $service->seller->seller_rating_count ?? 0 }} reviews)</span>
                                            </div>
                                            <div class="inline-flex items-center gap-2 rounded-full bg-gray-100 dark:bg-dark-800 px-3 py-2 text-xs text-gray-600 dark:text-gray-300 justify-end">
                                                <i class="fas fa-user"></i> {{ $service->seller->name ?? 'Seller' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="inline-flex items-center gap-2"><i class="fas fa-bolt"></i> Fast response</span>
                                            <span class="inline-flex items-center gap-2"><i class="fas fa-award"></i> Trusted professional</span>
                                        </div>
                                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600">
                                            <i class="fas fa-arrow-right"></i> View service
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="mt-8">
                            <x-pagination :paginator="$services" :showPerPage="true" />
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
