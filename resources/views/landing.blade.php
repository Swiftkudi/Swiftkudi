@extends('layouts.app')

@section('title', 'SwiftKudi — Hire Skilled Freelancers or Find Quality Work')
@section('meta_description', 'SwiftKudi connects clients with skilled freelancers for jobs and professional services, with messaging, contracts, milestones and secure wallet-based payments.')
@section('canonical', route('home'))

@section('content')
<div class="marketplace-page">
    <section class="border-b border-dark-700 bg-dark-950">
        <div class="marketplace-container py-14 sm:py-20 lg:py-24">
            <div class="grid items-center gap-12 lg:grid-cols-[1.1fr_.9fr]">
                <div>
                    <span class="marketplace-eyebrow">Freelance marketplace</span>
                    <h1 class="mt-4 max-w-4xl font-heading text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                        Find the right talent. Build great work together.
                    </h1>
                    <p class="mt-6 max-w-2xl text-base leading-7 text-gray-400 sm:text-lg">
                        Post a job, compare proposals, collaborate in one workroom and manage milestone payments. Freelancers can build a professional profile, discover opportunities and grow their reputation.
                    </p>

                    <form action="{{ route('marketplace.search') }}" method="GET" class="marketplace-search-shell mt-8 max-w-3xl">
                        <label class="marketplace-search-input-wrap" for="home-marketplace-search">
                            <i class="fas fa-search text-gray-500"></i>
                            <input id="home-marketplace-search" name="q" maxlength="120" placeholder="Search jobs, skills, talent or services">
                        </label>
                        <select name="scope" class="marketplace-search-scope" aria-label="Search type">
                            <option value="all">Everything</option>
                            <option value="jobs">Find work</option>
                            <option value="talent">Find talent</option>
                            <option value="services">Services</option>
                        </select>
                        <button class="marketplace-btn-primary" type="submit">Search</button>
                    </form>

                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <a class="marketplace-btn-secondary" href="{{ route('jobs.index') }}"><i class="fas fa-briefcase"></i>Find work</a>
                        <a class="marketplace-btn-secondary" href="{{ route('freelancers.index') }}"><i class="fas fa-user-tie"></i>Find talent</a>
                        <a class="marketplace-btn-secondary" href="{{ route('professional-services.index') }}"><i class="fas fa-layer-group"></i>Browse services</a>
                    </div>
                </div>

                <div class="marketplace-card overflow-hidden p-6 sm:p-8">
                    <div class="flex items-center justify-between border-b border-dark-700 pb-5">
                        <div>
                            <p class="text-sm font-semibold text-white">A complete project workflow</p>
                            <p class="mt-1 text-xs text-gray-500">From discovery to delivery</p>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-300"><i class="fas fa-route"></i></span>
                    </div>
                    <div class="mt-6 space-y-5">
                        @foreach([
                            ['1','Post or discover work','Clear job scope, budgets and skills.'],
                            ['2','Compare and collaborate','Proposals, profiles and messaging in one place.'],
                            ['3','Work through milestones','Fund, submit, revise and approve deliverables.'],
                            ['4','Build reputation','Complete work and collect real marketplace reviews.'],
                        ] as $step)
                            <div class="flex gap-4">
                                <span class="flex h-8 w-8 flex-none items-center justify-center rounded-full border border-indigo-500/30 bg-indigo-500/10 text-xs font-bold text-indigo-300">{{ $step[0] }}</span>
                                <div><p class="text-sm font-semibold text-gray-100">{{ $step[1] }}</p><p class="mt-1 text-sm leading-6 text-gray-500">{{ $step[2] }}</p></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($categories->isNotEmpty())
    <section class="border-b border-dark-700 bg-dark-900/30">
        <div class="marketplace-container py-10">
            <p class="mb-5 text-sm font-semibold text-gray-300">Explore services by category</p>
            <div class="flex flex-wrap gap-2.5">
                @foreach($categories as $category)
                    <a href="{{ route('professional-services.index', ['category' => $category->slug ?: $category->id]) }}" class="marketplace-pill px-4 py-2 hover:border-indigo-500/50 hover:text-white">{{ $category->name }}</a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section>
        <div class="marketplace-container py-14 sm:py-16">
            <div class="marketplace-page-header">
                <div><span class="marketplace-eyebrow">Find work</span><h2 class="marketplace-title mt-2">Fresh opportunities</h2><p class="marketplace-subtitle">Browse active jobs posted by clients on SwiftKudi.</p></div>
                <a class="marketplace-btn-secondary" href="{{ route('jobs.index') }}">View all jobs <i class="fas fa-arrow-right text-xs"></i></a>
            </div>

            @if($jobs->isEmpty())
                <div class="marketplace-card p-8 text-center text-sm text-gray-500">No active jobs are available right now.</div>
            @else
                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach($jobs as $job)
                        <a href="{{ route('jobs.show', $job) }}" class="marketplace-card-hover block p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0"><p class="text-xs font-semibold uppercase tracking-wide text-indigo-400">{{ optional($job->category)->name ?: 'Job' }}</p><h3 class="mt-2 line-clamp-2 text-lg font-semibold text-white">{{ $job->title }}</h3></div>
                                <span class="whitespace-nowrap text-sm font-bold text-gray-100">{{ $job->budget_range }}</span>
                            </div>
                            <p class="mt-3 line-clamp-2 text-sm leading-6 text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags($job->description), 170) }}</p>
                            <div class="mt-5 flex flex-wrap gap-2 text-xs text-gray-400"><span class="marketplace-pill">{{ $job->type_label }}</span><span class="marketplace-pill">{{ $job->level_label }}</span>@if($job->location)<span class="marketplace-pill">{{ $job->location }}</span>@endif</div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="border-y border-dark-700 bg-dark-900/30">
        <div class="marketplace-container py-14 sm:py-16">
            <div class="marketplace-page-header">
                <div><span class="marketplace-eyebrow">Project catalog</span><h2 class="marketplace-title mt-2">Ready-to-buy professional services</h2><p class="marketplace-subtitle">Start with a defined service, price and delivery window.</p></div>
                <a class="marketplace-btn-secondary" href="{{ route('professional-services.index') }}">Browse services <i class="fas fa-arrow-right text-xs"></i></a>
            </div>

            @if($services->isEmpty())
                <div class="marketplace-card p-8 text-center text-sm text-gray-500">No active services are available right now.</div>
            @else
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($services as $service)
                        <a href="{{ route('professional-services.show', $service) }}" class="marketplace-card-hover block p-5">
                            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-400">{{ optional($service->category)->name ?: 'Professional service' }}</p>
                            <h3 class="mt-2 line-clamp-2 min-h-[48px] font-semibold text-white">{{ $service->title }}</h3>
                            <p class="mt-3 line-clamp-2 text-sm leading-6 text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags($service->description), 120) }}</p>
                            <div class="mt-5 flex items-center justify-between border-t border-dark-700 pt-4"><div class="text-xs text-gray-500">By <span class="font-medium text-gray-300">{{ optional($service->seller)->name ?: 'Freelancer' }}</span></div><div class="text-right"><span class="text-xs text-gray-500">From</span><p class="font-bold text-white">₦{{ number_format((float) $service->price) }}</p></div></div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if($freelancers->isNotEmpty())
    <section>
        <div class="marketplace-container py-14 sm:py-16">
            <div class="marketplace-page-header"><div><span class="marketplace-eyebrow">Find talent</span><h2 class="marketplace-title mt-2">Discover professionals</h2><p class="marketplace-subtitle">Review profiles, skills and availability before starting a conversation.</p></div><a class="marketplace-btn-secondary" href="{{ route('freelancers.index') }}">Browse talent <i class="fas fa-arrow-right text-xs"></i></a></div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($freelancers as $profile)
                    @php($profileUser = $profile->user)
                    @if($profileUser)
                    <a href="{{ route('freelancers.show', $profile->slug) }}" class="marketplace-card-hover block p-5">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">{{ strtoupper(substr($profileUser->name,0,2)) }}</div>
                        <h3 class="mt-4 font-semibold text-white">{{ $profileUser->name }}</h3>
                        <p class="mt-1 line-clamp-1 text-sm text-indigo-300">{{ $profile->professional_title ?: 'Freelancer' }}</p>
                        <p class="mt-3 line-clamp-2 text-sm leading-6 text-gray-500">{{ $profile->bio ?: 'View profile for skills, services and work history.' }}</p>
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="border-t border-dark-700 bg-dark-900/30">
        <div class="marketplace-container py-14 text-center sm:py-16">
            <h2 class="font-heading text-3xl font-bold text-white">Ready to get started?</h2>
            <p class="mx-auto mt-3 max-w-2xl text-gray-400">Create an account to post work, submit proposals, message securely and manage projects from one marketplace.</p>
            <div class="mt-7 flex flex-wrap justify-center gap-3"><a href="{{ route('register') }}" class="marketplace-btn-primary">Create an account</a><a href="{{ route('jobs.index') }}" class="marketplace-btn-secondary">Explore jobs</a></div>
        </div>
    </section>
</div>

    <section class="border-y border-dark-700 bg-dark-900/30">
        <div class="marketplace-container py-14 sm:py-16">
            <div class="mb-8 max-w-2xl"><span class="marketplace-eyebrow">Built for real work</span><h2 class="marketplace-title mt-2">Clear marketplace signals without invented claims</h2><p class="marketplace-subtitle">SwiftKudi shows trust information only when the platform has the underlying record.</p></div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="marketplace-card p-5"><span class="marketplace-icon-box"><i class="fas fa-user-check"></i></span><h3 class="mt-4 font-semibold text-white">Professional profiles</h3><p class="mt-2 text-sm leading-6 text-gray-500">Compare titles, skills, availability, rates, completed work and reviews where those records exist.</p></div>
                <div class="marketplace-card p-5"><span class="marketplace-icon-box"><i class="fas fa-comments"></i></span><h3 class="mt-4 font-semibold text-white">Connected messaging</h3><p class="mt-2 text-sm leading-6 text-gray-500">Keep job and service conversations attached to the marketplace context instead of scattered channels.</p></div>
                <div class="marketplace-card p-5"><span class="marketplace-icon-box"><i class="fas fa-list-check"></i></span><h3 class="mt-4 font-semibold text-white">Milestone workrooms</h3><p class="mt-2 text-sm leading-6 text-gray-500">Fund, submit, request revisions and approve milestone work through the contract workflow.</p></div>
                <div class="marketplace-card p-5"><span class="marketplace-icon-box"><i class="fas fa-star"></i></span><h3 class="mt-4 font-semibold text-white">Recorded reputation</h3><p class="mt-2 text-sm leading-6 text-gray-500">Ratings and review counts appear when SwiftKudi has real completed marketplace feedback.</p></div>
            </div>
        </div>
    </section>
@endsection
