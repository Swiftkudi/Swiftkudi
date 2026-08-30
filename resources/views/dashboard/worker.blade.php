@extends('layouts.app')

@section('title', 'Task Workspace - SwiftKudi')
@section('robots', 'noindex,nofollow')

@section('content')
@php
    $currentUser = auth()->user();
    $workerReferralUrl = $currentUser && $currentUser->referral_code ? route('ref.redirect', ['code' => $currentUser->referral_code]) : null;
@endphp
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="marketplace-page-header">
            <div>
                <span class="marketplace-eyebrow">Task workspace</span>
                <h1 class="marketplace-title mt-2">Available task work</h1>
                <p class="marketplace-subtitle">Find task-based opportunities, monitor reviews and track recorded earnings.</p>
            </div>
            <div class="flex flex-wrap gap-2"><a href="{{ route('jobs.index') }}" class="marketplace-btn-secondary">Freelance jobs</a><a href="{{ route('tasks.index') }}" class="marketplace-btn-primary">Browse tasks</a></div>
        </div>

        <section class="marketplace-stat-grid mb-8">
            <div class="marketplace-stat"><div class="flex items-center justify-between"><p class="marketplace-stat-label">Completed</p><span class="marketplace-icon-box"><i class="fas fa-check"></i></span></div><p class="marketplace-stat-value">{{ number_format($stats['completed']) }}</p><p class="marketplace-stat-meta">Approved submissions</p></div>
            <div class="marketplace-stat"><div class="flex items-center justify-between"><p class="marketplace-stat-label">Pending</p><span class="marketplace-icon-box"><i class="fas fa-clock"></i></span></div><p class="marketplace-stat-value">{{ number_format($stats['pending']) }}</p><p class="marketplace-stat-meta">Waiting for review</p></div>
            <div class="marketplace-stat"><div class="flex items-center justify-between"><p class="marketplace-stat-label">Rejected</p><span class="marketplace-icon-box"><i class="fas fa-rotate-left"></i></span></div><p class="marketplace-stat-value">{{ number_format($stats['rejected']) }}</p><p class="marketplace-stat-meta">Submissions not approved</p></div>
            <div class="marketplace-stat"><div class="flex items-center justify-between"><p class="marketplace-stat-label">Total earned</p><span class="marketplace-icon-box"><i class="fas fa-wallet"></i></span></div><p class="marketplace-stat-value">₦{{ number_format($stats['total_earned'], 2) }}</p><p class="marketplace-stat-meta">Recorded wallet earnings</p></div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="marketplace-card overflow-hidden lg:col-span-2">
                <div class="flex items-center justify-between gap-4 border-b border-dark-700 px-5 py-4 sm:px-6"><div><h2 class="marketplace-section-title">Available tasks</h2><p class="marketplace-section-description">Only tasks you are eligible to submit are shown.</p></div><a href="{{ route('tasks.index') }}" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200">View all</a></div>

                @if($referralTask)
                    <div class="border-b border-dark-700 bg-indigo-500/5 p-5 sm:p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex gap-3"><span class="marketplace-icon-box flex-none"><i class="fas fa-user-plus"></i></span><div><div class="flex flex-wrap items-center gap-2"><h3 class="font-semibold text-white">{{ $referralTask->title }}</h3><span class="marketplace-status marketplace-status-info">Ongoing</span></div><p class="mt-1 max-w-xl text-sm leading-6 text-gray-500">{{ $referralTask->description }}</p><p class="mt-3 font-semibold text-white">₦{{ number_format((float) $referralTask->worker_reward_per_task, 0) }} <span class="text-sm font-normal text-gray-500">per qualified referral</span></p></div></div>
                            @if($workerReferralUrl)<a href="{{ $workerReferralUrl }}" class="marketplace-btn-secondary flex-none">Open referral link</a>@endif
                        </div>
                    </div>
                @endif

                @if($availableTasks->count() > 0)
                    @foreach($availableTasks as $task)
                        <article class="marketplace-list-row">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2"><a href="{{ route('tasks.show', $task) }}" class="font-semibold text-white hover:text-indigo-300">{{ $task->title }}</a>@if($task->is_featured)<span class="marketplace-status marketplace-status-warning">Featured</span>@endif</div>
                                        <p class="mt-2 line-clamp-2 text-sm leading-6 text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags((string) $task->description), 180) }}</p>
                                    </div>
                                    <div class="flex-none sm:text-right"><p class="font-bold text-white">₦{{ number_format((float) $task->worker_reward_per_task, 0) }}</p><p class="text-xs text-gray-500">per task</p></div>
                                </div>
                                <div class="mt-4 flex flex-wrap items-center gap-2"><span class="marketplace-pill"><i class="fas fa-tag"></i>{{ optional($task->category)->name ?: 'General' }}</span><span class="marketplace-pill"><i class="fas fa-users"></i>{{ max(0, (int)$task->quantity - (int)$task->completed_count) }} left</span><span class="marketplace-pill"><i class="fas fa-clock"></i>{{ $task->expires_at ? $task->expires_at->diffForHumans() : 'No expiry' }}</span><a href="{{ route('tasks.show', $task) }}" class="ml-auto text-sm font-semibold text-indigo-300 hover:text-indigo-200">View task <i class="fas fa-arrow-right ml-1 text-xs"></i></a></div>
                            </div>
                        </article>
                    @endforeach
                    <div class="border-t border-dark-700 px-5 py-4">{{ $availableTasks->links() }}</div>
                @else
                    <div class="p-12 text-center"><span class="marketplace-empty-icon mx-auto"><i class="fas fa-inbox"></i></span><h3 class="mt-4 font-semibold text-white">No tasks available</h3><p class="mt-2 text-sm text-gray-500">New opportunities will appear here when they become available.</p></div>
                @endif
            </section>

            <aside class="space-y-6">
                <section class="marketplace-card p-5"><h2 class="marketplace-section-title">Quick actions</h2><div class="mt-4 grid gap-2"><a href="{{ route('tasks.index') }}" class="marketplace-btn-secondary w-full justify-start"><i class="fas fa-search"></i> Browse tasks</a><a href="{{ route('tasks.my-tasks') }}" class="marketplace-btn-secondary w-full justify-start"><i class="fas fa-clipboard-check"></i> My submissions</a><a href="{{ route('referrals.index') }}" class="marketplace-btn-secondary w-full justify-start"><i class="fas fa-user-group"></i> Referrals</a><a href="{{ route('wallet.index') }}" class="marketplace-btn-secondary w-full justify-start"><i class="fas fa-wallet"></i> Wallet</a></div></section>

                <section class="marketplace-card overflow-hidden">
                    <div class="border-b border-dark-700 px-5 py-4"><h2 class="marketplace-section-title">Recent submissions</h2><p class="marketplace-section-description">Your latest task review states.</p></div>
                    @if($mySubmissions->count())
                        @foreach($mySubmissions->take(5) as $submission)
                            <div class="border-t border-dark-700 px-5 py-4 first:border-t-0"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><a href="{{ $submission->task ? route('tasks.show', $submission->task) : '#' }}" class="line-clamp-1 text-sm font-medium text-white">{{ optional($submission->task)->title ?: 'Task submission' }}</a><p class="mt-1 text-xs text-gray-500">{{ $submission->created_at->diffForHumans() }}</p></div>@if($submission->status === 'approved')<span class="marketplace-status marketplace-status-success">Approved</span>@elseif($submission->status === 'pending')<span class="marketplace-status marketplace-status-warning">Pending</span>@else<span class="marketplace-status marketplace-status-danger">{{ ucfirst($submission->status) }}</span>@endif</div></div>
                        @endforeach
                    @else
                        <div class="p-6 text-sm text-gray-500">No submissions yet.</div>
                    @endif
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection
