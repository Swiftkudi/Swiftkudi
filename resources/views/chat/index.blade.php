@extends('layouts.app')

@section('title', 'Messages')

@push('styles')
<style>
    .chat-pagination nav > div:first-child {
        color: rgb(156 163 175);
    }

    .chat-pagination nav > div:last-child > span,
    .chat-pagination nav > div:last-child > a {
        background-color: rgb(31 41 55) !important;
        border-color: rgb(75 85 99) !important;
        color: rgb(209 213 219) !important;
    }

    .chat-pagination nav > div:last-child > a:hover {
        background-color: rgb(55 65 81) !important;
        color: rgb(243 244 246) !important;
    }

    .chat-pagination nav span[aria-current="page"] > span {
        background-color: rgb(37 99 235) !important;
        border-color: rgb(37 99 235) !important;
        color: rgb(255 255 255) !important;
    }

    .chat-pagination svg {
        color: rgb(209 213 219);
    }
</style>
@endpush

@section('content')
<div class="py-6 min-h-screen bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-100">Messages</h1>
            <p class="mt-1 text-sm text-gray-400">View and manage your conversations</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Conversations List -->
            <div class="lg:col-span-1 bg-gray-900 rounded-xl shadow-sm border border-gray-800 overflow-hidden">
                <div class="p-4 border-b border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-100">Conversations</h2>
                </div>
                
                <div class="divide-y divide-gray-800 max-h-[600px] overflow-y-auto">
                    @forelse($conversations as $conversation)
                        @php
                            $otherUser = $conversation->buyer_id === auth()->id() 
                                ? $conversation->seller 
                                : $conversation->buyer;
                            $unreadCount = $conversation->unreadCount();
                        @endphp
                        <a href="{{ route('chat.show', $conversation) }}" 
                           class="block p-4 hover:bg-gray-800 transition-colors {{ $unreadCount > 0 ? 'bg-blue-900/30' : '' }}">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr($otherUser->name ?? 'U', 0, 2)) }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-100 truncate">
                                            {{ $otherUser->name ?? 'Unknown User' }}
                                        </p>
                                        @if($unreadCount > 0)
                                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-600 text-white">
                                                {{ $unreadCount }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ ucfirst($conversation->type) }}
                                    </p>
                                    @if($conversation->latestMessage)
                                        <p class="text-sm text-gray-300 truncate mt-1">
                                            {{ $conversation->latestMessage->message }}
                                        </p>
                                    @endif
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $conversation->last_message_at?->diffForHumans() ?? '' }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-300">No conversations yet</p>
                            <p class="text-xs text-gray-500 mt-1">Start a conversation by purchasing a service or posting a task</p>
                        </div>
                    @endforelse
                </div>

                @if($conversations->hasPages())
                    <div class="p-4 border-t border-gray-800 chat-pagination">
                        {{ $conversations->links() }}
                    </div>
                @endif
            </div>

            <!-- Empty State -->
            <div class="lg:col-span-2 bg-gray-900 rounded-xl shadow-sm border border-gray-800 flex items-center justify-center min-h-[400px]">
                <div class="text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-100">Select a conversation</h3>
                    <p class="mt-2 text-sm text-gray-400">Choose a conversation from the list to view messages</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
