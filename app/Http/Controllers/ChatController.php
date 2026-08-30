<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceConversation;
use App\Models\MarketplaceMessage;
use App\Models\User;
use App\Services\NotificationManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    protected $notificationManager;

    public function __construct(NotificationManager $notificationManager)
    {
        $this->middleware('auth');
        $this->notificationManager = $notificationManager;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = MarketplaceConversation::forUser($user->id)
            ->with(['latestMessage', 'buyer', 'seller', 'reference'])
            ->withCount(['unreadMessages as unread_count' => function ($q) use ($user) {
                $q->where('sender_id', '!=', $user->id);
            }]);

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search, $user) {
                $q->whereHas('buyer', fn ($uq) => $uq->where('id', '!=', $user->id)->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('seller', fn ($uq) => $uq->where('id', '!=', $user->id)->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('messages', fn ($mq) => $mq->where('message', 'like', '%' . $search . '%'));
            });
        }

        $conversations = $query->orderByRaw('last_message_at IS NULL')
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('chat.index', compact('conversations', 'search'));
    }

    public function show(MarketplaceConversation $conversation)
    {
        $user = Auth::user();
        
        // Check if user is part of the conversation
        if ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        $conversation->load(['messages.sender', 'buyer', 'seller', 'reference']);
        $conversation->markAsReadFor($user->id);

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
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,zip,txt',
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
            $attachmentPath = $file->store('chat/attachments', 'local');
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
                true,
                true,
                true,
                true
            );
        }

        // Load sender relationship and expose only the authorized download endpoint.
        $message->load('sender');
        $message->setAttribute('attachment_url', $message->attachment_path ? route('chat.attachment', $message) : null);

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

        $conversation->markAsReadFor($user->id);

        return response()->json(['success' => true]);
    }

    public function downloadAttachment(MarketplaceMessage $message)
    {
        $user = Auth::user();
        $message->loadMissing('conversation');
        $conversation = $message->conversation;

        if (!$conversation || ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id)) {
            abort(403);
        }

        abort_unless($message->attachment_path, 404);

        if (Storage::disk('local')->exists($message->attachment_path)) {
            if (str_starts_with((string) $message->attachment_type, 'image/')) {
                return response()->file(Storage::disk('local')->path($message->attachment_path), [
                    'Content-Type' => $message->attachment_type,
                    'Cache-Control' => 'private, max-age=3600',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }
            return Storage::disk('local')->download($message->attachment_path, basename($message->attachment_path));
        }

        // Backward-compatible authorized access for attachments created before the private-storage migration.
        abort_unless(Storage::disk('public')->exists($message->attachment_path), 404);
        if (str_starts_with((string) $message->attachment_type, 'image/')) {
            return response()->file(Storage::disk('public')->path($message->attachment_path), [
                'Content-Type' => $message->attachment_type,
                'Cache-Control' => 'private, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }
        return Storage::disk('public')->download($message->attachment_path, basename($message->attachment_path));
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

        $receivedIds = $messages->where('sender_id', '!=', $user->id)->where('is_read', false)->pluck('id');
        if ($receivedIds->isNotEmpty()) {
            MarketplaceMessage::whereIn('id', $receivedIds)->update(['is_read' => true]);
        }

        $messages->each(function (MarketplaceMessage $message) use ($user) {
            if ($message->sender_id !== $user->id) {
                $message->is_read = true;
            }
            $message->setAttribute('attachment_url', $message->attachment_path ? route('chat.attachment', $message) : null);
        });

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
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,zip,txt',
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
            $attachmentPath = $file->store('chat/attachments', 'local');
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
        $message->setAttribute('attachment_url', $message->attachment_path ? route('chat.attachment', $message) : null);

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
                true,
                true,
                true,
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
