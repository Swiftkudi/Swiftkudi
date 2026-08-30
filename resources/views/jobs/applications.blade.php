@extends('layouts.app')

@section('title', 'My Proposals - SwiftKudi')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="marketplace-page"><div class="marketplace-container">
    <div class="marketplace-page-header">
        <div><span class="marketplace-eyebrow">Freelancer workspace</span><h1 class="marketplace-title mt-2">My proposals</h1><p class="marketplace-subtitle">Track every proposal from submission through hiring.</p></div>
        <a href="{{ route('jobs.index') }}" class="marketplace-btn-primary"><i class="fas fa-search"></i>Find work</a>
    </div>

    @if($applications->isEmpty())
        <div class="marketplace-card p-10 text-center"><span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-500/10 text-indigo-300"><i class="far fa-paper-plane text-xl"></i></span><h2 class="mt-4 text-lg font-semibold text-white">No proposals yet</h2><p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">Browse active jobs and submit a focused proposal that explains how you will solve the client’s problem.</p><a href="{{ route('jobs.index') }}" class="marketplace-btn-primary mt-6">Browse jobs</a></div>
    @else
        <div class="space-y-4">
            @foreach($applications as $application)
                @php
                    $job = $application->job;
                    $statusClass = match($application->status) {
                        'hired' => 'border-green-500/30 bg-green-500/10 text-green-300',
                        'rejected' => 'border-red-500/30 bg-red-500/10 text-red-300',
                        'withdrawn' => 'border-dark-600 bg-dark-800 text-gray-400',
                        default => 'border-yellow-500/30 bg-yellow-500/10 text-yellow-300',
                    };
                @endphp
                <article class="marketplace-card p-5 sm:p-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2"><span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $application->status_label }}</span><span class="text-xs text-gray-600">Submitted {{ $application->created_at->diffForHumans() }}</span></div>
                            <a href="{{ route('jobs.show', $job) }}" class="mt-3 block text-lg font-semibold text-white hover:text-indigo-300">{{ $job->title }}</a>
                            <p class="mt-1 text-sm text-gray-500">Client: {{ optional($job->user)->name ?: 'Client' }}</p>
                            <div class="mt-4 flex flex-wrap gap-2"><span class="marketplace-pill">Proposal ₦{{ number_format((float)$application->proposal_amount) }}</span><span class="marketplace-pill">{{ $application->estimated_duration }}</span><span class="marketplace-pill">Job budget {{ $job->budget_range }}</span></div>
                            @if($application->cover_letter)<div class="mt-5 border-t border-dark-700 pt-4"><p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cover letter</p><p class="mt-2 line-clamp-3 text-sm leading-6 text-gray-400">{{ $application->cover_letter }}</p></div>@endif
                        </div>
                        <div class="flex flex-wrap gap-2 lg:w-56 lg:flex-col">
                            @if($application->status === 'hired' && $application->contract)
                                <a href="{{ route('contracts.show', $application->contract) }}" class="marketplace-btn-primary"><i class="fas fa-file-signature"></i>Open contract</a>
                            @else
                                <a href="{{ route('jobs.show', $job) }}" class="marketplace-btn-secondary"><i class="far fa-eye"></i>View job</a>
                            @endif
                            @if($job && $job->user_id !== auth()->id())<a href="{{ route('chat.open', ['type'=>'job','referenceId'=>$job->id,'participantId'=>$job->user_id]) }}" class="marketplace-btn-secondary"><i class="far fa-comment"></i>Message client</a>@endif
                            @if(in_array($application->status, ['pending','reviewing','shortlisted'], true))
                                <form action="{{ route('jobs.withdraw', $application) }}" method="POST" onsubmit="return confirm('Withdraw this proposal?')">@csrf<button class="marketplace-btn-secondary w-full text-red-300 hover:border-red-500/40 hover:bg-red-500/10"><i class="fas fa-xmark"></i>Withdraw</button></form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $applications->links() }}</div>
    @endif
</div></div>
@endsection
