@extends('layouts.app')

@section('title', 'Messages - SwiftKudi')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="marketplace-page"><div class="marketplace-container">
    <div class="marketplace-page-header"><div><span class="marketplace-eyebrow">Collaboration</span><h1 class="marketplace-title mt-2">Messages</h1><p class="marketplace-subtitle">Keep job, service and marketplace conversations in one place.</p></div></div>

    <div class="grid min-h-[620px] overflow-hidden rounded-2xl border border-dark-700 bg-dark-900 lg:grid-cols-[380px_1fr]">
        <aside class="border-b border-dark-700 lg:border-b-0 lg:border-r">
            <div class="border-b border-dark-700 p-4">
                <form method="GET" class="relative"><label for="conversation-search" class="sr-only">Search conversations</label><i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-600"></i><input id="conversation-search" name="search" value="{{ $search }}" class="marketplace-input pl-10" placeholder="Search messages or people"></form>
            </div>
            <div class="max-h-[560px] divide-y divide-dark-700 overflow-y-auto">
                @forelse($conversations as $conversation)
                    @php
                        $otherUser = $conversation->buyer_id === auth()->id() ? $conversation->seller : $conversation->buyer;
                        $unreadCount = (int) ($conversation->unread_count ?? 0);
                        $contextTitle = optional($conversation->reference)->title;
                    @endphp
                    <a href="{{ route('chat.show', $conversation) }}" class="block p-4 transition hover:bg-dark-800 {{ $unreadCount ? 'bg-indigo-500/5' : '' }}">
                        <div class="flex gap-3"><span class="flex h-11 w-11 flex-none items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">{{ strtoupper(substr($otherUser->name ?? 'U',0,2)) }}</span><div class="min-w-0 flex-1"><div class="flex items-start justify-between gap-3"><p class="truncate text-sm font-semibold text-white">{{ $otherUser->name ?? 'Unknown user' }}</p>@if($unreadCount)<span class="flex min-w-[20px] items-center justify-center rounded-full bg-indigo-500 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $unreadCount }}</span>@endif</div><p class="mt-0.5 truncate text-xs text-indigo-300">{{ $contextTitle ?: ucwords(str_replace('_',' ',$conversation->type)) }}</p>@if($conversation->latestMessage)<p class="mt-1 truncate text-sm {{ $unreadCount ? 'font-medium text-gray-300' : 'text-gray-500' }}">{{ $conversation->latestMessage->message ?: 'Attachment' }}</p>@endif<p class="mt-1 text-[11px] text-gray-600">{{ $conversation->last_message_at?->diffForHumans() ?: $conversation->updated_at?->diffForHumans() }}</p></div></div>
                    </a>
                @empty
                    <div class="px-6 py-12 text-center"><span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-dark-800 text-gray-500"><i class="far fa-comments"></i></span><p class="mt-4 text-sm font-semibold text-gray-300">No conversations found</p><p class="mt-1 text-xs leading-5 text-gray-600">Message a client or freelancer from a job, proposal or service page.</p></div>
                @endforelse
            </div>
            @if($conversations->hasPages())<div class="border-t border-dark-700 p-4">{{ $conversations->links() }}</div>@endif
        </aside>

        <section class="hidden items-center justify-center bg-dark-950/35 p-8 text-center lg:flex"><div><span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-dark-700 bg-dark-900 text-2xl text-indigo-300"><i class="far fa-comment-dots"></i></span><h2 class="mt-5 text-lg font-semibold text-white">Select a conversation</h2><p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-gray-500">Your messages stay connected to the marketplace context so you can move from discussion to work without losing track of the job or service.</p></div></section>
    </div>
</div></div>
@endsection
