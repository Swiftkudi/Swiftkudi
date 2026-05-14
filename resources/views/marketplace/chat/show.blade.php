@extends('layouts.app')

@section('title', 'Chat - ' . ($otherUser->name ?? 'Unknown'))

@push('styles')
<style>
    .chat-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 200px);
    }
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        scroll-behavior: smooth;
    }
    .chat-message {
        max-width: 70%;
        padding: 10px 16px;
        border-radius: 18px;
        margin-bottom: 8px;
        word-wrap: break-word;
    }
    .chat-message.sent {
        background: #3b82f6;
        color: white;
        margin-left: auto;
        border-bottom-right-radius: 4px;
    }
    .chat-message.received {
        background: #1e293b;
        border: 1px solid #334155;
        color: #e5e7eb;
        margin-right: auto;
        border-bottom-left-radius: 4px;
    }
    .chat-input-area {
        border-top: 1px solid #1e293b;
        padding: 16px;
        background: #0f172a;
    }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('marketplace.chat.index') }}" class="text-gray-400 hover:text-white">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                {{ strtoupper(substr($otherUser->name ?? '?', 0, 1)) }}
            </div>
            <div>
                <h2 class="text-white font-semibold">{{ $otherUser->name }}</h2>
                <p class="text-gray-500 text-xs">
                    @if($otherUser->seller_rating)
                    <i class="fas fa-star text-yellow-400 text-xs"></i> {{ number_format($otherUser->seller_rating, 1) }}
                    @else
                    No ratings yet
                    @endif
                </p>
            </div>
        </div>
        @if($listing = $conversation->listing)
        <a href="{{ route('marketplace.listings.show', $listing->slug) }}" class="text-sm text-blue-400 hover:underline">
            <i class="fas fa-box mr-1"></i>View Listing
        </a>
        @endif
    </div>

    <!-- Chat Container -->
    <div class="chat-container bg-dark-800 rounded-2xl border border-dark-700 overflow-hidden">
        <!-- Messages -->
        <div class="chat-messages p-4" id="chat-messages">
            @foreach($conversation->messages()->with('sender')->orderBy('id', 'asc')->get() as $message)
            <div class="chat-message {{ $message->sender_id === Auth::id() ? 'sent' : 'received' }}">
                <p class="text-sm leading-relaxed">{{ $message->message }}</p>
                <p class="text-[10px] opacity-60 mt-1 {{ $message->sender_id === Auth::id() ? 'text-right' : 'text-left' }}">
                    {{ $message->created_at->format('h:i A') }}
                </p>
            </div>
            @endforeach
        </div>

        <!-- Input Area -->
        <div class="chat-input-area">
            <form id="chat-form" class="flex gap-3" onsubmit="return sendMessage(event)">
                @csrf
                <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
                <input type="text" name="message" id="message-input"
                       class="flex-1 px-4 py-3 rounded-full bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                       placeholder="Type a message..." autocomplete="off">
                <button type="submit" class="btn btn-primary btn-sm px-6">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function scrollToBottom() {
    const container = document.getElementById('chat-messages');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function sendMessage(e) {
    e.preventDefault();
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    if (!message) return;

    fetch('{{ route('marketplace.chat.message') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            conversation_id: '{{ $conversation->id }}',
            message: message
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            // Append new message
            const container = document.getElementById('chat-messages');
            const div = document.createElement('div');
            div.className = 'chat-message sent';
            div.innerHTML = `<p class="text-sm leading-relaxed">${data.message.message}</p>
                              <p class="text-[10px] opacity-60 mt-1 text-right">Just now</p>`;
            container.appendChild(div);
            scrollToBottom();
        } else {
            alert('Failed to send message.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error sending message.');
    });

    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();

    // Auto-refresh messages every 30 seconds
    setInterval(function() {
        fetch('{{ route('marketplace.chat.api_messages', $conversation->id) }}?since_id={{ $conversation->messages()->max('id') ?? 0 }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.messages && data.messages.length > 0) {
                const container = document.getElementById('chat-messages');
                data.messages.forEach(msg => {
                    const existing = container.querySelector(`[data-msg-id="${msg.id}"]`);
                    if (!existing) {
                        const div = document.createElement('div');
                        div.setAttribute('data-msg-id', msg.id);
                        div.className = 'chat-message ' + (msg.sender_id === {{ Auth::id() }} ? 'sent' : 'received');
                        div.innerHTML = `<p class="text-sm leading-relaxed">${msg.message}</p>
                                          <p class="text-[10px] opacity-60 mt-1">${msg.created_at}</p>`;
                        container.appendChild(div);
                    }
                });
                scrollToBottom();
            }
        });
    }, 30000);
});
</script>
@endpush