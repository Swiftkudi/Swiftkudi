@extends('layouts.admin')

@section('title', 'Admin Dashboard - SwiftKudi')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="marketplace-page-header">
            <div><span class="marketplace-eyebrow">Operations</span><h1 class="marketplace-title mt-2">Admin dashboard</h1><p class="marketplace-subtitle">Monitor users, marketplace activity, payouts and operational items that need attention.</p></div>
            <div class="flex flex-wrap gap-2"><a href="{{ route('admin.analytics') }}" class="marketplace-btn-secondary"><i class="fas fa-chart-line"></i> Analytics</a><a href="{{ route('admin.settings') }}" class="marketplace-btn-primary"><i class="fas fa-cog"></i> Settings</a></div>
        </div>

        @if(($stats['unresolved_fraud_logs'] ?? 0) > 0)
            <div class="marketplace-alert marketplace-alert-danger mb-6"><div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div class="flex gap-3"><span class="marketplace-icon-box flex-none"><i class="fas fa-shield-halved"></i></span><div><p class="font-semibold text-white">{{ number_format($stats['unresolved_fraud_logs']) }} unresolved fraud alert{{ $stats['unresolved_fraud_logs'] == 1 ? '' : 's' }}</p><p class="mt-1 text-sm text-gray-500">Review flagged activity before approving related financial actions.</p></div></div><a href="{{ route('admin.fraud-logs') }}" class="marketplace-btn-secondary flex-none">Review alerts</a></div></div>
        @endif

        <section class="marketplace-stat-grid mb-8">
            <div class="marketplace-stat"><div class="flex items-center justify-between"><p class="marketplace-stat-label">Users</p><span class="marketplace-icon-box"><i class="fas fa-users"></i></span></div><p class="marketplace-stat-value">{{ number_format($stats['total_users']) }}</p><p class="marketplace-stat-meta">+{{ number_format($stats['new_users_today']) }} today · {{ number_format($stats['activated_users']) }} activated</p></div>
            <div class="marketplace-stat"><div class="flex items-center justify-between"><p class="marketplace-stat-label">Active tasks</p><span class="marketplace-icon-box"><i class="fas fa-list-check"></i></span></div><p class="marketplace-stat-value">{{ number_format($stats['active_tasks']) }}</p><p class="marketplace-stat-meta">{{ number_format($stats['total_tasks']) }} total · {{ number_format($stats['pending_tasks']) }} pending</p></div>
            <div class="marketplace-stat"><div class="flex items-center justify-between"><p class="marketplace-stat-label">Recorded earnings</p><span class="marketplace-icon-box"><i class="fas fa-wallet"></i></span></div><p class="marketplace-stat-value">₦{{ number_format($stats['total_earnings'], 2) }}</p><p class="marketplace-stat-meta">₦{{ number_format($stats['total_withdrawals'], 2) }} completed withdrawals</p></div>
            <div class="marketplace-stat"><div class="flex items-center justify-between"><p class="marketplace-stat-label">Pending actions</p><span class="marketplace-icon-box"><i class="fas fa-clock"></i></span></div><p class="marketplace-stat-value">{{ number_format($pendingCompletions + $pendingWithdrawals) }}</p><p class="marketplace-stat-meta">{{ number_format($pendingCompletions) }} completions · {{ number_format($pendingWithdrawals) }} withdrawals</p></div>
        </section>

        <section class="mb-8">
            <div class="mb-4"><h2 class="marketplace-section-title">Operations</h2><p class="marketplace-section-description">Common administration workspaces.</p></div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['route' => route('admin.users'), 'icon' => 'fa-users', 'title' => 'Users', 'desc' => 'Accounts and access'],
                    ['route' => route('admin.jobs'), 'icon' => 'fa-briefcase', 'title' => 'Jobs', 'desc' => 'Freelance listings'],
                    ['route' => route('admin.professional-services'), 'icon' => 'fa-layer-group', 'title' => 'Services', 'desc' => 'Service marketplace'],
                    ['route' => route('admin.completions'), 'icon' => 'fa-clipboard-check', 'title' => 'Completions', 'desc' => $pendingCompletions . ' pending'],
                    ['route' => route('admin.withdrawals'), 'icon' => 'fa-money-bill-transfer', 'title' => 'Withdrawals', 'desc' => $pendingWithdrawals . ' pending'],
                    ['route' => route('admin.referrals'), 'icon' => 'fa-user-plus', 'title' => 'Referrals', 'desc' => 'Referral activity'],
                    ['route' => route('admin.settings.email-deliveries'), 'icon' => 'fa-envelope-circle-check', 'title' => 'Email delivery', 'desc' => 'Transport diagnostics'],
                    ['route' => route('admin.settings.security'), 'icon' => 'fa-shield-halved', 'title' => 'Security', 'desc' => 'Limits and controls'],
                ] as $item)
                    <a href="{{ $item['route'] }}" class="marketplace-card-hover p-5"><div class="flex items-start gap-3"><span class="marketplace-icon-box flex-none"><i class="fas {{ $item['icon'] }}"></i></span><div><h3 class="font-semibold text-white">{{ $item['title'] }}</h3><p class="mt-1 text-sm text-gray-500">{{ $item['desc'] }}</p></div></div></a>
                @endforeach
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="marketplace-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-dark-700 px-5 py-4"><div><h2 class="marketplace-section-title">Recent users</h2><p class="marketplace-section-description">Newest accounts on the platform.</p></div><a href="{{ route('admin.users') }}" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200">View all</a></div>
                @forelse($recentUsers as $user)
                    <a href="{{ route('admin.user-details', $user) }}" class="marketplace-list-row"><span class="flex h-10 w-10 flex-none items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">{{ strtoupper(substr($user->name, 0, 2)) }}</span><div class="min-w-0 flex-1"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="truncate text-sm font-medium text-white">{{ $user->name }}</p><p class="truncate text-xs text-gray-500">{{ $user->email }}</p></div><span class="text-xs text-gray-600">{{ $user->created_at->diffForHumans() }}</span></div></div></a>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500">No users yet.</div>
                @endforelse
            </section>

            <section class="marketplace-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-dark-700 px-5 py-4"><div><h2 class="marketplace-section-title">Recent tasks</h2><p class="marketplace-section-description">Latest campaign/task activity.</p></div><a href="{{ route('admin.tasks') }}" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200">View all</a></div>
                @forelse($recentTasks as $task)
                    <div class="marketplace-list-row"><span class="marketplace-icon-box flex-none"><i class="fas fa-list-check"></i></span><div class="min-w-0 flex-1"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="truncate text-sm font-medium text-white">{{ $task->title }}</p><p class="mt-1 text-xs text-gray-500">{{ optional($task->user)->name ?: 'Unknown owner' }}</p></div><span class="marketplace-status {{ $task->status === 'active' ? 'marketplace-status-success' : 'marketplace-status-info' }}">{{ ucfirst($task->status) }}</span></div></div></div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500">No tasks yet.</div>
                @endforelse
            </section>
        </div>

        <section class="marketplace-card mt-6 overflow-hidden">
            <div class="flex items-center justify-between border-b border-dark-700 px-5 py-4"><div><h2 class="marketplace-section-title">Recent withdrawals</h2><p class="marketplace-section-description">Latest wallet withdrawal requests and outcomes.</p></div><a href="{{ route('admin.withdrawals') }}" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200">View all</a></div>
            @if(count($recentWithdrawals))
                <div class="overflow-x-auto"><table class="min-w-full"><thead><tr><th class="px-5 py-3 text-left">User</th><th class="px-5 py-3 text-left">Amount</th><th class="px-5 py-3 text-left">Method</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3 text-left">Submitted</th></tr></thead><tbody>@foreach($recentWithdrawals as $withdrawal)<tr class="border-t border-dark-700"><td class="px-5 py-4"><p class="text-sm font-medium text-white">{{ optional($withdrawal->user)->name ?: 'Unknown user' }}</p><p class="text-xs text-gray-500">{{ optional($withdrawal->user)->email }}</p></td><td class="px-5 py-4 whitespace-nowrap text-sm font-semibold text-gray-200">₦{{ number_format((float)$withdrawal->amount, 2) }}</td><td class="px-5 py-4 text-sm capitalize text-gray-400">{{ $withdrawal->method }}</td><td class="px-5 py-4">@if($withdrawal->status === 'completed')<span class="marketplace-status marketplace-status-success">Completed</span>@elseif($withdrawal->status === 'pending')<span class="marketplace-status marketplace-status-warning">Pending</span>@else<span class="marketplace-status marketplace-status-danger">{{ ucfirst($withdrawal->status) }}</span>@endif</td><td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ $withdrawal->created_at->diffForHumans() }}</td></tr>@endforeach</tbody></table></div>
            @else
                <div class="p-8 text-center text-sm text-gray-500">No withdrawals yet.</div>
            @endif
        </section>
    </div>
</div>
@endsection
