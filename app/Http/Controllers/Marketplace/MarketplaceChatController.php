<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceChatController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $conversations = \App\Models\MarketplaceConversation::forUser($user->id)
            ->with(['latestMessage', 'buyer', 'seller', 'listing'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        return view('marketplace.chat.index', compact('conversations'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $conversation = \App\Models\MarketplaceConversation::with(['messages.sender', 'buyer', 'seller', 'listing'])
            ->findOrFail($id);

        if ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        $conversation->load(['messages.sender', 'buyer', 'seller', 'listing']);
        $conversation->markAsRead();

        $otherUser = $conversation->buyer_id === $user->id
            ? $conversation->seller
            : $conversation->buyer;

        return view('marketplace.chat.show', compact('conversation', 'otherUser'));
    }

    public function open(string $type, int $referenceId, int $participantId)
    {
        $user = Auth::user();

        $allowedTypes = ['task', 'professional_service', 'growth_service', 'digital_product', 'job', 'marketplace_listing'];
        if (!in_array($type, $allowedTypes, true)) {
            abort(404);
        }

        if ($participantId === $user->id) {
            return redirect()->route('marketplace.chat.index')->with('error', 'Cannot open chat with yourself.');
        }

        $buyerId = $user->id;
        $sellerId = $participantId;

        $conversation = \App\Models\MarketplaceConversation::findOrCreate(
            $type,
            $referenceId,
            $buyerId,
            $sellerId
        );

        return redirect()->route('marketplace.chat.show', $conversation);
    }

    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:marketplace_conversations,id',
            'message' => 'required_without:attachment|string|min:1',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $user = Auth::user();
        $conversation = \App\Models\MarketplaceConversation::findOrFail($request->conversation_id);

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

        $message = \App\Models\MarketplaceMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->message ?? '',
            'attachment_type' => $attachmentType,
            'attachment_path' => $attachmentPath,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        $recipientId = $conversation->buyer_id === $user->id
            ? $conversation->seller_id
            : $conversation->buyer_id;
        $recipient = \App\Models\User::find($recipientId);
        if ($recipient) {
            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $recipient,
                'New Chat Message',
                $user->name . ' sent you a new message.',
                \App\Models\Notification::TYPE_MARKETPLACE_MESSAGE,
                [
                    'conversation_id' => $conversation->id,
                    'action_url' => route('marketplace.chat.show', $conversation),
                ],
                'marketplace_chat_message',
                true
            );
        }

        $message->load('sender');

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function startConversation(Request $request)
    {
        $request->validate([
            'type' => 'required|in:task,professional_service,growth_service,digital_product,job,marketplace_listing',
            'reference_id' => 'required|integer',
            'seller_id' => 'required|exists:users,id',
        ]);

        $buyer = Auth::user();

        if ($buyer->id == $request->seller_id) {
            return response()->json(['error' => 'You cannot start a conversation with yourself.'], 400);
        }

        $conversation = \App\Models\MarketplaceConversation::findOrCreate(
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

        $count = \App\Models\MarketplaceMessage::whereHas('conversation', function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id);
            });
        })
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $conversation = \App\Models\MarketplaceConversation::findOrFail($id);

        if ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->markAsRead();

        return response()->json(['success' => true]);
    }

    public function closeConversation($id)
    {
        $user = Auth::user();
        $conversation = \App\Models\MarketplaceConversation::findOrFail($id);

        if ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->close();

        return response()->json(['success' => true]);
    }

    public function apiMessages($id, Request $request)
    {
        $user = Auth::user();
        $conversation = \App\Models\MarketplaceConversation::findOrFail($id);

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
}