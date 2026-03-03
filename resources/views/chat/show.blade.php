@extends('layouts.app')

@section('title', 'Chat with ' . ($otherUser->name ?? 'User'))

@push('styles')
<style>
    .messages-container {
        scroll-behavior: smooth;
    }
    .message-bubble {
        max-width: 75%;
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .message-enter {
        animation: slideIn 0.3s ease-out;
    }
</style>
@endpush

@section('content')
<div class="py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div
            id="chat-config"
            data-conversation-id="{{ $conversation->id }}"
            data-last-message-id="{{ $conversation->messages->max('id') ?? 0 }}"
            data-current-user-id="{{ auth()->id() }}"
            data-send-url="{{ route('chat.send') }}"
            data-messages-url="{{ route('chat.messages', $conversation) }}"
        ></div>

        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('chat.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr($otherUser->name ?? 'U', 0, 2)) }}
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ $otherUser->name ?? 'Unknown User' }}</h1>
                            <p class="text-sm text-gray-500">{{ ucfirst($conversation->type) }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @if($conversation->status === 'active')
                        <button onclick="closeConversation()" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                            Close Chat
                        </button>
                    @else
                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                            {{ $conversation->status === 'closed' ? 'bg-gray-100 text-gray-600' : 'bg-green-100 text-green-700' }}">
                            {{ ucfirst($conversation->status) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Messages -->
            <div id="messages-container" class="h-[500px] overflow-y-auto p-6 space-y-4 messages-container">
                @forelse($conversation->messages as $message)
                    @php
                        $isOwn = $message->sender_id === auth()->id();
                    @endphp
                    <div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }} message-enter">
                        <div class="message-bubble {{ $isOwn 
                            ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-2xl rounded-br-md' 
                            : 'bg-gray-100 text-gray-900 rounded-2xl rounded-bl-md' }} px-4 py-3">
                            @if($message->message)
                                <p class="text-sm">{{ $message->message }}</p>
                            @endif
                            
                            @if($message->attachment_path)
                                <div class="mt-2">
                                    @if(str_starts_with($message->attachment_type, 'image/'))
                                        <img src="{{ asset('storage/' . $message->attachment_path) }}" alt="Attachment" class="max-w-[200px] rounded-lg">
                                    @else
                                        <a href="{{ asset('storage/' . $message->attachment_path) }}" target="_blank" class="flex items-center space-x-2 text-xs underline">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            <span>View Attachment</span>
                                        </a>
                                    @endif
                                </div>
                            @endif
                            
                            <p class="text-xs mt-1 {{ $isOwn ? 'text-blue-100' : 'text-gray-400' }}">
                                {{ $message->created_at->format('g:i A') }}
                                @if($isOwn && $message->is_read)
                                    <span class="ml-1">✓✓</span>
                                @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p class="mt-4 text-gray-500">No messages yet</p>
                        <p class="text-sm text-gray-400">Send a message to start the conversation</p>
                    </div>
                @endforelse
            </div>

            <!-- Input Area -->
            @if($conversation->status === 'active')
                <div class="border-t border-gray-100 p-4">
                    <form id="message-form" class="flex items-end space-x-3">
                        <div class="flex-1">
                            <textarea 
                                name="message" 
                                id="message-input"
                                rows="1"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                placeholder="Type your message..."
                                oninput="autoResize(this)"
                                onkeydown="handleKeyDown(event)"
                            ></textarea>
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="cursor-pointer p-3 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                <input type="file" id="attachment-input" class="hidden" onchange="handleFileSelect(this)">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                            </label>
                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all shadow-sm">
                                Send
                            </button>
                        </div>
                    </form>
                    <div id="selected-file" class="hidden mt-2 flex items-center justify-between px-3 py-2 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600 truncate" id="file-name"></span>
                        <button type="button" onclick="clearFile()" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            @else
                <div class="border-t border-gray-100 p-4 bg-gray-50 text-center">
                    <p class="text-gray-500">This conversation has been closed</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    const chatConfig = document.getElementById('chat-config');
    let selectedFile = null;
    const conversationId = Number(chatConfig?.dataset?.conversationId || 0);
    let lastMessageId = Number(chatConfig?.dataset?.lastMessageId || 0);
    const currentUserId = Number(chatConfig?.dataset?.currentUserId || 0);
    const chatSendUrl = chatConfig?.dataset?.sendUrl || '/chat/send';
    const chatMessagesUrl = chatConfig?.dataset?.messagesUrl || `/chat/${conversationId}/messages`;

    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }

    function handleKeyDown(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            document.getElementById('message-form').dispatchEvent(new Event('submit'));
        }
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            selectedFile = input.files[0];
            document.getElementById('file-name').textContent = selectedFile.name;
            document.getElementById('selected-file').classList.remove('hidden');
        }
    }

    function clearFile() {
        selectedFile = null;
        document.getElementById('attachment-input').value = '';
        document.getElementById('selected-file').classList.add('hidden');
    }

    document.getElementById('message-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        
        if (!message && !selectedFile) return;

        const formData = new FormData();
        formData.append('conversation_id', conversationId);
        if (message) formData.append('message', message);
        if (selectedFile) formData.append('attachment', selectedFile);

        try {
            const response = await fetch(chatSendUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                input.value = '';
                autoResize(input);
                clearFile();
                appendMessage(data.message);
                lastMessageId = Math.max(lastMessageId, data.message.id || 0);
                const container = document.getElementById('messages-container');
                container.scrollTop = container.scrollHeight;
            }
        } catch (error) {
            console.error('Error sending message:', error);
            alert('Failed to send message. Please try again.');
        }
    });

    function escapeHtml(value) {
        return (value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatTime(dateString) {
        try {
            return new Date(dateString).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        } catch (_) {
            return '';
        }
    }

    function appendMessage(message) {
        const container = document.getElementById('messages-container');
        const isOwn = Number(message.sender_id) === Number(currentUserId);
        const wrapper = document.createElement('div');
        wrapper.className = `flex ${isOwn ? 'justify-end' : 'justify-start'} message-enter`;

        const bubbleClass = isOwn
            ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-2xl rounded-br-md'
            : 'bg-gray-100 text-gray-900 rounded-2xl rounded-bl-md';

        const timeClass = isOwn ? 'text-blue-100' : 'text-gray-400';
        let attachmentHtml = '';
        if (message.attachment_path) {
            const url = `/storage/${message.attachment_path}`;
            if ((message.attachment_type || '').startsWith('image/')) {
                attachmentHtml = `<div class="mt-2"><img src="${url}" alt="Attachment" class="max-w-[200px] rounded-lg"></div>`;
            } else {
                attachmentHtml = `<div class="mt-2"><a href="${url}" target="_blank" class="flex items-center space-x-2 text-xs underline"><span>View Attachment</span></a></div>`;
            }
        }

        wrapper.innerHTML = `
            <div class="message-bubble ${bubbleClass} px-4 py-3">
                ${message.message ? `<p class="text-sm">${escapeHtml(message.message)}</p>` : ''}
                ${attachmentHtml}
                <p class="text-xs mt-1 ${timeClass}">${formatTime(message.created_at)}</p>
            </div>
        `;

        container.appendChild(wrapper);
    }

    async function pollMessages() {
        try {
            const response = await fetch(`${chatMessagesUrl}?since_id=${lastMessageId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            if (!data.success || !Array.isArray(data.messages) || data.messages.length === 0) {
                return;
            }

            const container = document.getElementById('messages-container');
            const nearBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 120;

            data.messages.forEach((message) => {
                appendMessage(message);
                lastMessageId = Math.max(lastMessageId, message.id || 0);
            });

            if (nearBottom) {
                container.scrollTop = container.scrollHeight;
            }
        } catch (error) {
            console.error('Message polling failed:', error);
        }
    }

    function closeConversation() {
        if (confirm('Are you sure you want to close this conversation?')) {
            fetch(`/chat/${conversationId}/close`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    }

    // Scroll to bottom on load
    window.addEventListener('load', function() {
        const container = document.getElementById('messages-container');
        container.scrollTop = container.scrollHeight;

        setInterval(pollMessages, 5000);
    });
</script>
@endpush
@endsection
