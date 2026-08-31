@extends('layouts.app')

@section('title', $term ? 'Search results for ' . $term . ' | SwiftKudi' : 'Search SwiftKudi Marketplace')
@section('meta_description', 'Search SwiftKudi jobs, freelancers and professional services from one marketplace search.')
@section('canonical', route('marketplace.search'))
@section('robots', 'noindex,follow')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="marketplace-page-header">
            <div>
                <span class="marketplace-eyebrow">Marketplace search</span>
                <h1 class="marketplace-title mt-2">Search jobs, talent and services</h1>
                <p class="marketplace-subtitle">One search across the parts of SwiftKudi that already support marketplace discovery.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('marketplace.search') }}" class="marketplace-search-shell mb-6">
            <label class="marketplace-search-input-wrap" for="marketplace-search-page-input">
                <i class="fas fa-search text-gray-500"></i>
                <input id="marketplace-search-page-input" type="search" name="q" value="{{ $term }}" maxlength="120" placeholder="Search by job, skill, service or professional" autocomplete="off">
            </label>
            <select name="scope" class="marketplace-search-scope" aria-label="Search type">
                <option value="all" @selected($scope === 'all')>Everything</option>
                <option value="jobs" @selected($scope === 'jobs')>Jobs</option>
                <option value="talent" @selected($scope === 'talent')>Talent</option>
                <option value="services" @selected($scope === 'services')>Services</option>
            </select>
            <button class="marketplace-btn-primary" type="submit">Search</button>
        </form>

        @if($term === '')
            <div class="marketplace-empty-state">
                <span class="marketplace-empty-icon"><i class="fas fa-search"></i></span>
                <h2 class="mt-4 text-lg font-semibold text-white">Start with what you need</h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-gray-500">Search for a job title, skill, freelancer specialty or defined professional service.</p>
                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    <a href="{{ route('jobs.index') }}" class="marketplace-btn-secondary">Find work</a>
                    <a href="{{ route('freelancers.index') }}" class="marketplace-btn-secondary">Find talent</a>
                    <a href="{{ route('professional-services.index') }}" class="marketplace-btn-secondary">Browse services</a>
                </div>
            </div>
        @else
            @php($totalMatches = array_sum($counts))
            <div class="mb-6 flex flex-col gap-3 border-b border-dark-700 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm text-gray-500">Search results for</p>
                    <h2 class="mt-1 text-2xl font-bold text-white">“{{ $term }}”</h2>
                </div>
                <p class="text-sm text-gray-500">{{ number_format($totalMatches) }} matching marketplace {{ \Illuminate\Support\Str::plural('item', $totalMatches) }}</p>
            </div>

            <nav class="marketplace-result-tabs mb-7" aria-label="Search result type">
                @foreach(['all' => ['All', $totalMatches], 'jobs' => ['Jobs', $counts['jobs']], 'talent' => ['Talent', $counts['talent']], 'services' => ['Services', $counts['services']]] as $value => [$label, $count])
                    <a href="{{ route('marketplace.search', ['q' => $term, 'scope' => $value]) }}" class="marketplace-result-tab {{ $scope === $value ? 'marketplace-result-tab-active' : '' }}">{{ $label }} <span>{{ number_format($count) }}</span></a>
                @endforeach
            </nav>

            @if(($scope === 'all' || $scope === 'jobs'))
                <section class="mb-10" aria-labelledby="search-jobs-heading">
                    <div class="mb-4 flex items-end justify-between gap-4">
                        <div><span class="marketplace-eyebrow">Find work</span><h2 id="search-jobs-heading" class="marketplace-section-title mt-2">Jobs <span class="font-normal text-gray-500">({{ number_format($counts['jobs']) }})</span></h2></div>
                        @if($counts['jobs'] > $jobs->count())<a href="{{ route('jobs.index', ['search' => $term]) }}" class="text-sm font-semibold text-indigo-300">See all jobs</a>@endif
                    </div>
                    @if($jobs->isEmpty())
                        <div class="marketplace-card p-6 text-sm text-gray-500">No active jobs match this search.</div>
                    @else
                        <div class="space-y-3">
                            @foreach($jobs as $job)
                                <a href="{{ route('jobs.show', $job) }}" class="marketplace-card-hover block p-5 sm:p-6">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"><div><p class="text-xs text-gray-500">{{ $job->created_at->diffForHumans() }}@if($job->category) · {{ $job->category->name }}@endif</p><h3 class="mt-1 text-lg font-semibold text-white">{{ $job->title }}</h3></div><span class="font-semibold text-gray-200">{{ $job->budget_range }}</span></div>
                                    <p class="mt-3 line-clamp-2 text-sm leading-6 text-gray-400">{{ \Illuminate\Support\Str::limit(strip_tags($job->description), 190) }}</p>
                                    <div class="mt-4 flex flex-wrap gap-2"><span class="marketplace-pill">{{ $job->type_label }}</span><span class="marketplace-pill">{{ $job->level_label }}</span>@if($job->location)<span class="marketplace-pill">{{ $job->location }}</span>@endif</div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            @if(($scope === 'all' || $scope === 'talent'))
                <section class="mb-10" aria-labelledby="search-talent-heading">
                    <div class="mb-4 flex items-end justify-between gap-4">
                        <div><span class="marketplace-eyebrow">Find talent</span><h2 id="search-talent-heading" class="marketplace-section-title mt-2">Professionals <span class="font-normal text-gray-500">({{ number_format($counts['talent']) }})</span></h2></div>
                        @if($counts['talent'] > $talent->count())<a href="{{ route('freelancers.index', ['search' => $term]) }}" class="text-sm font-semibold text-indigo-300">See all talent</a>@endif
                    </div>
                    @if($talent->isEmpty())
                        <div class="marketplace-card p-6 text-sm text-gray-500">No available freelancer profiles match this search.</div>
                    @else
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @foreach($talent as $profile)
                                @php($profileUser = $profile->user)
                                <a href="{{ $profile->slug ? route('freelancers.show', $profile->slug) : route('professional-services.provider-profile', $profile->user_id) }}" class="marketplace-card-hover block p-5">
                                    <div class="flex items-start gap-3"><span class="marketplace-avatar">{{ strtoupper(substr($profileUser->name ?? 'U', 0, 2)) }}</span><div class="min-w-0"><div class="flex items-center gap-2"><h3 class="truncate font-semibold text-white">{{ $profileUser->name ?? 'Professional' }}</h3>@if($profileUser && $profileUser->marketplace_seller_verified)<i class="fas fa-circle-check text-xs text-indigo-300" title="Verified marketplace seller" aria-label="Verified marketplace seller"></i>@endif</div><p class="mt-0.5 line-clamp-1 text-sm text-gray-400">{{ $profile->professional_title ?: 'Independent professional' }}</p></div></div>
                                    @if($profile->bio)<p class="mt-4 line-clamp-2 text-sm leading-6 text-gray-500">{{ $profile->bio }}</p>@endif
                                    <div class="mt-4 flex flex-wrap gap-2">@foreach(array_slice($profile->skills ?? [], 0, 4) as $skill)<span class="marketplace-pill">{{ $skill }}</span>@endforeach</div>
                                    <div class="mt-5 flex items-center justify-between border-t border-dark-700 pt-4 text-sm"><span class="text-gray-400">@if($profile->total_reviews > 0)<i class="fas fa-star text-amber-400"></i> {{ number_format((float)$profile->average_rating, 1) }} ({{ $profile->total_reviews }})@else New talent · no reviews yet @endif</span><span class="font-semibold text-white">{{ $profile->hourly_rate ? '₦'.number_format((float)$profile->hourly_rate).'/hr' : 'Rate on request' }}</span></div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            @if(($scope === 'all' || $scope === 'services'))
                <section class="mb-6" aria-labelledby="search-services-heading">
                    <div class="mb-4 flex items-end justify-between gap-4">
                        <div><span class="marketplace-eyebrow">Services</span><h2 id="search-services-heading" class="marketplace-section-title mt-2">Ready-to-buy services <span class="font-normal text-gray-500">({{ number_format($counts['services']) }})</span></h2></div>
                        @if($counts['services'] > $services->count())<a href="{{ route('professional-services.index', ['search' => $term]) }}" class="text-sm font-semibold text-indigo-300">See all services</a>@endif
                    </div>
                    @if($services->isEmpty())
                        <div class="marketplace-card p-6 text-sm text-gray-500">No active professional services match this search.</div>
                    @else
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @foreach($services as $service)
                                <a href="{{ route('professional-services.show', $service) }}" class="marketplace-card-hover block p-5">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-400">{{ optional($service->category)->name ?: 'Professional service' }}</p>
                                    <h3 class="mt-2 line-clamp-2 min-h-[48px] font-semibold text-white">{{ $service->title }}</h3>
                                    <p class="mt-3 line-clamp-2 text-sm leading-6 text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags($service->description), 120) }}</p>
                                    <div class="mt-5 flex items-center justify-between border-t border-dark-700 pt-4"><div class="text-xs text-gray-500">{{ $service->delivery_days }} day delivery</div><div class="text-right"><span class="text-xs text-gray-500">From</span><p class="font-bold text-white">₦{{ number_format((float)$service->price) }}</p></div></div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            @if($totalMatches === 0)
                <div class="marketplace-empty-state">
                    <span class="marketplace-empty-icon"><i class="fas fa-search"></i></span>
                    <h2 class="mt-4 text-lg font-semibold text-white">No marketplace results</h2>
                    <p class="mt-2 max-w-lg text-sm leading-6 text-gray-500">Try a shorter or broader search term. SwiftKudi only shows real jobs, profiles and services that exist in the marketplace.</p>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
