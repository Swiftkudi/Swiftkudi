@extends('layouts.app')

@section('title', 'Marketplace Chat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-white">
            <i class="fas fa-comments mr-2 text-blue-400"></i>Messages
        </h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Conversation List -->
        <div class="lg:col-span-1">
            <div class="bg-dark-800 rounded-2xl border border-dark-700 overflow-hidden">
                <div class="p-4 border-b border-dark-700">
                    <h3 class="text-white font-semibold">Conversations</h3>
                </div>
                <div class="divide-y divide-dark-700" id="conversation-list">
                    @if($conversations->isNotEmpty())
                    @foreach($conversations as $conversation)
                    @php
                        $otherUser = $conversation->buyer_id === Auth::id() ? $conversation->seller : $conversation->buyer;
                        $unread = $conversation->messages()->where('sender_id', '!=', Auth::id())->where('is_read', false)->count();
                    @endphp
                    <a href="{{ route('marketplace.chat.show', $conversation->id) }}"
                       class="flex items-center gap-3 p-4 hover:bg-dark-700 transition-all {{ $unread > 0 ? 'bg-indigo-500/5' : '' }}">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold flex-shrink-0">
                            {{ strtoupper(substr($otherUser->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-medium text-sm truncate">{{ $otherUser->name ?? 'Unknown' }}</p>
                            <p class="text-gray-500 text-xs truncate">
                                {{ $conversation->latestMessage->message ?? 'No messages yet' }}
                            </p>
                        </div>
                        @if($unread > 0)
                        <span class="bg-red-500 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0">
                            {{ $unread > 9 ? '9+' : $unread }}
                        </span>
                        @endif
                    </a>
                    @endforeach

                    <div class="mt-4 px-4">
                        {{ $conversations->appends(request()->query())->links() }}
                    </div>
                    @else
                    <div class="p-8 text-center">
                        <i class="fas fa-comments text-4xl text-gray-600 mb-3"></i>
                        <p class="text-gray-500 text-sm">No conversations yet</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Messages Area (placeholder when no conversation selected from list) -->
        <div class="lg:col-span-3">
            <div class="bg-dark-800 rounded-2xl border border-dark-700 p-8 text-center">
                <i class="fas fa-comments text-5xl text-gray-700 mb-4"></i>
                <h3 class="text-gray-400 text-lg mb-2">Select a conversation</h3>
                <p class="text-gray-500 text-sm">Choose a conversation from the list to start messaging.</p>
            </div>
        </div>
    </div>
</div>
@endsection