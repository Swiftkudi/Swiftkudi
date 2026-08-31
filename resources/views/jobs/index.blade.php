@extends('layouts.app')

@section('title', request('search') ? 'Freelance jobs matching ' . request('search') . ' | SwiftKudi' : 'Find Freelance Jobs | SwiftKudi')
@section('meta_description', 'Browse active freelance jobs on SwiftKudi. Filter opportunities by category, experience level, work type, budget and location.')
@section('canonical', route('jobs.index'))
@section('robots', request()->query() ? 'noindex,follow' : 'index,follow')
@section('og_title', 'Find Freelance Jobs | SwiftKudi')
@section('og_description', 'Browse active freelance opportunities and submit proposals from your SwiftKudi account.')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="marketplace-page-header gap-5">
            <div>
                <p class="marketplace-eyebrow">Find work</p>
                <h1 class="marketplace-title mt-2">Freelance jobs that fit your skills</h1>
                <p class="marketplace-subtitle">Scan current opportunities quickly, filter real job data, save promising work and open a listing only when you need the full scope.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @auth
                    <a href="{{ route('jobs.applications') }}" class="marketplace-btn-secondary"><i class="fas fa-paper-plane"></i> My proposals</a>
                    <a href="{{ route('jobs.create') }}" class="marketplace-btn-primary"><i class="fas fa-plus"></i> Post a job</a>
                @else
                    <a href="{{ route('login') }}" class="marketplace-btn-secondary">Sign in</a>
                    <a href="{{ route('register') }}" class="marketplace-btn-primary">Create account</a>
                @endauth
            </div>
        </div>

        <form action="{{ route('jobs.index') }}" method="GET" class="marketplace-search-shell mb-5">
            <label class="marketplace-search-input-wrap" for="job-search">
                <i class="fas fa-search text-gray-500"></i>
                <input id="job-search" type="search" name="search" value="{{ request('search') }}" maxlength="120" placeholder="Search by title, category or keyword">
            </label>
            @foreach(['category','type','level','budget_min','budget_max','location','saved'] as $field)
                @if(request()->filled($field))<input type="hidden" name="{{ $field }}" value="{{ request($field) }}">@endif
            @endforeach
            <select name="sort" class="marketplace-search-scope" aria-label="Sort jobs">
                <option value="newest" @selected(request('sort','newest') === 'newest')>Newest</option>
                <option value="budget_high" @selected(request('sort') === 'budget_high')>Highest budget</option>
                <option value="budget_low" @selected(request('sort') === 'budget_low')>Lowest budget</option>
                <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
            </select>
            <button class="marketplace-btn-primary" type="submit">Search</button>
        </form>

        <details class="marketplace-mobile-filter mb-5 lg:hidden" {{ request()->hasAny(['category','type','level','budget_min','budget_max','location','saved']) ? 'open' : '' }}>
            <summary><span><i class="fas fa-sliders mr-2"></i>Filters</span><span class="text-xs text-gray-500">Refine results</span></summary>
            <form action="{{ route('jobs.index') }}" method="GET" class="space-y-4 p-4">
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                @include('jobs.partials.filters', ['categories' => $categories, 'mobile' => true])
            </form>
        </details>

        <div class="grid gap-7 lg:grid-cols-[270px_minmax(0,1fr)]">
            <aside class="hidden lg:block lg:sticky lg:top-24 lg:self-start" aria-label="Job filters">
                <form action="{{ route('jobs.index') }}" method="GET" class="marketplace-panel space-y-5 p-5">
                    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                    @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                    @include('jobs.partials.filters', ['categories' => $categories, 'mobile' => false])
                </form>
            </aside>

            <section aria-label="Job results">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-500"><strong class="text-gray-100">{{ number_format($jobs->total()) }}</strong> active {{ \Illuminate\Support\Str::plural('job', $jobs->total()) }}</p>
                    @if(request()->hasAny(['search','category','type','level','budget_min','budget_max','location','saved']))
                        <a href="{{ route('jobs.index') }}" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200">Clear all filters</a>
                    @endif
                </div>

                <div class="space-y-3">
                    @forelse($jobs as $job)
                        @php $saved = in_array($job->id, $savedJobIds, true); @endphp
                        <article class="marketplace-job-card">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                        <span>Posted {{ $job->created_at->diffForHumans() }}</span>
                                        @if($job->category)<span aria-hidden="true">•</span><span>{{ $job->category->name }}</span>@endif
                                        @if($job->location)<span aria-hidden="true">•</span><span>{{ $job->location }}</span>@endif
                                    </div>
                                    <h2 class="mt-2 text-xl font-semibold leading-7 text-white"><a href="{{ route('jobs.show', $job) }}" class="hover:text-indigo-300">{{ $job->title }}</a></h2>
                                </div>
                                @auth
                                    <form method="POST" action="{{ $saved ? route('jobs.unsave', $job) : route('jobs.save', $job) }}" class="flex-none">
                                        @csrf
                                        @if($saved) @method('DELETE') @endif
                                        <button type="submit" class="marketplace-save-button {{ $saved ? 'marketplace-save-button-active' : '' }}" aria-label="{{ $saved ? 'Remove saved job' : 'Save job' }}"><i class="{{ $saved ? 'fas' : 'far' }} fa-heart"></i></button>
                                    </form>
                                @endauth
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm font-medium text-gray-300">
                                <span>{{ $job->budget_range }}</span>
                                <span>{{ $job->type_label }}</span>
                                <span>{{ $job->level_label }}</span>
                                @if($job->duration)<span>{{ $job->duration }}</span>@endif
                            </div>

                            <p class="mt-4 line-clamp-3 text-sm leading-6 text-gray-400">{{ \Illuminate\Support\Str::limit(strip_tags($job->description), 300) }}</p>

                            @if(!empty($job->requirements))
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach(array_slice($job->requirements, 0, 6) as $requirement)
                                        @if(is_string($requirement) && trim($requirement) !== '')<span class="marketplace-pill">{{ \Illuminate\Support\Str::limit($requirement, 36) }}</span>@endif
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-5 grid gap-3 border-t border-dark-700 pt-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                                <div>
                                    <p class="text-sm font-semibold text-gray-200">{{ optional($job->user)->name ?: 'SwiftKudi client' }}</p>
                                    <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                                        @if(optional($job->user)->email_verified_at)<span><i class="fas fa-circle-check mr-1 text-indigo-400"></i>Email verified</span>@endif
                                        <span>{{ number_format($job->applications_count ?? 0) }} proposal{{ ($job->applications_count ?? 0) === 1 ? '' : 's' }}</span>
                                        <span>{{ max(0, $job->positions_remaining) }} position{{ $job->positions_remaining === 1 ? '' : 's' }} left</span>
                                    </div>
                                </div>
                                <a href="{{ route('jobs.show', $job) }}" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200">View job <i class="fas fa-arrow-right ml-1 text-xs"></i></a>
                            </div>
                        </article>
                    @empty
                        <div class="marketplace-empty-state">
                            <span class="marketplace-empty-icon"><i class="fas fa-search"></i></span>
                            <h2 class="mt-4 text-lg font-semibold text-white">No jobs match these filters</h2>
                            <p class="mt-2 max-w-md text-sm leading-6 text-gray-500">Try a broader keyword, increase the budget range or remove a filter.</p>
                            <a href="{{ route('jobs.index') }}" class="marketplace-btn-secondary mt-5">Reset search</a>
                        </div>
                    @endforelse
                </div>

                @if($jobs->hasPages())<div class="mt-7">{{ $jobs->links() }}</div>@endif
            </section>
        </div>
    </div>
</div>
@endsection
