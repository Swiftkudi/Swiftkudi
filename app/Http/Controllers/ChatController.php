<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceConversation;
use App\Models\MarketplaceMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $conversations = MarketplaceConversation::forUser($user->id)
            ->with(['latestMessage', 'buyer', 'seller', 'reference'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        return view('chat.index', compact('conversations'));
    }

    public function show(MarketplaceConversation $conversation)
    {
        $user = Auth::user();
        
        // Check if user is part of the conversation
        if ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        $conversation->load(['messages.sender', 'buyer', 'seller', 'reference']);
        $conversation->markAsRead();

        $otherUser = $conversation->buyer_id === $user->id 
            ? $conversation->seller 
            : $conversation->buyer;

        return view('chat.show', compact('conversation', 'otherUser'));
    }

    public function open(string $type, int $referenceId, int $participantId)
    {
        $user = Auth::user();

        $allowedTypes = ['task', 'professional_service', 'growth_service', 'digital_product', 'job'];
        if (!in_array($type, $allowedTypes, true)) {
            abort(404);
        }

        if ($participantId === $user->id) {
            return redirect()->route('chat.index')->with('error', 'Cannot open chat with yourself.');
        }

        $buyerId = $user->id;
        $sellerId = $participantId;

        $conversation = MarketplaceConversation::findOrCreate(
            $type,
            $referenceId,
            $buyerId,
            $sellerId
        );

        return redirect()->route('chat.show', $conversation);
    }

    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:marketplace_conversations,id',
            'message' => 'required_without:attachment|string|min:1',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $user = Auth::user();
        $conversation = MarketplaceConversation::findOrFail($request->conversation_id);

        // Check if user is part of the conversation
        if ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $attachmentType = null;
        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentType = $file->getMimeType();
            $attachmentPath = $file->store('chat/attachments', 'public');
        }

        $message = MarketplaceMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->message ?? '',
            'attachment_type' => $attachmentType,
            'attachment_path' => $attachmentPath,
            'is_read' => false,
        ]);

        // Update conversation last_message_at
        $conversation->update(['last_message_at' => now()]);

        $recipientId = $conversation->buyer_id === $user->id ? $conversation->seller_id : $conversation->buyer_id;
        $recipient = User::find($recipientId);
        if ($recipient) {
            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $recipient,
                'New Chat Message',
                $user->name . ' sent you a new message.',
                \App\Models\Notification::TYPE_SYSTEM,
                [
                    'conversation_id' => $conversation->id,
                    'action_url' => route('chat.show', $conversation),
                ],
                'notify_chat_messages',
                true
            );
        }

        // Load sender relationship
        $message->load('sender');

        // TODO: Broadcast to Pusher/WebSocket here

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function startConversation(Request $request)
    {
        $request->validate([
            'type' => 'required|in:task,professional_service,growth_service,digital_product,job',
            'reference_id' => 'required|integer',
            'seller_id' => 'required|exists:users,id',
        ]);

        $buyer = Auth::user();

        if ($buyer->id == $request->seller_id) {
            return response()->json(['error' => 'You cannot start a conversation with yourself.'], 400);
        }

        $conversation = MarketplaceConversation::findOrCreate(
            $request->type,
            $request->reference_id,
            $buyer->id,
            $request->seller_id
        );

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    public function getUnreadCount()
    {
        $user = Auth::user();
        
        $count = MarketplaceMessage::whereHas('conversation', function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id);
            });
        })
        ->where('sender_id', '!=', $user->id)
        ->where('is_read', false)
        ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function markAsRead(MarketplaceConversation $conversation)
    {
        $user = Auth::user();

        if ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->markAsRead();

        return response()->json(['success' => true]);
    }

    public function closeConversation(MarketplaceConversation $conversation)
    {
        $user = Auth::user();

        if ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->close();

        return response()->json(['success' => true]);
    }

    public function apiMessages(MarketplaceConversation $conversation, Request $request)
    {
        $user = Auth::user();

        if ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = $conversation->messages()->with('sender');

        $sinceId = (int) $request->query('since_id', 0);
        if ($sinceId > 0) {
            $query->where('id', '>', $sinceId);
        }

        $messages = $query->orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    public function apiSend(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:marketplace_conversations,id',
            'message' => 'required_without:attachment|string|min:1',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $user = Auth::user();
        $conversation = MarketplaceConversation::findOrFail($request->conversation_id);

        if ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $attachmentType = null;
        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentType = $file->getMimeType();
            $attachmentPath = $file->store('chat/attachments', 'public');
        }

        $message = MarketplaceMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->message ?? '',
            'attachment_type' => $attachmentType,
            'attachment_path' => $attachmentPath,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $message->load('sender');

        $recipientId = $conversation->buyer_id === $user->id ? $conversation->seller_id : $conversation->buyer_id;
        $recipient = User::find($recipientId);
        if ($recipient) {
            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $recipient,
                'New Chat Message',
                $user->name . ' sent you a new message.',
                \App\Models\Notification::TYPE_SYSTEM,
                [
                    'conversation_id' => $conversation->id,
                    'action_url' => route('chat.show', $conversation),
                ],
                'notify_chat_messages',
                true
            );
        }

        // TODO: Broadcast to WebSocket

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}
