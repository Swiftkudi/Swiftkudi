@extends('layouts.app')

@section('title', 'Message ' . ($otherUser->name ?? 'User') . ' - SwiftKudi')
@section('robots', 'noindex,nofollow')

@push('styles')
<style>.messages-container{scroll-behavior:smooth}.message-bubble{max-width:min(78%,42rem)}@keyframes messageIn{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:translateY(0)}}.message-enter{animation:messageIn .18s ease-out}</style>
@endpush

@section('content')
@php
    $reference = $conversation->reference;
    $contextTitle = optional($reference)->title ?: ucwords(str_replace('_',' ',$conversation->type));
    $contextUrl = null;
    if ($reference) {
        $contextUrl = match($conversation->type) {
            'job', 'jobs' => route('jobs.show', $reference),
            'professional_service', 'service' => route('professional-services.show', $reference),
            'task', 'tasks' => route('tasks.show', $reference),
            'growth_service', 'growth' => route('growth.show', $reference),
            'digital_product', 'product' => route('digital-products.show', $reference),
            default => null,
        };
    }
@endphp
<div class="marketplace-page"><div class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div id="chat-config" data-conversation-id="{{ $conversation->id }}" data-last-message-id="{{ $conversation->messages->max('id') ?? 0 }}" data-current-user-id="{{ auth()->id() }}" data-send-url="{{ route('chat.send') }}" data-messages-url="{{ route('chat.messages', $conversation) }}"></div>

    <div class="overflow-hidden rounded-2xl border border-dark-700 bg-dark-900">
        <header class="static h-auto border-b border-dark-700 bg-dark-900 px-4 py-4 sm:px-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-3"><a href="{{ route('chat.index') }}" class="flex h-9 w-9 flex-none items-center justify-center rounded-lg border border-dark-700 text-gray-400 hover:bg-dark-800 hover:text-white"><i class="fas fa-arrow-left"></i></a><span class="flex h-11 w-11 flex-none items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">{{ strtoupper(substr($otherUser->name ?? 'U',0,2)) }}</span><div class="min-w-0"><h1 class="truncate font-semibold text-white">{{ $otherUser->name ?? 'Unknown user' }}</h1><p class="truncate text-xs text-gray-500">{{ $contextTitle }}</p></div></div>
                <div class="flex flex-wrap items-center gap-2">@if($contextUrl)<a href="{{ $contextUrl }}" class="marketplace-btn-secondary py-2"><i class="fas fa-arrow-up-right-from-square"></i>View context</a>@endif @if($conversation->status === 'active')<button type="button" onclick="closeConversation()" class="marketplace-btn-secondary py-2 text-gray-400">Close chat</button>@else<span class="marketplace-pill">{{ ucfirst($conversation->status) }}</span>@endif</div>
            </div>
        </header>

        <div id="messages-container" class="messages-container h-[min(62vh,620px)] space-y-3 overflow-y-auto bg-dark-950/50 p-4 sm:p-6">
            @forelse($conversation->messages as $message)
                @php($isOwn = $message->sender_id === auth()->id())
                <div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }} message-enter">
                    <div class="message-bubble rounded-2xl px-4 py-3 {{ $isOwn ? 'rounded-br-md bg-indigo-600 text-white' : 'rounded-bl-md border border-dark-700 bg-dark-900 text-gray-100' }}">
                        @if($message->message)<p class="whitespace-pre-wrap break-words text-sm leading-6">{{ $message->message }}</p>@endif
                        @if($message->attachment_path)
                            <div class="mt-2">@if(str_starts_with((string)$message->attachment_type,'image/'))<a href="{{ route('chat.attachment', $message) }}" target="_blank" rel="noopener"><img src="{{ route('chat.attachment', $message) }}" alt="Shared image" class="max-h-72 max-w-full rounded-lg object-contain"></a>@else<a href="{{ route('chat.attachment', $message) }}" class="inline-flex items-center gap-2 rounded-lg border border-white/10 px-3 py-2 text-xs font-semibold underline"><i class="fas fa-paperclip"></i>Download attachment</a>@endif</div>
                        @endif
                        <p class="mt-1.5 text-[10px] {{ $isOwn ? 'text-indigo-100' : 'text-gray-600' }}">{{ $message->created_at->format('g:i A') }} @if($isOwn && $message->is_read)<span class="ml-1" title="Read">✓✓</span>@endif</p>
                    </div>
                </div>
            @empty
                <div id="chat-empty" class="py-16 text-center"><span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-dark-900 text-gray-600"><i class="far fa-comment"></i></span><p class="mt-4 text-sm font-semibold text-gray-300">Start the conversation</p><p class="mt-1 text-xs text-gray-600">Keep project details and decisions here for a clear work history.</p></div>
            @endforelse
        </div>

        @if($conversation->status === 'active')
        <div class="border-t border-dark-700 bg-dark-900 p-4 sm:p-5">
            <div id="chat-error" class="mb-3 hidden items-center justify-between gap-3 rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2 text-sm text-red-300"><span id="chat-error-text"></span><button id="chat-retry-button" type="button" class="flex-none rounded-md border border-red-400/30 px-2.5 py-1 text-xs font-semibold text-red-200 hover:bg-red-500/10">Retry</button></div>
            <form id="message-form" class="flex items-end gap-2 sm:gap-3"><div class="flex-1"><textarea id="message-input" name="message" rows="1" maxlength="5000" class="marketplace-input max-h-40 resize-none" placeholder="Write a message…" oninput="autoResize(this)" onkeydown="handleKeyDown(event)"></textarea></div><label class="flex h-11 w-11 flex-none cursor-pointer items-center justify-center rounded-lg border border-dark-600 text-gray-400 hover:bg-dark-800 hover:text-white" title="Attach file"><input type="file" id="attachment-input" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.zip,.txt" onchange="handleFileSelect(this)"><i class="fas fa-paperclip"></i></label><button id="send-button" type="submit" class="marketplace-btn-primary h-11 px-5"><span>Send</span><i class="fas fa-paper-plane text-xs"></i></button></form>
            <div id="selected-file" class="mt-2 hidden items-center justify-between rounded-lg border border-dark-700 bg-dark-950 px-3 py-2"><span id="file-name" class="truncate text-xs text-gray-400"></span><button type="button" onclick="clearFile()" class="text-gray-500 hover:text-red-300"><i class="fas fa-xmark"></i></button></div>
            <p class="mt-2 text-[11px] text-gray-600">Enter to send · Shift+Enter for a new line · attachments up to 10 MB</p>
        </div>
        @else<div class="border-t border-dark-700 bg-dark-900 p-5 text-center text-sm text-gray-500">This conversation is closed.</div>@endif
    </div>
</div></div>

@push('scripts')
<script>
(() => {
    const config = document.getElementById('chat-config');
    const form = document.getElementById('message-form');
    const input = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const errorBox = document.getElementById('chat-error');
    const errorText = document.getElementById('chat-error-text');
    const retryButton = document.getElementById('chat-retry-button');
    const container = document.getElementById('messages-container');
    if (!config || !container) return;

    let selectedFile = null;
    const conversationId = Number(config.dataset.conversationId || 0);
    let lastMessageId = Number(config.dataset.lastMessageId || 0);
    const currentUserId = Number(config.dataset.currentUserId || 0);
    const chatSendUrl = config.dataset.sendUrl;
    const chatMessagesUrl = config.dataset.messagesUrl;

    window.autoResize = el => { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 160) + 'px'; };
    window.handleKeyDown = e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form?.requestSubmit(); } };
    window.handleFileSelect = el => { if (!el.files?.[0]) return; selectedFile = el.files[0]; document.getElementById('file-name').textContent = selectedFile.name; document.getElementById('selected-file').classList.remove('hidden'); document.getElementById('selected-file').classList.add('flex'); };
    window.clearFile = () => { selectedFile = null; const fileInput = document.getElementById('attachment-input'); if (fileInput) fileInput.value=''; const box=document.getElementById('selected-file'); box?.classList.add('hidden'); box?.classList.remove('flex'); };

    const escapeHtml = value => String(value || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    const formatTime = value => { try { return new Date(value).toLocaleTimeString([], {hour:'numeric',minute:'2-digit'}); } catch (_) { return ''; } };
    const showError = message => { if (!errorBox) return; if (errorText) errorText.textContent = message; errorBox.classList.remove('hidden'); errorBox.classList.add('flex'); };
    const clearError = () => { errorBox?.classList.add('hidden'); errorBox?.classList.remove('flex'); };
    retryButton?.addEventListener('click', () => form?.requestSubmit());

    function appendMessage(message) {
        document.getElementById('chat-empty')?.remove();
        const isOwn = Number(message.sender_id) === currentUserId;
        const wrapper = document.createElement('div');
        wrapper.className = `flex ${isOwn ? 'justify-end' : 'justify-start'} message-enter`;
        const bubbleClass = isOwn ? 'rounded-br-md bg-indigo-600 text-white' : 'rounded-bl-md border border-dark-700 bg-dark-900 text-gray-100';
        const timeClass = isOwn ? 'text-indigo-100' : 'text-gray-600';
        let attachment = '';
        if (message.attachment_url) {
            const safeUrl = escapeHtml(message.attachment_url);
            attachment = (message.attachment_type || '').startsWith('image/')
                ? `<div class="mt-2"><a href="${safeUrl}" target="_blank" rel="noopener"><img src="${safeUrl}" alt="Shared image" class="max-h-72 max-w-full rounded-lg object-contain"></a></div>`
                : `<div class="mt-2"><a href="${safeUrl}" class="inline-flex items-center gap-2 rounded-lg border border-white/10 px-3 py-2 text-xs font-semibold underline">Download attachment</a></div>`;
        }
        wrapper.innerHTML = `<div class="message-bubble rounded-2xl px-4 py-3 ${bubbleClass}">${message.message ? `<p class="whitespace-pre-wrap break-words text-sm leading-6">${escapeHtml(message.message)}</p>` : ''}${attachment}<p class="mt-1.5 text-[10px] ${timeClass}">${formatTime(message.created_at)}</p></div>`;
        container.appendChild(wrapper);
    }

    form?.addEventListener('submit', async e => {
        e.preventDefault(); clearError();
        const text = input.value.trim();
        if (!text && !selectedFile) return;
        sendButton.disabled = true;
        const body = new FormData(); body.append('conversation_id', conversationId); if (text) body.append('message', text); if (selectedFile) body.append('attachment', selectedFile);
        try {
            const response = await fetch(chatSendUrl, { method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || data.error || 'Message could not be sent.');
            input.value=''; autoResize(input); clearFile(); appendMessage(data.message); lastMessageId=Math.max(lastMessageId, Number(data.message.id||0)); container.scrollTop=container.scrollHeight;
        } catch (err) { showError(err.message || 'Message could not be sent. Please retry.'); }
        finally { sendButton.disabled=false; }
    });

    async function pollMessages() {
        try {
            const response = await fetch(`${chatMessagesUrl}?since_id=${lastMessageId}`, { headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'} });
            if (!response.ok) return;
            const data = await response.json(); if (!data.success || !Array.isArray(data.messages) || !data.messages.length) return;
            const nearBottom = container.scrollHeight-container.scrollTop-container.clientHeight < 140;
            data.messages.forEach(message => { if (Number(message.id) > lastMessageId) { appendMessage(message); lastMessageId=Math.max(lastMessageId,Number(message.id||0)); } });
            if (nearBottom) container.scrollTop=container.scrollHeight;
        } catch (_) {}
    }

    window.closeConversation = async () => {
        if (!confirm('Close this conversation? You will no longer be able to send new messages here.')) return;
        const response = await fetch(`/chat/${conversationId}/close`, {method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}});
        if (response.ok) location.reload();
    };

    container.scrollTop=container.scrollHeight;
    setInterval(pollMessages, 5000);
})();
</script>
@endpush
@endsection
