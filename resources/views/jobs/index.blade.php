@extends('layouts.app')

@section('title', request('search') ? 'Freelance jobs matching ' . request('search') . ' | SwiftKudi' : 'Find Freelance Jobs | SwiftKudi')
@section('meta_description', 'Browse active freelance jobs on SwiftKudi. Filter opportunities by category, experience level, work type, and budget.')
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
                <h1 class="marketplace-title">Freelance jobs that fit your skills</h1>
                <p class="marketplace-subtitle">Search current opportunities, compare scope and budget, save the ones you like, and submit a focused proposal.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @auth
                    <a href="{{ route('jobs.applications') }}" class="marketplace-btn marketplace-btn-secondary"><i class="fas fa-paper-plane"></i> My proposals</a>
                    <a href="{{ route('jobs.create') }}" class="marketplace-btn marketplace-btn-primary"><i class="fas fa-plus"></i> Post a job</a>
                @else
                    <a href="{{ route('login') }}" class="marketplace-btn marketplace-btn-secondary">Sign in</a>
                    <a href="{{ route('register') }}" class="marketplace-btn marketplace-btn-primary">Create account</a>
                @endauth
            </div>
        </div>

        <form action="{{ route('jobs.index') }}" method="GET" class="marketplace-card mb-6 p-4 sm:p-5">
            <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_160px_auto]">
                <label class="relative">
                    <span class="sr-only">Search jobs</span>
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input class="marketplace-input pl-11" type="search" name="search" value="{{ request('search') }}" placeholder="Search by job title or keyword">
                </label>
                <select name="category" class="marketplace-input" aria-label="Category">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) request('category') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="sort" class="marketplace-input" aria-label="Sort jobs">
                    <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest</option>
                    <option value="budget_high" {{ request('sort') === 'budget_high' ? 'selected' : '' }}>Highest budget</option>
                    <option value="budget_low" {{ request('sort') === 'budget_low' ? 'selected' : '' }}>Lowest budget</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest</option>
                </select>
                <button class="marketplace-btn marketplace-btn-primary justify-center" type="submit">Search</button>
            </div>

            <details class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-800" {{ request()->hasAny(['type','level','budget_min','budget_max','saved']) ? 'open' : '' }}>
                <summary class="cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-200">More filters</summary>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <select name="type" class="marketplace-input">
                        <option value="">Any work type</option>
                        @foreach(['full-time' => 'Full time','part-time' => 'Part time','contract' => 'Contract','internship' => 'Internship'] as $value => $label)
                            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="level" class="marketplace-input">
                        <option value="">Any experience</option>
                        @foreach(['entry' => 'Entry','intermediate' => 'Intermediate','expert' => 'Expert'] as $value => $label)
                            <option value="{{ $value }}" {{ request('level') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input class="marketplace-input" type="number" min="0" name="budget_min" value="{{ request('budget_min') }}" placeholder="Min budget (₦)">
                    <input class="marketplace-input" type="number" min="0" name="budget_max" value="{{ request('budget_max') }}" placeholder="Max budget (₦)">
                    @auth
                        <label class="marketplace-option-card justify-start gap-3">
                            <input class="marketplace-checkbox" type="checkbox" name="saved" value="1" {{ request()->boolean('saved') ? 'checked' : '' }}>
                            <span class="text-sm font-medium">Saved jobs only</span>
                        </label>
                    @endauth
                </div>
                @if(request()->query())
                    <a href="{{ route('jobs.index') }}" class="mt-3 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-500">Clear filters</a>
                @endif
            </details>
        </form>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
            <section class="space-y-4" aria-label="Job results">
                <div class="flex items-center justify-between gap-3 px-1">
                    <p class="text-sm text-slate-500 dark:text-slate-400"><strong class="text-slate-800 dark:text-slate-100">{{ number_format($jobs->total()) }}</strong> active {{ \Illuminate\Support\Str::plural('job', $jobs->total()) }}</p>
                </div>

                @forelse($jobs as $job)
                    @php $saved = in_array($job->id, $savedJobIds, true); @endphp
                    <article class="marketplace-card marketplace-card-hover p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ $job->created_at->diffForHumans() }}</span>
                                    @if($job->category)<span>•</span><span>{{ $job->category->name }}</span>@endif
                                    @if($job->location)<span>•</span><span>{{ $job->location }}</span>@endif
                                </div>
                                <h2 class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">
                                    <a href="{{ route('jobs.show', $job) }}" class="hover:text-indigo-600 dark:hover:text-indigo-300">{{ $job->title }}</a>
                                </h2>
                            </div>

                            @auth
                                <form method="POST" action="{{ $saved ? route('jobs.unsave', $job) : route('jobs.save', $job) }}">
                                    @csrf
                                    @if($saved) @method('DELETE') @endif
                                    <button type="submit" class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 text-slate-500 hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-300" aria-label="{{ $saved ? 'Remove saved job' : 'Save job' }}">
                                        <i class="{{ $saved ? 'fas' : 'far' }} fa-heart"></i>
                                    </button>
                                </form>
                            @endauth
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="marketplace-pill">{{ $job->budget_range }}</span>
                            <span class="marketplace-pill">{{ $job->type_label }}</span>
                            <span class="marketplace-pill">{{ $job->level_label }}</span>
                            @if($job->duration)<span class="marketplace-pill">{{ $job->duration }}</span>@endif
                        </div>

                        <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit(strip_tags($job->description), 260) }}</p>

                        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4 text-sm dark:border-slate-800">
                            <div class="flex flex-wrap items-center gap-4 text-slate-500 dark:text-slate-400">
                                <span><i class="far fa-file-lines mr-1.5"></i>{{ number_format($job->applications_count ?? 0) }} proposals</span>
                                <span><i class="far fa-eye mr-1.5"></i>{{ number_format($job->views_count ?? 0) }} views</span>
                                <span><i class="far fa-user mr-1.5"></i>{{ max(0, $job->positions_remaining) }} position{{ $job->positions_remaining === 1 ? '' : 's' }} left</span>
                            </div>
                            <a href="{{ route('jobs.show', $job) }}" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-300">View job <i class="fas fa-arrow-right ml-1 text-xs"></i></a>
                        </div>
                    </article>
                @empty
                    <div class="marketplace-card px-6 py-14 text-center">
                        <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300"><i class="fas fa-search text-xl"></i></div>
                        <h2 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">No jobs match these filters</h2>
                        <p class="mx-auto mt-2 max-w-md text-sm text-slate-500 dark:text-slate-400">Try a broader keyword, remove a filter, or check again when new opportunities are posted.</p>
                        <a href="{{ route('jobs.index') }}" class="marketplace-btn marketplace-btn-secondary mt-5">Reset search</a>
                    </div>
                @endforelse

                <div>{{ $jobs->links() }}</div>
            </section>

            <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
                @auth
                    <div class="marketplace-card p-5">
                        <h2 class="font-semibold text-slate-900 dark:text-white">Your work hub</h2>
                        <div class="mt-4 space-y-2 text-sm">
                            <a class="flex items-center justify-between rounded-xl px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800" href="{{ route('jobs.applications') }}"><span>My proposals</span><i class="fas fa-chevron-right text-xs text-slate-400"></i></a>
                            <a class="flex items-center justify-between rounded-xl px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800" href="{{ route('jobs.my-jobs') }}"><span>Jobs I posted</span><i class="fas fa-chevron-right text-xs text-slate-400"></i></a>
                            @if(Route::has('contracts.index'))<a class="flex items-center justify-between rounded-xl px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800" href="{{ route('contracts.index') }}"><span>Contracts</span><i class="fas fa-chevron-right text-xs text-slate-400"></i></a>@endif
                        </div>
                    </div>
                @endauth
                <div class="marketplace-card p-5">
                    <h2 class="font-semibold text-slate-900 dark:text-white">A stronger proposal</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Respond to the actual scope, explain the relevant experience you bring, give a realistic price and timeline, and keep communication inside the project workroom when hired.</p>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
