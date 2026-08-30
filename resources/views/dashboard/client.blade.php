@extends('layouts.app')

@section('title', 'Campaign Workspace - SwiftKudi')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="marketplace-page-header">
            <div><span class="marketplace-eyebrow">Campaign workspace</span><h1 class="marketplace-title mt-2">Manage task campaigns</h1><p class="marketplace-subtitle">Create campaigns, review progress and manage task submissions from one workspace.</p></div>
            <div class="flex flex-wrap gap-2"><a href="{{ route('jobs.create') }}" class="marketplace-btn-secondary">Post freelance job</a><a href="{{ route('tasks.create') }}" class="marketplace-btn-primary"><i class="fas fa-plus"></i> Create campaign</a></div>
        </div>

        <section class="marketplace-stat-grid mb-8">
            <div class="marketplace-stat"><div class="flex items-center justify-between"><p class="marketplace-stat-label">Campaigns</p><span class="marketplace-icon-box"><i class="fas fa-bullhorn"></i></span></div><p class="marketplace-stat-value">{{ number_format($stats['total_campaigns']) }}</p><p class="marketplace-stat-meta">Created to date</p></div>
            <div class="marketplace-stat"><div class="flex items-center justify-between"><p class="marketplace-stat-label">Active</p><span class="marketplace-icon-box"><i class="fas fa-play"></i></span></div><p class="marketplace-stat-value">{{ number_format($stats['active_campaigns']) }}</p><p class="marketplace-stat-meta">Currently accepting work</p></div>
            <div class="marketplace-stat"><div class="flex items-center justify-between"><p class="marketplace-stat-label">Submissions</p><span class="marketplace-icon-box"><i class="fas fa-clipboard-check"></i></span></div><p class="marketplace-stat-value">{{ number_format($stats['total_submissions']) }}</p><p class="marketplace-stat-meta">Across your campaigns</p></div>
            <div class="marketplace-stat"><div class="flex items-center justify-between"><p class="marketplace-stat-label">Total spent</p><span class="marketplace-icon-box"><i class="fas fa-wallet"></i></span></div><p class="marketplace-stat-value">₦{{ number_format($stats['total_spent'], 2) }}</p><p class="marketplace-stat-meta">Recorded wallet spend</p></div>
        </section>

        @if($pendingApprovals > 0)
            <div class="marketplace-alert marketplace-alert-warning mb-6"><div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div class="flex items-start gap-3"><span class="marketplace-icon-box flex-none"><i class="fas fa-clock"></i></span><div><p class="font-semibold text-white">{{ $pendingApprovals }} submission{{ $pendingApprovals === 1 ? '' : 's' }} waiting for review</p><p class="mt-1 text-sm text-gray-500">Review promptly so contributors know where their work stands.</p></div></div><a href="{{ route('tasks.my-tasks') }}" class="marketplace-btn-secondary flex-none">Review submissions</a></div></div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="marketplace-card overflow-hidden lg:col-span-2">
                <div class="flex items-center justify-between gap-4 border-b border-dark-700 px-5 py-4 sm:px-6"><div><h2 class="marketplace-section-title">My campaigns</h2><p class="marketplace-section-description">Status, progress and budget at a glance.</p></div><a href="{{ route('tasks.create') }}" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200">New campaign</a></div>
                @if($campaigns->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead><tr><th class="px-5 py-3 text-left">Campaign</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3 text-left">Progress</th><th class="px-5 py-3 text-left">Budget</th><th class="px-5 py-3 text-right">Actions</th></tr></thead>
                            <tbody>
                                @foreach($campaigns as $campaign)
                                    @php($campaignProgress = min(100, ((int)$campaign->completed_count / max(1, (int)$campaign->quantity)) * 100))
                                    <tr class="border-t border-dark-700">
                                        <td class="px-5 py-4"><a href="{{ route('tasks.show', $campaign) }}" class="font-medium text-white hover:text-indigo-300">{{ $campaign->title }}</a><p class="mt-1 text-xs text-gray-500">{{ optional($campaign->category)->name ?: 'General' }}</p></td>
                                        <td class="px-5 py-4">@if($campaign->status === 'active')<span class="marketplace-status marketplace-status-success">Active</span>@elseif($campaign->status === 'pending')<span class="marketplace-status marketplace-status-warning">Pending</span>@elseif($campaign->status === 'completed')<span class="marketplace-status marketplace-status-info">Completed</span>@else<span class="marketplace-status marketplace-status-info">{{ ucfirst($campaign->status) }}</span>@endif</td>
                                        <td class="px-5 py-4"><div class="h-2 min-w-[120px] overflow-hidden rounded-full bg-dark-700"><div class="h-full rounded-full bg-indigo-500" style="width: {{ $campaignProgress }}%"></div></div><p class="mt-1 text-xs text-gray-500">{{ (int)$campaign->completed_count }}/{{ (int)$campaign->quantity }} completed</p></td>
                                        <td class="px-5 py-4 whitespace-nowrap font-medium text-gray-200">₦{{ number_format((float)$campaign->budget, 0) }}</td>
                                        <td class="px-5 py-4"><div class="flex justify-end gap-2"><a href="{{ route('tasks.show', $campaign) }}" class="marketplace-btn-secondary !min-h-0 !px-3 !py-2" aria-label="View {{ $campaign->title }}"><i class="fas fa-eye"></i></a>@if($campaign->status === 'active')<a href="{{ route('tasks.analytics', $campaign) }}" class="marketplace-btn-secondary !min-h-0 !px-3 !py-2" aria-label="View analytics for {{ $campaign->title }}"><i class="fas fa-chart-line"></i></a>@endif</div></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-dark-700 px-5 py-4">{{ $campaigns->links() }}</div>
                @else
                    <div class="p-12 text-center"><span class="marketplace-empty-icon mx-auto"><i class="fas fa-bullhorn"></i></span><h3 class="mt-4 font-semibold text-white">No campaigns yet</h3><p class="mt-2 text-sm text-gray-500">Create a focused campaign with clear instructions and a defined budget.</p><a href="{{ route('tasks.create') }}" class="marketplace-btn-primary mt-5">Create campaign</a></div>
                @endif
            </section>

            <aside class="space-y-6">
                <section class="marketplace-card p-5"><h2 class="marketplace-section-title">Quick actions</h2><div class="mt-4 grid gap-2"><a href="{{ route('tasks.create') }}" class="marketplace-btn-primary w-full justify-start"><i class="fas fa-plus"></i> New campaign</a><a href="{{ route('tasks.my-tasks') }}" class="marketplace-btn-secondary w-full justify-start"><i class="fas fa-clipboard-check"></i> Review submissions</a><a href="{{ route('wallet.deposit') }}" class="marketplace-btn-secondary w-full justify-start"><i class="fas fa-wallet"></i> Add funds</a><a href="{{ route('jobs.my-jobs') }}" class="marketplace-btn-secondary w-full justify-start"><i class="fas fa-briefcase"></i> Freelance jobs</a></div></section>
                <section class="marketplace-card p-5"><h2 class="marketplace-section-title">Campaign quality checklist</h2><ul class="mt-4 space-y-3 text-sm leading-6 text-gray-400"><li class="flex gap-2"><i class="fas fa-check mt-1 text-xs text-indigo-300"></i><span>Explain exactly what counts as a valid completion.</span></li><li class="flex gap-2"><i class="fas fa-check mt-1 text-xs text-indigo-300"></i><span>Use realistic rewards and completion limits.</span></li><li class="flex gap-2"><i class="fas fa-check mt-1 text-xs text-indigo-300"></i><span>Review submissions consistently against the same criteria.</span></li><li class="flex gap-2"><i class="fas fa-check mt-1 text-xs text-indigo-300"></i><span>Avoid asking contributors for unnecessary personal data.</span></li></ul></section>
            </aside>
        </div>
    </div>
</div>
@endsection
