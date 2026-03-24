<?php

namespace App\Http\Controllers;

use App\Models\Notification as AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    /**
     * Return the latest unread (and recent read) notifications for the authenticated user.
     * Used by the bell dropdown via JSON fetch.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $notifications = AppNotification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['id', 'title', 'message', 'type', 'is_read', 'read_at', 'created_at', 'data']);

        $unreadCount = AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, int $id)
    {
        $user = Auth::user();

        $notification = AppNotification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($notification) {
            $notification->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllRead(Request $request)
    {
        $user = Auth::user();

        AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Full notifications page.
     */
    public function page(Request $request)
    {
        $user = Auth::user();

        $notifications = AppNotification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        $unreadCount = AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        // Mark all as read when viewing the page
        AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }
}
