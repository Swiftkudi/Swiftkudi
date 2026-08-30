@extends('layouts.app')

@section('title', 'Notifications | SwiftKudi')
@section('meta_description', 'Your SwiftKudi marketplace notifications.')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container max-w-5xl">
        <div class="marketplace-page-header">
            <div>
                <p class="marketplace-eyebrow">Activity center</p>
                <h1 class="marketplace-title">Notifications</h1>
                <p class="marketplace-subtitle">Keep track of proposals, contracts, messages, payments and account activity.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button type="submit" class="marketplace-btn-secondary"><i class="far fa-check-circle"></i>Mark all read</button></form>
                @endif
                <a href="{{ route('notification-settings.edit') }}" class="marketplace-btn-secondary"><i class="fas fa-sliders"></i>Preferences</a>
            </div>
        </div>

        <div class="marketplace-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-dark-700 px-5 py-4">
                <p class="text-sm font-semibold text-white">Recent activity</p>
                <span class="marketplace-pill">{{ $unreadCount }} unread</span>
            </div>

            @forelse($notifications as $notification)
                @php
                    $data = is_array($notification->data) ? $notification->data : [];
                    $candidateUrl = $data['action_url'] ?? $data['url'] ?? null;
                    $actionUrl = null;
                    if (is_string($candidateUrl) && $candidateUrl !== '') {
                        if (str_starts_with($candidateUrl, '/') && !str_starts_with($candidateUrl, '//')) {
                            $actionUrl = url($candidateUrl);
                        } else {
                            $parts = parse_url($candidateUrl);
                            $appParts = parse_url(config('app.url'));
                            if (($parts['host'] ?? null) && ($parts['host'] ?? null) === ($appParts['host'] ?? null)) {
                                $actionUrl = $candidateUrl;
                            }
                        }
                    }
                    $iconMap = [
                        'task_approved' => ['fa-check', 'text-emerald-300', 'bg-emerald-500/10'],
                        'task_rejected' => ['fa-xmark', 'text-red-300', 'bg-red-500/10'],
                        'job_created' => ['fa-briefcase', 'text-indigo-300', 'bg-indigo-500/10'],
                        'job_application_submitted' => ['fa-paper-plane', 'text-indigo-300', 'bg-indigo-500/10'],
                        'job_applicant_hired' => ['fa-handshake', 'text-emerald-300', 'bg-emerald-500/10'],
                        'marketplace_message' => ['fa-comment', 'text-blue-300', 'bg-blue-500/10'],
                        'marketplace_dispute' => ['fa-triangle-exclamation', 'text-amber-300', 'bg-amber-500/10'],
                        'payment' => ['fa-wallet', 'text-emerald-300', 'bg-emerald-500/10'],
                        'withdrawal' => ['fa-building-columns', 'text-blue-300', 'bg-blue-500/10'],
                        'system' => ['fa-bell', 'text-gray-300', 'bg-dark-800'],
                    ];
                    [$ico, $icoColor, $icoBg] = $iconMap[$notification->type] ?? $iconMap['system'];
                @endphp
                <article class="flex gap-4 border-b border-dark-700 px-5 py-5 last:border-b-0 {{ $notification->is_read ? '' : 'bg-indigo-500/[0.04]' }}">
                    <div class="mt-0.5 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl {{ $icoBg }}"><i class="fas {{ $ico }} {{ $icoColor }}"></i></div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                @if($actionUrl)
                                    <a href="{{ $actionUrl }}" class="text-sm font-semibold text-white hover:text-indigo-300">{{ $notification->title }}</a>
                                @else
                                    <p class="text-sm font-semibold text-white">{{ $notification->title }}</p>
                                @endif
                                <p class="mt-1 text-sm leading-6 text-gray-400">{{ $notification->message }}</p>
                            </div>
                            @unless($notification->is_read)<span class="mt-1.5 h-2.5 w-2.5 flex-shrink-0 rounded-full bg-indigo-500" aria-label="Unread"></span>@endunless
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500">
                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                            <span class="capitalize">{{ str_replace('_', ' ', $notification->type) }}</span>
                            @if($actionUrl)<a href="{{ $actionUrl }}" class="font-medium text-indigo-400 hover:text-indigo-300">Open</a>@endif
                            <form method="POST" action="{{ $notification->is_read ? route('notifications.unread', $notification->id) : route('notifications.read', $notification->id) }}">@csrf<button type="submit" class="font-medium text-gray-400 hover:text-white">Mark {{ $notification->is_read ? 'unread' : 'read' }}</button></form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="px-6 py-16 text-center"><i class="far fa-bell-slash mb-4 block text-4xl text-gray-600"></i><h2 class="text-base font-semibold text-white">No notifications yet</h2><p class="mt-2 text-sm text-gray-500">Important marketplace activity will appear here.</p></div>
            @endforelse
        </div>

        @if($notifications->hasPages())<div class="mt-6">{{ $notifications->links() }}</div>@endif
    </div>
</div>
@endsection
