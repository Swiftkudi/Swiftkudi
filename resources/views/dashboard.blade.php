@extends('layouts.app')

@section('title', 'Dashboard - SwiftKudi')
@section('robots', 'noindex,nofollow')

@section('content')
@php
    $user = auth()->user();
    $accountType = $user->account_type ?? 'member';
    $walletBalance = $wallet ? ((float) $wallet->withdrawable_balance + (float) $wallet->promo_credit_balance) : 0;
    $referralUrl = !empty($referralCode) ? route('ref.redirect', ['code' => $referralCode]) : null;
@endphp
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="marketplace-page-header">
            <div>
                <span class="marketplace-eyebrow">Workspace</span>
                <h1 class="marketplace-title mt-2">Welcome back, {{ $user->name }}</h1>
                <p class="marketplace-subtitle">Manage work, earnings, referrals and your SwiftKudi marketplace activity from one place.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('jobs.index') }}" class="marketplace-btn-secondary"><i class="fas fa-search"></i> Find work</a>
                <a href="{{ route('jobs.create') }}" class="marketplace-btn-primary"><i class="fas fa-plus"></i> Post a job</a>
            </div>
        </div>

        @if(!$isActivated)
            <section class="marketplace-alert marketplace-alert-warning mb-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="marketplace-icon-box flex-none"><i class="fas fa-shield-alt"></i></span>
                        <div>
                            <h2 class="font-semibold text-white">Account activation is available</h2>
                            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-400">
                                @if($accountType === 'earner' && $activationFeeEnabled)
                                    Activate for ₦{{ number_format($activationFee, 0) }} to unlock the account features configured for your role.
                                @else
                                    Complete activation to unlock the account features configured for your role.
                                @endif
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('wallet.activate') }}" class="marketplace-btn-primary flex-none">Review activation</a>
                </div>
            </section>
        @endif

        @php
            $needsAttention = (int)($marketplaceStats['unreadMessages'] ?? 0)
                + (int)($marketplaceStats['submittedForReviewCount'] ?? 0)
                + (int)($marketplaceStats['revisionRequestedCount'] ?? 0)
                + (int)($marketplaceStats['proposalsReceivedCount'] ?? 0);
        @endphp

        <section class="marketplace-stat-grid mb-8" aria-label="Marketplace overview">
            <a href="{{ route('my-work.index') }}" class="marketplace-stat marketplace-stat-link">
                <div class="flex items-center justify-between"><p class="marketplace-stat-label">Active contracts</p><span class="marketplace-icon-box"><i class="fas fa-file-contract"></i></span></div>
                <p class="marketplace-stat-value">{{ number_format($marketplaceStats['activeContractsCount'] ?? 0) }}</p>
                <p class="marketplace-stat-meta">Open your work center</p>
            </a>
            <a href="{{ route('chat.index') }}" class="marketplace-stat marketplace-stat-link">
                <div class="flex items-center justify-between"><p class="marketplace-stat-label">Unread messages</p><span class="marketplace-icon-box"><i class="far fa-comments"></i></span></div>
                <p class="marketplace-stat-value">{{ number_format($marketplaceStats['unreadMessages'] ?? 0) }}</p>
                <p class="marketplace-stat-meta">Conversations needing your attention</p>
            </a>
            @if($marketplaceStats['is_client'] ?? false)
                <a href="{{ route('jobs.my-jobs') }}" class="marketplace-stat marketplace-stat-link">
                    <div class="flex items-center justify-between"><p class="marketplace-stat-label">Proposals to review</p><span class="marketplace-icon-box"><i class="fas fa-users"></i></span></div>
                    <p class="marketplace-stat-value">{{ number_format($marketplaceStats['proposalsReceivedCount'] ?? 0) }}</p>
                    <p class="marketplace-stat-meta">Across {{ number_format($marketplaceStats['activeJobsCount'] ?? 0) }} active job{{ ($marketplaceStats['activeJobsCount'] ?? 0) === 1 ? '' : 's' }}</p>
                </a>
            @else
                <a href="{{ route('jobs.applications') }}" class="marketplace-stat marketplace-stat-link">
                    <div class="flex items-center justify-between"><p class="marketplace-stat-label">Proposals sent</p><span class="marketplace-icon-box"><i class="fas fa-paper-plane"></i></span></div>
                    <p class="marketplace-stat-value">{{ number_format($marketplaceStats['proposalsSentCount'] ?? 0) }}</p>
                    <p class="marketplace-stat-meta">Track application status</p>
                </a>
            @endif
            <a href="{{ route('wallet.index') }}" class="marketplace-stat marketplace-stat-link">
                <div class="flex items-center justify-between"><p class="marketplace-stat-label">Wallet balance</p><span class="marketplace-icon-box"><i class="fas fa-wallet"></i></span></div>
                <p class="marketplace-stat-value">₦{{ number_format($walletBalance, 2) }}</p>
                <p class="marketplace-stat-meta">Available and promotional balance</p>
            </a>
        </section>

        <section class="marketplace-card mb-8 overflow-hidden" aria-labelledby="dashboard-next-actions">
            <div class="flex flex-col gap-3 border-b border-dark-700 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 id="dashboard-next-actions" class="marketplace-section-title">What needs your attention</h2>
                    <p class="marketplace-section-description">SwiftKudi surfaces work that may need an action instead of filling your dashboard with decorative charts.</p>
                </div>
                <a href="{{ route('my-work.index') }}" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200">Open My Work <i class="fas fa-arrow-right ml-1 text-xs"></i></a>
            </div>
            <div class="grid md:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('chat.index') }}" class="marketplace-attention-item">
                    <span class="marketplace-icon-box"><i class="far fa-message"></i></span>
                    <div><p class="font-semibold text-white">{{ number_format($marketplaceStats['unreadMessages'] ?? 0) }} unread message{{ ($marketplaceStats['unreadMessages'] ?? 0) === 1 ? '' : 's' }}</p><p class="mt-1 text-sm text-gray-500">Reply to clients and freelancers.</p></div>
                </a>
                @if($marketplaceStats['is_client'] ?? false)
                    <a href="{{ route('jobs.my-jobs') }}" class="marketplace-attention-item">
                        <span class="marketplace-icon-box"><i class="fas fa-user-check"></i></span>
                        <div><p class="font-semibold text-white">{{ number_format($marketplaceStats['proposalsReceivedCount'] ?? 0) }} proposal{{ ($marketplaceStats['proposalsReceivedCount'] ?? 0) === 1 ? '' : 's' }} to review</p><p class="mt-1 text-sm text-gray-500">Compare applicants on your jobs.</p></div>
                    </a>
                    <a href="{{ route('my-work.index') }}" class="marketplace-attention-item">
                        <span class="marketplace-icon-box"><i class="fas fa-clipboard-check"></i></span>
                        <div><p class="font-semibold text-white">{{ number_format($marketplaceStats['submittedForReviewCount'] ?? 0) }} milestone{{ ($marketplaceStats['submittedForReviewCount'] ?? 0) === 1 ? '' : 's' }} awaiting review</p><p class="mt-1 text-sm text-gray-500">Approve delivery or request a revision.</p></div>
                    </a>
                @endif
                @if($marketplaceStats['is_freelancer'] ?? false)
                    <a href="{{ route('jobs.applications') }}" class="marketplace-attention-item">
                        <span class="marketplace-icon-box"><i class="fas fa-paper-plane"></i></span>
                        <div><p class="font-semibold text-white">{{ number_format($marketplaceStats['proposalsSentCount'] ?? 0) }} proposal{{ ($marketplaceStats['proposalsSentCount'] ?? 0) === 1 ? '' : 's' }} submitted</p><p class="mt-1 text-sm text-gray-500">Track hiring decisions and contracts.</p></div>
                    </a>
                    <a href="{{ route('my-work.index') }}" class="marketplace-attention-item">
                        <span class="marketplace-icon-box"><i class="fas fa-rotate-left"></i></span>
                        <div><p class="font-semibold text-white">{{ number_format($marketplaceStats['revisionRequestedCount'] ?? 0) }} revision request{{ ($marketplaceStats['revisionRequestedCount'] ?? 0) === 1 ? '' : 's' }}</p><p class="mt-1 text-sm text-gray-500">Return to submitted work that needs changes.</p></div>
                    </a>
                @endif
                @if(!($marketplaceStats['is_client'] ?? false) && !($marketplaceStats['is_freelancer'] ?? false))
                    <a href="{{ route('jobs.index') }}" class="marketplace-attention-item">
                        <span class="marketplace-icon-box"><i class="fas fa-search"></i></span>
                        <div><p class="font-semibold text-white">Explore freelance work</p><p class="mt-1 text-sm text-gray-500">Browse active opportunities that match your interests.</p></div>
                    </a>
                @endif
            </div>
        </section>

        @if(($marketplaceStats['is_freelancer'] ?? false) && $recommendedJobs->isNotEmpty())
            <section class="marketplace-card mb-8 overflow-hidden">
                <div class="flex items-center justify-between border-b border-dark-700 px-5 py-4 sm:px-6"><div><h2 class="marketplace-section-title">New opportunities</h2><p class="marketplace-section-description">Recent active jobs you have not posted yourself.</p></div><a href="{{ route('jobs.index') }}" class="text-sm font-semibold text-indigo-300">Find work</a></div>
                @foreach($recommendedJobs as $job)
                    <a href="{{ route('jobs.show', $job) }}" class="marketplace-list-row">
                        <div class="min-w-0 flex-1"><div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between"><h3 class="font-semibold text-white">{{ $job->title }}</h3><span class="font-semibold text-gray-200">{{ $job->budget_range }}</span></div><p class="mt-1 line-clamp-2 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags($job->description), 140) }}</p><div class="mt-2 flex flex-wrap gap-2"><span class="marketplace-pill">{{ $job->type_label }}</span>@if($job->category)<span class="marketplace-pill">{{ $job->category->name }}</span>@endif</div></div>
                    </a>
                @endforeach
            </section>
        @endif

        @if(($marketplaceStats['is_client'] ?? false) && $recentClientJobs->isNotEmpty())
            <section class="marketplace-card mb-8 overflow-hidden">
                <div class="flex items-center justify-between border-b border-dark-700 px-5 py-4 sm:px-6"><div><h2 class="marketplace-section-title">Your recent jobs</h2><p class="marketplace-section-description">Open a job to review applicants or manage its status.</p></div><a href="{{ route('jobs.my-jobs') }}" class="text-sm font-semibold text-indigo-300">Manage jobs</a></div>
                @foreach($recentClientJobs as $job)
                    <a href="{{ route('jobs.show', $job) }}" class="marketplace-list-row">
                        <div class="min-w-0 flex-1"><div class="flex items-center justify-between gap-3"><h3 class="truncate font-semibold text-white">{{ $job->title }}</h3><span class="marketplace-status {{ $job->status === 'active' ? 'marketplace-status-success' : 'marketplace-status-info' }}">{{ ucfirst($job->status) }}</span></div><p class="mt-1 text-sm text-gray-500">{{ number_format($job->applications_count ?? 0) }} proposal{{ ($job->applications_count ?? 0) === 1 ? '' : 's' }} · {{ $job->budget_range }}</p></div>
                    </a>
                @endforeach
            </section>
        @endif

        <section class="mb-8">
            <div class="mb-4 flex items-end justify-between gap-4">
                <div><h2 class="marketplace-section-title">Marketplace shortcuts</h2><p class="marketplace-section-description">Move between the main SwiftKudi workspaces without hunting through menus.</p></div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('jobs.index') }}" class="marketplace-card-hover p-5">
                    <span class="marketplace-icon-box"><i class="fas fa-briefcase"></i></span><h3 class="mt-4 font-semibold text-white">Find work</h3><p class="mt-1 text-sm leading-6 text-gray-500">Browse jobs and submit focused proposals.</p>
                </a>
                <a href="{{ route('freelancers.index') }}" class="marketplace-card-hover p-5">
                    <span class="marketplace-icon-box"><i class="fas fa-user-group"></i></span><h3 class="mt-4 font-semibold text-white">Find talent</h3><p class="mt-1 text-sm leading-6 text-gray-500">Review freelancer profiles and skills.</p>
                </a>
                <a href="{{ route('professional-services.index') }}" class="marketplace-card-hover p-5">
                    <span class="marketplace-icon-box"><i class="fas fa-layer-group"></i></span><h3 class="mt-4 font-semibold text-white">Services</h3><p class="mt-1 text-sm leading-6 text-gray-500">Browse defined offers with clear delivery terms.</p>
                </a>
                <a href="{{ route('contracts.index') }}" class="marketplace-card-hover p-5">
                    <span class="marketplace-icon-box"><i class="fas fa-file-contract"></i></span><h3 class="mt-4 font-semibold text-white">Contracts</h3><p class="mt-1 text-sm leading-6 text-gray-500">Manage milestones, submissions and approvals.</p>
                </a>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="marketplace-card overflow-hidden lg:col-span-2">
                <div class="flex items-center justify-between gap-4 border-b border-dark-700 px-5 py-4 sm:px-6">
                    <div><h2 class="marketplace-section-title">Opportunities for you</h2><p class="marketplace-section-description">Available tasks you have not already submitted.</p></div>
                    <a href="{{ route('tasks.index') }}" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200">Browse all</a>
                </div>
                @if(isset($availableTasks) && $availableTasks->count())
                    <div>
                        @foreach($availableTasks->take(5) as $task)
                            <div class="marketplace-list-row">
                                <span class="marketplace-icon-box flex-none"><i class="fas fa-bolt"></i></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div><a href="{{ route('tasks.show', $task) }}" class="font-semibold text-white hover:text-indigo-300">{{ $task->title }}</a><p class="mt-1 line-clamp-2 text-sm leading-6 text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags((string) $task->description), 130) }}</p></div>
                                        <div class="flex-none text-left sm:text-right"><p class="font-semibold text-white">₦{{ number_format((float) $task->worker_reward_per_task, 0) }}</p><p class="text-xs text-gray-500">per completion</p></div>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2"><span class="marketplace-pill">{{ optional($task->category)->name ?: 'General' }}</span>@if($task->is_featured)<span class="marketplace-status marketplace-status-warning">Featured</span>@endif</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-10 text-center"><span class="marketplace-empty-icon mx-auto"><i class="fas fa-search"></i></span><h3 class="mt-4 font-semibold text-white">No task opportunities right now</h3><p class="mt-2 text-sm text-gray-500">Check again later or browse freelance jobs.</p></div>
                @endif
            </section>

            <aside class="space-y-6">
                <section class="marketplace-card p-5">
                    <div class="flex items-center justify-between"><h2 class="marketplace-section-title">Account status</h2><span class="marketplace-status {{ $isActivated ? 'marketplace-status-success' : 'marketplace-status-warning' }}">{{ $isActivated ? 'Active' : 'Activation available' }}</span></div>
                    <dl class="mt-5 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-4"><dt class="text-gray-500">Account type</dt><dd class="font-medium text-gray-200">{{ ucwords(str_replace('_', ' ', $accountType)) }}</dd></div>
                        <div class="flex items-center justify-between gap-4"><dt class="text-gray-500">Daily streak</dt><dd class="font-medium text-gray-200">{{ $user->daily_streak }} days</dd></div>
                        <div class="flex items-center justify-between gap-4"><dt class="text-gray-500">Tasks created</dt><dd class="font-medium text-gray-200">{{ number_format($stats['tasks_created'] ?? 0) }}</dd></div>
                        <div class="flex items-center justify-between gap-4"><dt class="text-gray-500">Referrals</dt><dd class="font-medium text-gray-200">{{ number_format($stats['total_referrals'] ?? 0) }}</dd></div>
                    </dl>
                    <a href="{{ route('onboarding.features') }}" class="marketplace-btn-secondary mt-5 w-full">Manage feature access</a>
                </section>

                <section class="marketplace-card p-5">
                    <h2 class="marketplace-section-title">Referral link</h2>
                    <p class="marketplace-section-description">Share your unique link. Referral rewards follow the current platform rules.</p>
                    @if($referralUrl)
                        <label for="dashboard-referral-link" class="sr-only">Referral link</label>
                        <input id="dashboard-referral-link" class="marketplace-input mt-4" type="text" readonly value="{{ $referralUrl }}">
                        <button id="copy-referral-button" type="button" class="marketplace-btn-secondary mt-3 w-full"><i class="fas fa-copy"></i><span>Copy referral link</span></button>
                        <p id="copy-referral-status" class="mt-2 min-h-[20px] text-center text-xs text-gray-500" aria-live="polite"></p>
                    @else
                        <p class="mt-4 rounded-lg border border-dark-700 bg-dark-950 p-3 text-sm text-gray-500">Your referral code is being generated.</p>
                    @endif
                </section>
            </aside>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="marketplace-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-dark-700 px-5 py-4 sm:px-6"><div><h2 class="marketplace-section-title">My task campaigns</h2><p class="marketplace-section-description">A quick view of work you created.</p></div><a href="{{ route('tasks.my-tasks') }}" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200">View all</a></div>
                @if(isset($myTasks) && $myTasks->count())
                    @foreach($myTasks->take(4) as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="marketplace-list-row block">
                            <div class="min-w-0 flex-1"><div class="flex items-center justify-between gap-3"><h3 class="truncate font-medium text-white">{{ $task->title }}</h3><span class="marketplace-status {{ $task->status === 'active' ? 'marketplace-status-success' : 'marketplace-status-info' }}">{{ ucfirst($task->status) }}</span></div><p class="mt-1 text-sm text-gray-500">{{ (int) ($task->completions_count ?? 0) }} of {{ (int) $task->quantity }} completed</p></div>
                        </a>
                    @endforeach
                @else
                    <div class="p-8 text-center"><p class="text-sm text-gray-500">You have not created a task campaign yet.</p><a href="{{ route('tasks.create') }}" class="marketplace-btn-secondary mt-4">Create a task</a></div>
                @endif
            </section>

            <section class="marketplace-card p-5 sm:p-6">
                <div class="flex items-center justify-between"><div><h2 class="marketplace-section-title">Referral performance</h2><p class="marketplace-section-description">Track referrals and recorded referral earnings.</p></div><a href="{{ route('referrals.index') }}" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200">Details</a></div>
                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="marketplace-option-card"><p class="text-xs font-medium text-gray-500">Total referrals</p><p class="mt-2 text-2xl font-bold text-white">{{ number_format($stats['total_referrals'] ?? 0) }}</p></div>
                    <div class="marketplace-option-card"><p class="text-xs font-medium text-gray-500">Referral earnings</p><p class="mt-2 text-2xl font-bold text-white">₦{{ number_format($stats['referral_earnings'] ?? 0, 2) }}</p></div>
                </div>
                @if(isset($badges) && count($badges) > 0)
                    <div class="mt-6 border-t border-dark-700 pt-5"><p class="text-sm font-semibold text-gray-200">Recent badges</p><div class="mt-3 flex flex-wrap gap-2">@foreach($badges->take(4) as $badge)<span class="marketplace-pill">{!! $badge->icon !!}<span>{{ $badge->name }}</span></span>@endforeach</div></div>
                @endif
            </section>
        </div>
    </div>
</div>

<script>
(function () {
    const progressBar = document.getElementById('dashboard-level-progress');
    if (progressBar) {
        const progress = Number(progressBar.dataset.progress || 0);
        progressBar.style.width = `${Math.min(100, Math.max(0, progress))}%`;
    }

    const copyButton = document.getElementById('copy-referral-button');
    const input = document.getElementById('dashboard-referral-link');
    const status = document.getElementById('copy-referral-status');
    if (!copyButton || !input) return;

    copyButton.addEventListener('click', async function () {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(input.value);
            } else {
                input.focus();
                input.select();
                document.execCommand('copy');
            }
            copyButton.querySelector('span').textContent = 'Copied';
            if (status) status.textContent = 'Referral link copied to your clipboard.';
            setTimeout(function () {
                copyButton.querySelector('span').textContent = 'Copy referral link';
                if (status) status.textContent = '';
            }, 2400);
        } catch (error) {
            input.focus();
            input.select();
            if (status) status.textContent = 'Copy was blocked by your browser. The link is selected for you.';
        }
    });
})();
</script>
@endsection
