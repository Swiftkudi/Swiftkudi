@extends('layouts.app')

@section('title', 'Find Freelancers | SwiftKudi')
@section('meta_description', 'Browse available freelancers on SwiftKudi by skill, rating, completed work and hourly rate.')
@section('canonical', route('freelancers.index'))
@section('robots', request()->query() ? 'noindex,follow' : 'index,follow')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="marketplace-page-header">
            <div>
                <p class="marketplace-eyebrow">Find talent</p>
                <h1 class="marketplace-title mt-2">Hire skilled professionals</h1>
                <p class="marketplace-subtitle">Compare real freelancer profiles by specialty, skills, reputation, completed work, rate and availability.</p>
            </div>
            @auth<a href="{{ route('jobs.create') }}" class="marketplace-btn-primary"><i class="fas fa-plus"></i>Post a job</a>@endauth
        </div>

        <form action="{{ route('freelancers.index') }}" method="GET" class="marketplace-search-shell mb-5">
            <label class="marketplace-search-input-wrap" for="talent-search"><i class="fas fa-search text-gray-500"></i><input id="talent-search" type="search" name="search" value="{{ request('search') }}" maxlength="120" placeholder="Search by name, specialty or skill"></label>
            @foreach(['skill','min_rating','min_rate','max_rate'] as $field)@if(request()->filled($field))<input type="hidden" name="{{ $field }}" value="{{ request($field) }}">@endif @endforeach
            <select name="sort" class="marketplace-search-scope" aria-label="Sort professionals">
                <option value="recommended" @selected(request('sort','recommended') === 'recommended')>Recommended</option>
                <option value="rating" @selected(request('sort') === 'rating')>Highest rated</option>
                <option value="completed" @selected(request('sort') === 'completed')>Most completed</option>
                <option value="rate_low" @selected(request('sort') === 'rate_low')>Rate: low to high</option>
                <option value="rate_high" @selected(request('sort') === 'rate_high')>Rate: high to low</option>
            </select>
            <button class="marketplace-btn-primary" type="submit">Search</button>
        </form>

        <details class="marketplace-mobile-filter mb-5 lg:hidden" {{ request()->hasAny(['skill','min_rating','min_rate','max_rate']) ? 'open' : '' }}>
            <summary><span><i class="fas fa-sliders mr-2"></i>Filters</span><span class="text-xs text-gray-500">Refine talent</span></summary>
            <form method="GET" action="{{ route('freelancers.index') }}" class="space-y-4 p-4">
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                @include('professional-services.partials.talent-filters', ['allSkills' => $allSkills, 'mobile' => true])
            </form>
        </details>

        <div class="grid gap-7 lg:grid-cols-[270px_minmax(0,1fr)]">
            <aside class="hidden lg:block lg:sticky lg:top-24 lg:self-start" aria-label="Talent filters">
                <form method="GET" action="{{ route('freelancers.index') }}" class="marketplace-panel space-y-5 p-5">
                    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                    @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                    @include('professional-services.partials.talent-filters', ['allSkills' => $allSkills, 'mobile' => false])
                </form>
            </aside>

            <section aria-label="Freelancer results">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-500"><strong class="text-gray-100">{{ number_format($providers->total()) }}</strong> available professional{{ $providers->total() === 1 ? '' : 's' }}</p>
                    @if(request()->hasAny(['search','skill','min_rating','min_rate','max_rate']))<a href="{{ route('freelancers.index') }}" class="text-sm font-semibold text-indigo-300">Clear all filters</a>@endif
                </div>

                @if($providers->isEmpty())
                    <div class="marketplace-empty-state"><span class="marketplace-empty-icon"><i class="fas fa-user-group"></i></span><h2 class="mt-4 text-lg font-semibold text-white">No freelancers match those filters</h2><p class="mt-2 max-w-md text-sm leading-6 text-gray-500">Try a broader skill, a lower minimum rating or a wider rate range.</p><a href="{{ route('freelancers.index') }}" class="marketplace-btn-secondary mt-5">Reset search</a></div>
                @else
                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        @foreach($providers as $profile)
                            @php($profileUser = $profile->user)
                            <article class="marketplace-talent-card">
                                <div class="flex items-start gap-4">
                                    <span class="marketplace-avatar marketplace-avatar-lg">{{ strtoupper(substr($profileUser->name ?? 'U', 0, 2)) }}</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2"><h2 class="truncate text-lg font-semibold text-white">{{ $profileUser->name ?? 'Professional' }}</h2>@if($profileUser && $profileUser->marketplace_seller_verified)<span class="marketplace-status marketplace-status-info"><i class="fas fa-circle-check mr-1"></i>Verified</span>@endif @if($profile->is_available)<span class="marketplace-status marketplace-status-success">Available</span>@endif</div>
                                        <p class="mt-1 line-clamp-1 text-sm text-gray-400">{{ $profile->professional_title ?: 'Independent professional' }}</p>
                                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                                            @if($profile->total_reviews > 0)<span><i class="fas fa-star text-amber-400"></i> <strong class="text-gray-300">{{ number_format((float)$profile->average_rating, 1) }}</strong> ({{ $profile->total_reviews }} review{{ $profile->total_reviews === 1 ? '' : 's' }})</span>@else<span>No reviews yet</span>@endif
                                            <span>{{ number_format($profile->total_orders_completed) }} completed</span>
                                        </div>
                                    </div>
                                </div>

                                @if($profile->bio)<p class="mt-4 line-clamp-3 text-sm leading-6 text-gray-400">{{ $profile->bio }}</p>@endif
                                @if(!empty($profile->skills))<div class="mt-4 flex flex-wrap gap-2">@foreach(array_slice($profile->skills, 0, 6) as $skill)<span class="marketplace-pill">{{ $skill }}</span>@endforeach</div>@endif

                                <div class="mt-5 grid gap-4 border-t border-dark-700 pt-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm text-gray-500">
                                        <span>@if($profile->hourly_rate)<strong class="text-white">₦{{ number_format((float)$profile->hourly_rate) }}/hr</strong>@else Rate on request @endif</span>
                                        @if($profile->availability_note)<span class="line-clamp-1">{{ $profile->availability_note }}</span>@endif
                                    </div>
                                    <a href="{{ $profile->slug ? route('freelancers.show', $profile->slug) : route('professional-services.provider-profile', $profile->user_id) }}" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200">View profile <i class="fas fa-arrow-right ml-1 text-xs"></i></a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if($providers->hasPages())<div class="mt-7">{{ $providers->links() }}</div>@endif
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
