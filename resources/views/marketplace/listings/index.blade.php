@extends('layouts.app')

@section('title', 'Campus-Marketplace - Browse Listings')

@section('content')
@php
    $activeSort = request('sort', 'newest');
@endphp

<div class="min-h-screen bg-[#f7f7f7] dark:bg-dark-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-6">

        {{-- Compact top bar --}}
        <section class="rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-sm p-4 sm:p-5 mb-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-orange-600">Student marketplace</p>
                    <h1 class="mt-2 text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                        Campus deals for students
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Buy and sell books, gadgets, fashion, and hostel essentials inside your campus community.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @auth
                        <a href="{{ route('marketplace.listings.create') }}"
                           class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white hover:bg-orange-600 transition">
                            <i class="fas fa-plus mr-2"></i>Sell
                        </a>
                    @endauth
                    <a href="{{ route('marketplace.listings.index') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 px-5 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:border-orange-300 hover:text-orange-700 transition">
                        Browse all
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('marketplace.listings.search') }}" class="mt-4 grid gap-3 lg:grid-cols-[1fr_auto]">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search textbooks, phones, clothes, calculators..."
                        class="w-full rounded-2xl border border-gray-200 dark:border-dark-600 bg-gray-50 dark:bg-dark-800 pl-11 pr-4 py-3.5 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20"
                    >
                </div>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-6 py-3.5 text-sm font-semibold text-white hover:bg-orange-600 transition">
                    Search
                </button>
            </form>

            <div class="mt-4 flex gap-2 overflow-x-auto pb-1">
                @foreach($categories->take(10) as $cat)
                    <a href="{{ route('marketplace.listings.category', $cat->slug) }}"
                       class="min-w-max rounded-full border border-gray-200 dark:border-dark-700 bg-gray-50 dark:bg-dark-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:border-orange-300 hover:text-orange-700 transition">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_290px]">
            {{-- Main listings --}}
            <main class="space-y-4 order-2 xl:order-1">
                <div class="rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-sm p-4 sm:p-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100">Latest student listings</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Showing {{ $listings->count() }} of {{ $listings->total() }} listings
                            </p>
                        </div>

                        <form method="GET" action="{{ route('marketplace.listings.search') }}" class="flex items-center gap-2">
                            @foreach(request()->except('sort', 'page') as $key => $value)
                                @if(is_array($value))
                                    @foreach($value as $v)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <select name="sort"
                                    onchange="this.form.submit()"
                                    class="rounded-2xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20">
                                <option value="newest" {{ $activeSort === 'newest' ? 'selected' : '' }}>Newest</option>
                                <option value="price_low" {{ $activeSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ $activeSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="popular" {{ $activeSort === 'popular' ? 'selected' : '' }}>Most Popular</option>
                                <option value="featured" {{ $activeSort === 'featured' ? 'selected' : '' }}>Featured</option>
                            </select>
                        </form>
                    </div>
                </div>

                @if($listings->isNotEmpty())
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($listings as $listing)
                            <a href="{{ route('marketplace.listings.show', $listing->id) }}"
                               class="group block overflow-hidden rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                                <div class="relative aspect-[4/3] overflow-hidden bg-gray-200 dark:bg-dark-700">
                                    @if($listing->images && count($listing->images) > 0)
                                        <img src="{{ asset('storage/' . $listing->images[0]) }}" alt="{{ $listing->title }}"
                                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                    @else
                                        <div class="flex h-full items-center justify-center text-gray-400">
                                            <i class="fas fa-image text-4xl"></i>
                                        </div>
                                    @endif

                                    @if($listing->is_featured)
                                        <span class="absolute left-3 top-3 inline-flex items-center rounded-full bg-orange-500 px-3 py-1 text-xs font-semibold text-white shadow">
                                            Featured
                                        </span>
                                    @endif

                                    <span class="absolute right-3 top-3 inline-flex items-center rounded-full bg-black/60 px-3 py-1 text-xs font-semibold text-white backdrop-blur">
                                        {{ $listing->condition === 'like_new' ? 'Like New' : ucfirst($listing->condition ?? 'used') }}
                                    </span>
                                </div>

                                <div class="p-4 sm:p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="rounded-full bg-orange-500/10 px-3 py-1 text-xs font-semibold text-orange-700 dark:text-orange-300">
                                            {{ $listing->category?->name ?? 'Campus' }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-heart mr-1"></i>{{ $listing->favourites_count ?? 0 }}
                                        </span>
                                    </div>

                                    <h3 class="mt-3 text-lg font-semibold text-gray-900 dark:text-gray-100 line-clamp-2 group-hover:text-orange-600 transition">
                                        {{ $listing->title }}
                                    </h3>

                                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ $listing->description }}
                                    </p>

                                    <div class="mt-4 flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Price</p>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">₦{{ number_format($listing->price, 2) }}</p>
                                        </div>
                                        <div class="text-right text-sm text-gray-500 dark:text-gray-400">
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $listing->seller?->name ?? 'Seller' }}</p>
                                            <p>{{ $listing->created_at?->diffForHumans() }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-gray-600 dark:text-gray-300">
                                        <span class="rounded-full bg-gray-100 dark:bg-dark-800 px-3 py-1.5">
                                            <i class="fas fa-map-marker-alt mr-1 text-orange-500"></i>
                                            {{ $listing->campus_name ?? 'Campus pickup' }}
                                        </span>
                                        @if(!empty($listing->delivery_available))
                                            <span class="rounded-full bg-gray-100 dark:bg-dark-800 px-3 py-1.5">
                                                <i class="fas fa-truck mr-1 text-orange-500"></i>
                                                Delivery
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $listings->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-sm p-10 sm:p-14 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-dark-800 text-2xl text-gray-400">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">No listings found</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Try a different keyword or clear the filters.
                        </p>
                        @auth
                            <a href="{{ route('marketplace.listings.create') }}"
                               class="mt-6 inline-flex items-center justify-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white hover:bg-orange-600 transition">
                                Create listing
                            </a>
                        @endauth
                    </div>
                @endif
            </main>

            {{-- Small sidebar only --}}
            <aside class="space-y-4 order-1 xl:order-2 xl:sticky xl:top-6 xl:self-start">
                <div class="rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Quick student searches</h3>
                    <div class="mt-4 grid gap-2">
                        <a href="{{ route('marketplace.listings.search', ['q' => 'textbooks']) }}" class="rounded-2xl bg-gray-50 dark:bg-dark-800 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:text-orange-700 transition">Textbooks and notes</a>
                        <a href="{{ route('marketplace.listings.search', ['q' => 'phone']) }}" class="rounded-2xl bg-gray-50 dark:bg-dark-800 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:text-orange-700 transition">Phones and accessories</a>
                        <a href="{{ route('marketplace.listings.search', ['q' => 'laptop']) }}" class="rounded-2xl bg-gray-50 dark:bg-dark-800 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:text-orange-700 transition">Laptops and gadgets</a>
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Why shop here?</h3>
                    <div class="mt-4 space-y-4 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex gap-3">
                            <i class="fas fa-shield-alt mt-1 text-orange-500"></i>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-gray-100">Safe campus trades</p>
                                <p>Buy and sell within your school community.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <i class="fas fa-truck mt-1 text-orange-500"></i>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-gray-100">Pickup or delivery</p>
                                <p>Meet nearby or request delivery.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection