@extends('layouts.app')

@section('title', 'Job Board - SwiftKudi')

@section('content')
@php
    $user = auth()->user();
    $accountType = $user->account_type ?? '';
    $activeSort = request('sort', 'newest');
@endphp

<div class="min-h-screen bg-[#f9fafb] dark:bg-dark-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

        {{-- Top bar / Upwork-like search strip --}}
        <div class="mb-6 rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-sm">
            <div class="flex flex-col gap-4 px-5 py-5 lg:px-6 lg:py-6">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-600">Find Work</p>
                        <h1 class="mt-1 text-2xl sm:text-3xl font-semibold text-gray-900 dark:text-gray-100">
                            Browse active jobs on SwiftKudi
                        </h1>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('jobs.applications') }}"
                           class="inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:border-cyan-300 hover:text-cyan-700 transition">
                            <i class="fas fa-paper-plane"></i>
                            My applications
                        </a>

                        <a href="{{ route('jobs.create') }}"
                           class="inline-flex items-center gap-2 rounded-full bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700 transition">
                            <i class="fas fa-plus"></i>
                            Post a job
                        </a>
                    </div>
                </div>

                <form action="{{ route('jobs.index') }}" method="GET" class="grid gap-3 lg:grid-cols-[1fr_auto]">
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search for jobs"
                            class="w-full rounded-2xl border border-gray-200 dark:border-dark-600 bg-gray-50 dark:bg-dark-800 pl-11 pr-4 py-3.5 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                        >
                    </div>

                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-600 px-5 py-3.5 text-sm font-semibold text-white hover:bg-cyan-700 transition">
                        <i class="fas fa-arrow-right"></i>
                        Search
                    </button>
                </form>

                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="rounded-full bg-cyan-50 dark:bg-cyan-900/20 px-3 py-1.5 font-medium text-cyan-700 dark:text-cyan-300">
                        My Feed
                    </span>
                    <span class="rounded-full bg-gray-100 dark:bg-dark-800 px-3 py-1.5 text-gray-600 dark:text-gray-300">
                        Best Matches
                    </span>
                    <span class="rounded-full bg-gray-100 dark:bg-dark-800 px-3 py-1.5 text-gray-600 dark:text-gray-300">
                        Recent
                    </span>
                    <span class="rounded-full bg-gray-100 dark:bg-dark-800 px-3 py-1.5 text-gray-600 dark:text-gray-300">
                        Saved
                    </span>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[280px_minmax(0,1fr)]">
            {{-- Left rail / filters --}}
            <aside class="space-y-4 xl:sticky xl:top-6 xl:self-start">
                <div class="rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-sm p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">My Feed</h2>
                        <span class="rounded-full bg-cyan-50 dark:bg-cyan-900/20 px-3 py-1 text-xs font-semibold text-cyan-700 dark:text-cyan-300">
                            {{ $jobs->total() }} jobs
                        </span>
                    </div>

                    <div class="mt-4 space-y-2">
                        <a href="{{ route('jobs.index') }}"
                           class="flex items-center justify-between rounded-2xl px-3 py-2.5 text-sm {{ !request()->filled('category') && !request()->filled('type') && !request()->filled('level') && !request()->filled('search') ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/20 dark:text-cyan-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-800' }} transition">
                            <span>Best Matches</span>
                            <i class="fas fa-chevron-right text-xs opacity-60"></i>
                        </a>

                        <a href="{{ route('jobs.index', array_merge(request()->except(['page']), ['sort' => 'newest'])) }}"
                           class="flex items-center justify-between rounded-2xl px-3 py-2.5 text-sm {{ $activeSort === 'newest' ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/20 dark:text-cyan-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-800' }} transition">
                            <span>Newest</span>
                            <i class="fas fa-chevron-right text-xs opacity-60"></i>
                        </a>

                        <a href="{{ route('jobs.index', array_merge(request()->except(['page']), ['sort' => 'budget_high'])) }}"
                           class="flex items-center justify-between rounded-2xl px-3 py-2.5 text-sm {{ $activeSort === 'budget_high' ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/20 dark:text-cyan-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-800' }} transition">
                            <span>Budget High</span>
                            <i class="fas fa-chevron-right text-xs opacity-60"></i>
                        </a>
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-sm p-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Filters</h3>
                        <a href="{{ route('jobs.index') }}" class="text-sm font-medium text-cyan-600 hover:text-cyan-700">Reset</a>
                    </div>

                    <form action="{{ route('jobs.index') }}" method="GET" class="mt-4 space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <select name="category"
                                    class="w-full rounded-2xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 px-4 py-3 text-sm text-gray-900 dark:text-gray-100 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Job type</label>
                            <select name="type"
                                    class="w-full rounded-2xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 px-4 py-3 text-sm text-gray-900 dark:text-gray-100 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                                <option value="">All Types</option>
                                <option value="full-time" {{ request('type') === 'full-time' ? 'selected' : '' }}>Full Time</option>
                                <option value="part-time" {{ request('type') === 'part-time' ? 'selected' : '' }}>Part Time</option>
                                <option value="contract" {{ request('type') === 'contract' ? 'selected' : '' }}>Contract</option>
                                <option value="internship" {{ request('type') === 'internship' ? 'selected' : '' }}>Internship</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Experience level</label>
                            <select name="level"
                                    class="w-full rounded-2xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 px-4 py-3 text-sm text-gray-900 dark:text-gray-100 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                                <option value="">Any Experience</option>
                                <option value="entry" {{ request('level') === 'entry' ? 'selected' : '' }}>Entry Level</option>
                                <option value="intermediate" {{ request('level') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="expert" {{ request('level') === 'expert' ? 'selected' : '' }}>Expert</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Sort</label>
                            <select name="sort"
                                    class="w-full rounded-2xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 px-4 py-3 text-sm text-gray-900 dark:text-gray-100 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                                <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                <option value="budget_high" {{ request('sort') === 'budget_high' ? 'selected' : '' }}>Budget: High to Low</option>
                                <option value="budget_low" {{ request('sort') === 'budget_low' ? 'selected' : '' }}>Budget: Low to High</option>
                            </select>
                        </div>

                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white hover:bg-cyan-700 transition">
                            <i class="fas fa-filter"></i>
                            Apply filters
                        </button>
                    </form>
                </div>

                <div class="rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Popular searches</h3>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($categories->take(10) as $category)
                            <a href="{{ route('jobs.index', array_merge(request()->except(['page']), ['category' => $category->id])) }}"
                               class="rounded-full border border-gray-200 dark:border-dark-700 bg-gray-50 dark:bg-dark-800 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:border-cyan-300 hover:text-cyan-700 transition">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>

            {{-- Main feed --}}
            <main class="space-y-4">
                <div class="rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-sm p-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-600">My Feed</p>
                            <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">Best matches for you</h2>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Showing {{ $jobs->count() }} of {{ $jobs->total() }} jobs.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-gray-100 dark:bg-dark-800 px-3 py-2 text-sm text-gray-700 dark:text-gray-300">U.S. Only</span>
                            <span class="rounded-full bg-gray-100 dark:bg-dark-800 px-3 py-2 text-sm text-gray-700 dark:text-gray-300">Verified clients</span>
                            <span class="rounded-full bg-gray-100 dark:bg-dark-800 px-3 py-2 text-sm text-gray-700 dark:text-gray-300">Fast responses</span>
                        </div>
                    </div>
                </div>

                @if($jobs->count() > 0)
                    <div class="space-y-4">
                        @foreach($jobs as $job)
                            <article class="rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-sm hover:shadow-md transition overflow-hidden">
                                <div class="p-5 sm:p-6">
                                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                @if($job->category)
                                                    <span class="inline-flex items-center rounded-full bg-cyan-50 dark:bg-cyan-900/20 px-3 py-1 text-xs font-semibold text-cyan-700 dark:text-cyan-300">
                                                        {{ $job->category->name }}
                                                    </span>
                                                @endif

                                                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-dark-800 px-3 py-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                    {{ $job->type_label }}
                                                </span>

                                                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-dark-800 px-3 py-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                    {{ $job->level_label }}
                                                </span>
                                            </div>

                                            <a href="{{ route('jobs.show', $job) }}" class="mt-3 block">
                                                <h3 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-gray-100 hover:text-cyan-600 transition">
                                                    {{ $job->title }}
                                                </h3>
                                            </a>

                                            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-500 dark:text-gray-400">
                                                <span class="inline-flex items-center gap-2">
                                                    <i class="fas fa-building"></i>
                                                    {{ $job->user->name }}
                                                </span>

                                                @if($job->location)
                                                    <span class="inline-flex items-center gap-2">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        {{ $job->location }}
                                                    </span>
                                                @endif

                                                <span class="inline-flex items-center gap-2">
                                                    <i class="fas fa-clock"></i>
                                                    {{ $job->created_at->diffForHumans() }}
                                                </span>
                                            </div>

                                            <p class="mt-4 text-sm leading-6 text-gray-600 dark:text-gray-400 line-clamp-3">
                                                {{ $job->description }}
                                            </p>

                                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                                <span class="rounded-full bg-gray-50 dark:bg-dark-800 px-3 py-1.5 text-xs text-gray-600 dark:text-gray-300">
                                                    Payment verified
                                                </span>
                                                <span class="rounded-full bg-gray-50 dark:bg-dark-800 px-3 py-1.5 text-xs text-gray-600 dark:text-gray-300">
                                                    {{ $job->applications_count ?? 0 }} proposals
                                                </span>
                                            </div>
                                        </div>

                                        <div class="lg:w-[220px] lg:text-right">
                                            <div class="rounded-3xl bg-gray-50 dark:bg-dark-800 p-4 border border-gray-100 dark:border-dark-700">
                                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $job->budget_range }}
                                                </p>

                                                <div class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                                    <div class="flex items-center gap-2 lg:justify-end">
                                                        <i class="fas fa-calendar-day text-cyan-500"></i>
                                                        <span>{{ $job->expires_at->diffForHumans() }}</span>
                                                    </div>

                                                    <div class="flex items-center gap-2 lg:justify-end">
                                                        <i class="fas fa-users text-cyan-500"></i>
                                                        <span>{{ $job->applications_count ?? 0 }} applicants</span>
                                                    </div>

                                                    <div class="flex items-center gap-2 lg:justify-end">
                                                        <i class="fas fa-bolt text-cyan-500"></i>
                                                        <span>Open to interviews</span>
                                                    </div>
                                                </div>

                                                <a href="{{ route('jobs.show', $job) }}"
                                                   class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white hover:bg-cyan-700 transition">
                                                    View job
                                                    <i class="fas fa-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $jobs->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 shadow-sm p-10 sm:p-14 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-dark-800 text-2xl text-gray-400">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">No jobs matched your search</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Try clearing filters, searching different keywords, or posting a new job.
                        </p>
                        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                            <a href="{{ route('jobs.index') }}"
                               class="inline-flex items-center justify-center rounded-2xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 px-5 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:border-cyan-300 hover:text-cyan-700 transition">
                                Reset filters
                            </a>
                            <a href="{{ route('jobs.create') }}"
                               class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700 transition">
                                Post a job
                            </a>
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>
</div>
@endsection