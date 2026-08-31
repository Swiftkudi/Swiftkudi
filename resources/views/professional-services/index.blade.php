@extends('layouts.app')

@section('title', 'Professional Services | ' . config('app.name', 'SwiftKudi'))
@section('meta_description', 'Browse professional services on ' . config('app.name', 'SwiftKudi') . ' and compare scope, delivery time, pricing and provider reputation.')

@section('content')
<div class="marketplace-page py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <section class="marketplace-panel overflow-hidden p-6 sm:p-8 lg:p-10">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div class="max-w-3xl">
                    <span class="marketplace-eyebrow">Professional services</span>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-5xl">Get project-ready services from independent professionals.</h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300">Compare real service listings by scope, delivery time, price and provider history. Choose the offer that fits your project and keep the work connected to SwiftKudi messaging and marketplace payments.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('freelancers.index') }}" class="marketplace-button-secondary">Find talent</a>
                    @auth
                        <a href="{{ route('professional-services.create') }}" class="marketplace-button-primary"><i class="fas fa-plus"></i> Create service</a>
                    @else
                        <a href="{{ route('login') }}" class="marketplace-button-primary">Sign in to sell</a>
                    @endauth
                </div>
            </div>

            <form action="{{ route('professional-services.index') }}" method="GET" class="mt-8 grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]">
                <div>
                    <label for="service-search" class="sr-only">Search professional services</label>
                    <input id="service-search" class="marketplace-input" type="search" name="search" value="{{ request('search') }}" maxlength="120" placeholder="Search services or skills">
                </div>
                <div>
                    <label for="service-category" class="sr-only">Category</label>
                    <select id="service-category" name="category" class="marketplace-input">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="marketplace-button-primary justify-center" type="submit"><i class="fas fa-search"></i> Search</button>
            </form>

            @if($categories->isNotEmpty())
                <div class="mt-5 flex flex-wrap gap-2" aria-label="Popular service categories">
                    <a href="{{ route('professional-services.index') }}" class="marketplace-chip {{ request('category') ? '' : 'marketplace-chip-active' }}">All</a>
                    @foreach($categories->take(8) as $category)
                        <a href="{{ route('professional-services.index', ['category' => $category->slug]) }}" class="marketplace-chip {{ request('category') === $category->slug ? 'marketplace-chip-active' : '' }}">{{ $category->name }}</a>
                    @endforeach
                </div>
            @endif
        </section>

        @auth
            <nav class="mt-5 flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 dark:border-slate-800 dark:bg-slate-950" aria-label="Services workspace">
                <a href="{{ route('professional-services.index') }}" class="marketplace-subnav-active">Browse</a>
                <a href="{{ route('professional-services.orders.index') }}" class="marketplace-subnav">My orders</a>
                <a href="{{ route('professional-services.my-services') }}" class="marketplace-subnav">My services</a>
                <a href="{{ route('professional-services.sales.index') }}" class="marketplace-subnav">Sales</a>
                <a href="{{ route('professional-services.edit-profile') }}" class="marketplace-subnav">Freelancer profile</a>
            </nav>
        @endauth

        <details class="marketplace-mobile-filter mt-6 lg:hidden" {{ request()->hasAny(['category','min_price','max_price','delivery_days']) ? 'open' : '' }}>
            <summary><span><i class="fas fa-sliders mr-2"></i>Filters</span><span class="text-xs text-gray-500">Refine services</span></summary>
            <form action="{{ route('professional-services.index') }}" method="GET" class="space-y-4 p-4">
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                @include('professional-services.partials.service-filters', ['categories' => $categories, 'mobile' => true])
            </form>
        </details>

        <div class="mt-8 grid gap-8 lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="hidden lg:block lg:sticky lg:top-24 lg:self-start">
                <form action="{{ route('professional-services.index') }}" method="GET" class="marketplace-panel space-y-5 p-5">
                    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                    @include('professional-services.partials.service-filters', ['categories' => $categories, 'mobile' => false])
                </form>
            </aside>

            <section>
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <span class="marketplace-eyebrow">Marketplace</span>
                        <h2 class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">{{ number_format($services->total()) }} service{{ $services->total() === 1 ? '' : 's' }}</h2>
                    </div>
                    @if(request()->hasAny(['search','category','min_price','max_price','delivery_days']))
                        <a href="{{ route('professional-services.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Clear all filters</a>
                    @endif
                </div>

                @forelse($services as $service)
                    <article class="marketplace-panel mb-4 p-5 sm:p-6">
                        <a href="{{ route('professional-services.show', $service) }}" class="group block">
                            <div class="flex flex-col gap-5 sm:flex-row sm:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                                        <span class="marketplace-chip">{{ $service->category->name ?? 'General' }}</span>
                                        @if($service->is_featured)
                                            <span class="marketplace-chip marketplace-chip-active">Featured placement</span>
                                        @endif
                                    </div>
                                    <h3 class="mt-4 text-xl font-bold text-slate-950 transition group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">{{ $service->title }}</h3>
                                    <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $service->description }}</p>
                                    <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-500 dark:text-slate-400">
                                        <span><i class="far fa-clock mr-1.5"></i>{{ $service->delivery_days }} day{{ $service->delivery_days == 1 ? '' : 's' }} delivery</span>
                                        <span><i class="fas fa-rotate-left mr-1.5"></i>{{ $service->revisions_included }} revision{{ $service->revisions_included == 1 ? '' : 's' }}</span>
                                    </div>
                                </div>
                                <div class="shrink-0 sm:w-52 sm:text-right">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Starting at</p>
                                    <p class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">₦{{ number_format((float) $service->price, 0) }}</p>
                                    <div class="mt-4 text-sm text-slate-600 dark:text-slate-300">
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ $service->seller->name ?? 'Service provider' }} @if(optional($service->seller)->marketplace_seller_verified)<i class="fas fa-circle-check ml-1 text-xs text-indigo-500" title="Verified marketplace seller" aria-label="Verified marketplace seller"></i>@endif</p>
                                        @if(($service->seller->seller_rating_count ?? 0) > 0)
                                            <p class="mt-1"><i class="fas fa-star text-amber-500"></i> {{ number_format((float) $service->seller->seller_rating, 1) }} <span class="text-slate-400">({{ $service->seller->seller_rating_count }} reviews)</span></p>
                                        @else
                                            <p class="mt-1 text-slate-400">No reviews yet</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>
                @empty
                    <div class="marketplace-empty-state">
                        <div class="marketplace-empty-icon"><i class="fas fa-magnifying-glass"></i></div>
                        <h3 class="mt-4 text-xl font-bold text-slate-950 dark:text-white">No matching services</h3>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Try removing a filter or searching with a broader term.</p>
                        <a href="{{ route('professional-services.index') }}" class="marketplace-button-primary mt-5">Browse all services</a>
                    </div>
                @endforelse

                @if($services->hasPages())
                    <div class="mt-7">
                        {{ $services->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
