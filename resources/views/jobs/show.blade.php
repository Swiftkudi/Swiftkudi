@extends('layouts.app')

@php
    $plainDescription = trim(preg_replace('/\s+/', ' ', strip_tags($job->description)) ?? '');
    $requirements = is_array($job->requirements) ? $job->requirements : (is_string($job->requirements) ? (json_decode($job->requirements, true) ?: preg_split('/[\r\n]+/', $job->requirements)) : []);
    $benefits = is_array($job->benefits) ? $job->benefits : (is_string($job->benefits) ? (json_decode($job->benefits, true) ?: preg_split('/[\r\n]+/', $job->benefits)) : []);
    $employmentType = match($job->job_type) {
        'full-time', 'full_time' => 'FULL_TIME',
        'part-time', 'part_time' => 'PART_TIME',
        'internship' => 'INTERN',
        default => 'CONTRACTOR',
    };
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'JobPosting',
        'title' => $job->title,
        'description' => $plainDescription,
        'datePosted' => optional($job->published_at ?: $job->created_at)->toAtomString(),
        'employmentType' => $employmentType,
        'url' => route('jobs.show', $job),
    ];
    if ($job->expires_at) $schema['validThrough'] = $job->expires_at->toAtomString();
    if ($job->is_remote ?? false) $schema['jobLocationType'] = 'TELECOMMUTE';
    if ($job->location && !($job->is_remote ?? false)) {
        $schema['jobLocation'] = ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => $job->location]];
    }
    if ((float) $job->budget_max > 0) {
        $schema['baseSalary'] = [
            '@type' => 'MonetaryAmount',
            'currency' => 'NGN',
            'value' => ['@type' => 'QuantitativeValue', 'minValue' => (float) $job->budget_min, 'maxValue' => (float) $job->budget_max, 'unitText' => 'PROJECT'],
        ];
    }
@endphp

@section('title', $job->title . ' | Freelance Job | SwiftKudi')
@section('meta_description', \Illuminate\Support\Str::limit($plainDescription, 155))
@section('canonical', route('jobs.show', $job))
@section('robots', $job->status === 'active' && !$job->is_expired ? 'index,follow' : 'noindex,follow')
@section('og_title', $job->title . ' | SwiftKudi')
@section('og_description', \Illuminate\Support\Str::limit($plainDescription, 180))

@push('meta')
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <nav class="mb-5 flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
            <a href="{{ route('jobs.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-300">Find Work</a>
            <i class="fas fa-chevron-right text-[9px]"></i>
            @if($job->category)<a href="{{ route('jobs.index', ['category' => $job->category_id]) }}" class="hover:text-indigo-600 dark:hover:text-indigo-300">{{ $job->category->name }}</a><i class="fas fa-chevron-right text-[9px]"></i>@endif
            <span class="truncate">{{ $job->title }}</span>
        </nav>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-6">
                <article class="marketplace-card overflow-hidden">
                    <header class="p-5 sm:p-7">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap gap-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                                    @if($job->category)<span>{{ $job->category->name }}</span><span>•</span>@endif
                                    <span>Posted {{ $job->created_at->diffForHumans() }}</span>
                                </div>
                                <h1 class="mt-3 text-2xl font-bold leading-tight text-slate-950 dark:text-white sm:text-3xl">{{ $job->title }}</h1>
                                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm text-slate-500 dark:text-slate-400">
                                    @if($job->location)<span><i class="fas fa-location-dot mr-1.5"></i>{{ $job->location }}</span>@endif
                                    <span><i class="far fa-eye mr-1.5"></i>{{ number_format($job->views_count ?? 0) }} views</span>
                                    <span><i class="far fa-file-lines mr-1.5"></i>{{ number_format($job->applications_count ?? 0) }} proposals</span>
                                </div>
                            </div>
                            @auth
                                @if(auth()->id() !== $job->user_id)
                                    <form method="POST" action="{{ $isSaved ? route('jobs.unsave', $job) : route('jobs.save', $job) }}">
                                        @csrf @if($isSaved) @method('DELETE') @endif
                                        <button class="grid h-11 w-11 place-items-center rounded-full border border-slate-200 text-slate-500 hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-300" type="submit" aria-label="{{ $isSaved ? 'Remove saved job' : 'Save job' }}"><i class="{{ $isSaved ? 'fas' : 'far' }} fa-heart"></i></button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    </header>

                    <div class="grid border-y border-slate-200 dark:border-slate-800 sm:grid-cols-4">
                        @foreach([
                            ['fa-money-bill-wave', 'Budget', $job->budget_range],
                            ['fa-signal', 'Experience', $job->level_label],
                            ['fa-clock', 'Work type', $job->type_label],
                            ['fa-user-group', 'Openings', max(0, $job->positions_remaining) . ' left'],
                        ] as [$icon,$label,$value])
                            <div class="border-b border-slate-200 p-4 last:border-b-0 dark:border-slate-800 sm:border-b-0 sm:border-r sm:last:border-r-0">
                                <i class="fas {{ $icon }} text-indigo-500"></i>
                                <div class="mt-2 text-xs text-slate-500">{{ $label }}</div>
                                <div class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">{{ $value }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-7 p-5 sm:p-7">
                        <section>
                            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">About the job</h2>
                            <div class="mt-3 whitespace-pre-line text-[15px] leading-7 text-slate-600 dark:text-slate-300">{{ $job->description }}</div>
                        </section>

                        @if($job->duration)
                            <section><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Expected duration</h2><p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $job->duration }}</p></section>
                        @endif

                        @if(count(array_filter($requirements ?? [])))
                            <section>
                                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Requirements</h2>
                                <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                                    @foreach(array_filter($requirements) as $item)<li class="flex gap-2"><i class="fas fa-check mt-1 text-indigo-500"></i><span>{{ is_scalar($item) ? $item : json_encode($item) }}</span></li>@endforeach
                                </ul>
                            </section>
                        @endif

                        @if(count(array_filter($benefits ?? [])))
                            <section>
                                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Additional details</h2>
                                <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                                    @foreach(array_filter($benefits) as $item)<li class="flex gap-2"><i class="fas fa-circle text-[6px] mt-2 text-slate-400"></i><span>{{ is_scalar($item) ? $item : json_encode($item) }}</span></li>@endforeach
                                </ul>
                            </section>
                        @endif
                    </div>
                </article>

                @auth
                    @if(auth()->id() === $job->user_id)
                        <section class="marketplace-card overflow-hidden">
                            <div class="border-b border-slate-200 p-5 dark:border-slate-800 sm:p-6">
                                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">Proposals</h2>
                                <p class="mt-1 text-sm text-slate-500">Review applicants and hire into a protected contract workroom.</p>
                            </div>
                            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse($job->applications->where('status', '!=', 'withdrawn')->sortByDesc('created_at') as $application)
                                    <div class="p-5 sm:p-6">
                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ $application->user->name }}</h3>
                                                    <span class="marketplace-pill">{{ $application->status_label }}</span>
                                                </div>
                                                <p class="mt-1 text-sm font-medium text-slate-700 dark:text-slate-200">₦{{ number_format((float) $application->proposal_amount, 2) }} · {{ $application->estimated_duration }}</p>
                                                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $application->cover_letter }}</p>
                                                @if($application->user->freelancerProfile?->slug)
                                                    <a class="mt-3 inline-flex text-sm font-semibold text-indigo-600 dark:text-indigo-300" href="{{ route('freelancers.show', $application->user->freelancerProfile->slug) }}">View freelancer profile</a>
                                                @endif
                                            </div>
                                            @if(in_array($application->status, ['pending','reviewing','shortlisted'], true) && !$job->is_fully_hired)
                                                <div class="flex shrink-0 flex-wrap gap-2">
                                                    <form method="POST" action="{{ route('jobs.hire', $application) }}">@csrf<button class="marketplace-btn marketplace-btn-primary" type="submit">Hire</button></form>
                                                    <form method="POST" action="{{ route('jobs.reject', $application) }}">@csrf<button class="marketplace-btn marketplace-btn-secondary" type="submit">Decline</button></form>
                                                </div>
                                            @elseif($application->status === 'hired')
                                                <span class="marketplace-pill">Contract created</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-8 text-center text-sm text-slate-500">No proposals yet.</div>
                                @endforelse
                            </div>
                        </section>
                    @endif
                @endauth

                @if($relatedJobs->isNotEmpty())
                    <section>
                        <h2 class="mb-3 text-lg font-semibold text-slate-950 dark:text-white">Similar jobs</h2>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach($relatedJobs as $relatedJob)
                                <a href="{{ route('jobs.show', $relatedJob) }}" class="marketplace-card marketplace-card-hover p-4">
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ $relatedJob->title }}</div>
                                    <div class="mt-2 text-sm text-slate-500">{{ $relatedJob->budget_range }} · {{ $relatedJob->level_label }}</div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
                <div class="marketplace-card p-5 sm:p-6">
                    @auth
                        @if(auth()->id() === $job->user_id)
                            <a href="{{ route('jobs.edit', $job) }}" class="marketplace-btn marketplace-btn-primary w-full justify-center">Edit job</a>
                            @if($job->status === 'active')<form class="mt-2" method="POST" action="{{ route('jobs.close', $job) }}">@csrf<button class="marketplace-btn marketplace-btn-secondary w-full justify-center" type="submit">Close job</button></form>@endif
                        @elseif($hasApplied)
                            <div class="rounded-xl bg-indigo-50 p-4 text-sm text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-200"><i class="fas fa-check-circle mr-1"></i> You already submitted a proposal for this job.</div>
                            <a href="{{ route('jobs.applications') }}" class="marketplace-btn marketplace-btn-secondary mt-3 w-full justify-center">View my proposals</a>
                        @elseif($job->is_fully_hired || $job->is_expired || $job->status !== 'active')
                            <div class="rounded-xl bg-slate-100 p-4 text-sm text-slate-600 dark:bg-slate-800 dark:text-slate-300">This job is no longer accepting proposals.</div>
                        @else
                            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Submit a proposal</h2>
                            <form action="{{ route('jobs.apply', $job) }}" method="POST" class="mt-4 space-y-4">
                                @csrf
                                <div><label class="marketplace-label" for="proposal_amount">Your price (₦)</label><input id="proposal_amount" class="marketplace-input" type="number" name="proposal_amount" min="0" step="100" value="{{ old('proposal_amount') }}" required></div>
                                <div><label class="marketplace-label" for="estimated_duration">Estimated duration</label><input id="estimated_duration" class="marketplace-input" type="text" name="estimated_duration" maxlength="100" value="{{ old('estimated_duration') }}" placeholder="e.g. 2 weeks" required></div>
                                <div><label class="marketplace-label" for="cover_letter">Cover letter</label><textarea id="cover_letter" class="marketplace-input min-h-[150px]" name="cover_letter" required placeholder="Explain your approach and relevant experience.">{{ old('cover_letter') }}</textarea></div>
                                <button class="marketplace-btn marketplace-btn-primary w-full justify-center" type="submit">Submit proposal</button>
                            </form>
                        @endif
                    @else
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Interested in this job?</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Sign in to save the opportunity or submit a proposal.</p>
                        <a href="{{ route('login') }}" class="marketplace-btn marketplace-btn-primary mt-4 w-full justify-center">Sign in to continue</a>
                    @endauth
                </div>

                <div class="marketplace-card p-5 sm:p-6">
                    <h2 class="font-semibold text-slate-950 dark:text-white">About the client</h2>
                    <div class="mt-4 flex items-center gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-full bg-indigo-100 font-bold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200">{{ strtoupper(substr($job->user->name, 0, 1)) }}</div>
                        <div><div class="font-medium text-slate-900 dark:text-white">{{ $job->user->name }}</div><div class="text-xs text-slate-500">Member since {{ $job->user->created_at->format('M Y') }}</div></div>
                    </div>
                    <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-900"><dt class="text-xs text-slate-500">Jobs posted</dt><dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $clientJobsPosted }}</dd></div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-900"><dt class="text-xs text-slate-500">Hires</dt><dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $clientHires }}</dd></div>
                    </dl>
                    @if($job->user->freelancerProfile?->slug)
                        <a href="{{ route('freelancers.show', $job->user->freelancerProfile->slug) }}" class="marketplace-btn marketplace-btn-secondary mt-4 w-full justify-center">View public profile</a>
                    @endif
                </div>

                <div class="marketplace-card p-5 sm:p-6">
                    <h2 class="font-semibold text-slate-950 dark:text-white">Job details</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Budget</dt><dd class="text-right font-medium text-slate-900 dark:text-white">{{ $job->budget_range }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Experience</dt><dd class="text-right font-medium text-slate-900 dark:text-white">{{ $job->level_label }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Expires</dt><dd class="text-right font-medium text-slate-900 dark:text-white">{{ $job->expires_at ? $job->expires_at->format('M j, Y') : 'Not specified' }}</dd></div>
                    </dl>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
