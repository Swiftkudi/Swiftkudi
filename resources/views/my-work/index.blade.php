@extends('layouts.app')

@section('title', 'My Work | SwiftKudi')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="marketplace-page-header">
            <div>
                <span class="marketplace-eyebrow">Work center</span>
                <h1 class="marketplace-title mt-2">My Work</h1>
                <p class="marketplace-subtitle">See active contracts, milestone actions, proposals and jobs you posted without jumping between unrelated pages.</p>
            </div>
            <div class="flex flex-wrap gap-2"><a href="{{ route('jobs.index') }}" class="marketplace-btn-secondary"><i class="fas fa-search"></i>Find work</a><a href="{{ route('jobs.create') }}" class="marketplace-btn-primary"><i class="fas fa-plus"></i>Post a job</a></div>
        </div>

        <section class="marketplace-stat-grid mb-8">
            <div class="marketplace-stat"><p class="marketplace-stat-label">Active contracts</p><p class="marketplace-stat-value">{{ number_format($stats['active_contracts']) }}</p><p class="marketplace-stat-meta">Current client/freelancer workrooms</p></div>
            <div class="marketplace-stat"><p class="marketplace-stat-label">Awaiting your review</p><p class="marketplace-stat-value">{{ number_format($stats['submitted_for_review']) }}</p><p class="marketplace-stat-meta">Submitted milestones where you are the client</p></div>
            <div class="marketplace-stat"><p class="marketplace-stat-label">Revision requests</p><p class="marketplace-stat-value">{{ number_format($stats['revision_requested']) }}</p><p class="marketplace-stat-meta">Milestones requiring another submission</p></div>
            @if($hasClientWork)<a href="{{ route('jobs.my-jobs') }}" class="marketplace-stat marketplace-stat-link"><p class="marketplace-stat-label">Proposals to review</p><p class="marketplace-stat-value">{{ number_format($stats['proposals_to_review']) }}</p><p class="marketplace-stat-meta">Applications on jobs you posted</p></a>@else<a href="{{ route('jobs.applications') }}" class="marketplace-stat marketplace-stat-link"><p class="marketplace-stat-label">Pending proposals</p><p class="marketplace-stat-value">{{ number_format($stats['pending_proposals']) }}</p><p class="marketplace-stat-meta">Applications still in the hiring process</p></a>@endif
        </section>

        @if($submittedForReview->isNotEmpty() || $revisionWork->isNotEmpty())
            <section class="marketplace-card mb-8 overflow-hidden">
                <div class="border-b border-dark-700 px-5 py-4 sm:px-6"><h2 class="marketplace-section-title">Needs attention</h2><p class="marketplace-section-description">Work is placed here only when the underlying milestone status requires a next step.</p></div>
                @if($stats['proposals_to_review'] > 0)
                    <a href="{{ route('jobs.my-jobs') }}" class="marketplace-list-row">
                        <span class="marketplace-icon-box flex-none"><i class="fas fa-user-check"></i></span>
                        <div class="min-w-0 flex-1"><div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"><h3 class="font-semibold text-white">Review new job proposals</h3><span class="marketplace-status marketplace-status-warning">{{ number_format($stats['proposals_to_review']) }} waiting</span></div><p class="mt-1 text-sm text-gray-500">Open your posted jobs to compare applicants, profiles and proposal terms.</p></div>
                    </a>
                @endif
                @foreach($submittedForReview as $milestone)
                    <a href="{{ route('contracts.show', $milestone->contract) }}#milestone-{{ $milestone->id }}" class="marketplace-list-row">
                        <span class="marketplace-icon-box flex-none"><i class="fas fa-clipboard-check"></i></span>
                        <div class="min-w-0 flex-1"><div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"><h3 class="font-semibold text-white">Review: {{ $milestone->title }}</h3><span class="marketplace-status marketplace-status-warning">Submitted</span></div><p class="mt-1 text-sm text-gray-500">{{ optional($milestone->contract->freelancer)->name ?: 'Freelancer' }} submitted work on {{ $milestone->contract->title }}.</p></div>
                    </a>
                @endforeach
                @foreach($revisionWork as $milestone)
                    <a href="{{ route('contracts.show', $milestone->contract) }}#milestone-{{ $milestone->id }}" class="marketplace-list-row">
                        <span class="marketplace-icon-box flex-none"><i class="fas fa-rotate-left"></i></span>
                        <div class="min-w-0 flex-1"><div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"><h3 class="font-semibold text-white">Revision requested: {{ $milestone->title }}</h3><span class="marketplace-status marketplace-status-info">Revision</span></div><p class="mt-1 text-sm text-gray-500">Return to {{ $milestone->contract->title }} and address the client’s revision request.</p></div>
                    </a>
                @endforeach
            </section>
        @endif

        <section class="mb-8">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div><h2 class="marketplace-section-title">Contracts</h2><p class="marketplace-section-description">Fixed-price workrooms, progress and current status.</p></div>
                <form method="GET" class="marketplace-result-tabs" aria-label="Filter contracts by status">
                    @foreach(['' => 'All', 'active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'disputed' => 'Disputed'] as $value => $label)
                        <button name="status" value="{{ $value }}" class="marketplace-result-tab {{ request('status','') === $value ? 'marketplace-result-tab-active' : '' }}">{{ $label }}</button>
                    @endforeach
                </form>
            </div>

            @if($contracts->isEmpty())
                <div class="marketplace-card p-8 text-center"><span class="marketplace-empty-icon mx-auto"><i class="fas fa-file-contract"></i></span><h3 class="mt-4 font-semibold text-white">No contracts in this view</h3><p class="mt-2 text-sm text-gray-500">A contract appears here after a job proposal is hired.</p></div>
            @else
                <div class="space-y-3">
                    @foreach($contracts as $contract)
                        @php
                            $otherParty = $contract->client_id === auth()->id() ? $contract->freelancer : $contract->client;
                            $statusClass = $contract->status === 'completed' ? 'marketplace-status-success' : ($contract->status === 'disputed' ? 'marketplace-status-danger' : ($contract->status === 'active' ? 'marketplace-status-info' : 'marketplace-status-warning'));
                        @endphp
                        <a href="{{ route('contracts.show', $contract) }}" class="marketplace-card-hover block p-5 sm:p-6">
                            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                                <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="font-semibold text-white">{{ $contract->title }}</h3><span class="marketplace-status {{ $statusClass }}">{{ ucfirst($contract->status) }}</span></div><p class="mt-1 text-sm text-gray-500">With {{ optional($otherParty)->name ?: 'Marketplace participant' }} · {{ ucfirst(str_replace('_',' ',$contract->contract_type)) }}</p><div class="mt-3 h-2 overflow-hidden rounded-full bg-dark-700"><div class="h-full rounded-full bg-indigo-500" style="width: {{ $contract->progress_percent }}%"></div></div><p class="mt-1 text-xs text-gray-600">{{ $contract->progress_percent }}% of milestones released</p></div>
                                <div class="flex items-center justify-between gap-8 lg:text-right"><div><p class="text-xs text-gray-500">Contract value</p><p class="mt-1 font-semibold text-white">₦{{ number_format((float)$contract->amount, 0) }}</p></div><i class="fas fa-chevron-right text-xs text-gray-600"></i></div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="marketplace-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-dark-700 px-5 py-4"><div><h2 class="marketplace-section-title">My proposals</h2><p class="marketplace-section-description">Recent applications and hiring status.</p></div><a href="{{ route('jobs.applications') }}" class="text-sm font-semibold text-indigo-300">View all</a></div>
                @forelse($proposals as $application)
                    <a href="{{ $application->contract ? route('contracts.show', $application->contract) : route('jobs.show', $application->job) }}" class="marketplace-list-row">
                        <div class="min-w-0 flex-1"><div class="flex items-start justify-between gap-3"><h3 class="truncate font-medium text-white">{{ optional($application->job)->title ?: 'Job proposal' }}</h3><span class="marketplace-status {{ $application->status === 'hired' ? 'marketplace-status-success' : ($application->status === 'rejected' ? 'marketplace-status-danger' : 'marketplace-status-warning') }}">{{ $application->status_label }}</span></div><p class="mt-1 text-sm text-gray-500">₦{{ number_format((float)$application->proposal_amount, 0) }} · {{ $application->estimated_duration }}</p></div>
                    </a>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500">You have not submitted a job proposal yet.</div>
                @endforelse
            </section>

            <section class="marketplace-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-dark-700 px-5 py-4"><div><h2 class="marketplace-section-title">Jobs I posted</h2><p class="marketplace-section-description">Recent client-side hiring activity.</p></div><a href="{{ route('jobs.my-jobs') }}" class="text-sm font-semibold text-indigo-300">View all</a></div>
                @forelse($postedJobs as $job)
                    <a href="{{ route('jobs.show', $job) }}" class="marketplace-list-row">
                        <div class="min-w-0 flex-1"><div class="flex items-start justify-between gap-3"><h3 class="truncate font-medium text-white">{{ $job->title }}</h3><span class="marketplace-status {{ $job->status === 'active' ? 'marketplace-status-success' : 'marketplace-status-info' }}">{{ ucfirst($job->status) }}</span></div><p class="mt-1 text-sm text-gray-500">{{ number_format($job->applications_count ?? 0) }} proposal{{ ($job->applications_count ?? 0) === 1 ? '' : 's' }} · {{ $job->budget_range }}</p></div>
                    </a>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500">You have not posted a freelance job yet.</div>
                @endforelse
            </section>
        </div>
    </div>
</div>
@endsection
