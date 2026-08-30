@extends('layouts.app')

@section('title', 'My Jobs - SwiftKudi')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="marketplace-page"><div class="marketplace-container">
    <div class="marketplace-page-header">
        <div><span class="marketplace-eyebrow">Client workspace</span><h1 class="marketplace-title mt-2">My jobs</h1><p class="marketplace-subtitle">Manage job posts, review proposals and move selected freelancers into contracts.</p></div>
        <a href="{{ route('jobs.create') }}" class="marketplace-btn-primary"><i class="fas fa-plus"></i>Post a job</a>
    </div>

    @if($jobs->isEmpty())
        <div class="marketplace-card p-10 text-center"><span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-500/10 text-indigo-300"><i class="fas fa-briefcase text-xl"></i></span><h2 class="mt-4 text-lg font-semibold text-white">You haven’t posted a job yet</h2><p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">Describe the outcome you need, set a realistic budget and receive proposals from freelancers.</p><a href="{{ route('jobs.create') }}" class="marketplace-btn-primary mt-6">Post your first job</a></div>
    @else
        <div class="space-y-5">
        @foreach($jobs as $job)
            <article class="marketplace-card overflow-hidden">
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2"><span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $job->status === 'active' ? 'border-green-500/30 bg-green-500/10 text-green-300' : 'border-dark-600 bg-dark-800 text-gray-400' }}">{{ ucfirst($job->status) }}</span>@if($job->category)<span class="marketplace-pill">{{ $job->category->name }}</span>@endif</div>
                            <a href="{{ route('jobs.show', $job) }}" class="mt-3 block text-xl font-semibold text-white hover:text-indigo-300">{{ $job->title }}</a>
                            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm text-gray-500"><span>{{ $job->budget_range }}</span><span>{{ $job->positions_remaining }} position{{ $job->positions_remaining === 1 ? '' : 's' }} remaining</span><span>{{ $job->applications->count() }} proposal{{ $job->applications->count() === 1 ? '' : 's' }}</span><span>Posted {{ $job->created_at->diffForHumans() }}</span></div>
                        </div>
                        <div class="flex flex-wrap gap-2"><a href="{{ route('jobs.show', $job) }}" class="marketplace-btn-secondary">View</a>@if($job->status === 'active')<a href="{{ route('jobs.edit', $job) }}" class="marketplace-btn-secondary">Edit</a><form action="{{ route('jobs.close', $job) }}" method="POST" onsubmit="return confirm('Close this job to new proposals?')">@csrf<button class="marketplace-btn-secondary">Close job</button></form>@endif</div>
                    </div>
                </div>

                <div class="border-t border-dark-700 bg-dark-950/50 p-5 sm:p-6">
                    <div class="mb-4 flex items-center justify-between"><div><h3 class="text-sm font-semibold text-white">Proposals</h3><p class="mt-1 text-xs text-gray-500">Review price, timeline and cover letter before hiring.</p></div></div>
                    @if($job->applications->isEmpty())
                        <p class="rounded-xl border border-dashed border-dark-700 p-5 text-center text-sm text-gray-500">No proposals have been submitted yet.</p>
                    @else
                        <div class="space-y-3">
                        @foreach($job->applications->sortByDesc('created_at') as $application)
                            <div class="rounded-xl border border-dark-700 bg-dark-900 p-4">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0 flex-1"><div class="flex flex-wrap items-center gap-2"><p class="font-semibold text-white">{{ optional($application->user)->name ?: 'Freelancer' }}</p><span class="marketplace-pill">{{ ucfirst($application->status) }}</span></div><div class="mt-2 flex flex-wrap gap-3 text-xs text-gray-500"><span>₦{{ number_format((float)$application->proposal_amount) }}</span><span>{{ $application->estimated_duration }}</span><span>{{ $application->created_at->diffForHumans() }}</span></div><p class="mt-3 line-clamp-2 text-sm leading-6 text-gray-400">{{ $application->cover_letter }}</p></div>
                                    <div class="flex flex-wrap gap-2 lg:max-w-xs lg:justify-end">
                                        @if($application->user)<a href="{{ route('chat.open', ['type'=>'job','referenceId'=>$job->id,'participantId'=>$application->user_id]) }}" class="marketplace-btn-secondary"><i class="far fa-comment"></i>Message</a>@endif
                                        @if($application->status === 'hired' && $application->contract)<a href="{{ route('contracts.show', $application->contract) }}" class="marketplace-btn-primary">Open contract</a>@elseif(in_array($application->status, ['pending','reviewing','shortlisted'], true) && !$job->is_fully_hired)<form method="POST" action="{{ route('jobs.hire', $application) }}" onsubmit="return confirm('Hire this freelancer and create a contract?')">@csrf<button class="marketplace-btn-primary"><i class="fas fa-check"></i>Hire</button></form><form method="POST" action="{{ route('jobs.reject', $application) }}" onsubmit="return confirm('Decline this proposal?')">@csrf<button class="marketplace-btn-secondary text-red-300">Decline</button></form>@endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        </div>
                    @endif
                </div>
            </article>
        @endforeach
        </div>
        <div class="mt-6">{{ $jobs->links() }}</div>
    @endif
</div></div>
@endsection
