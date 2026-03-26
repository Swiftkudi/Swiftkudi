<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\NotificationDispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Store or update a push subscription for the authenticated user.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint'         => 'required|string',
            'keys.p256dh'      => 'required|string',
            'keys.auth'        => 'required|string',
            'contentEncoding'  => 'nullable|string',
        ]);

        $user = Auth::user();

        $endpointHash = hash('sha256', $data['endpoint']);

        // Upsert: one record per user+endpoint pair
        PushSubscription::updateOrCreate(
            [
                'user_id'       => $user->id,
                'endpoint_hash' => $endpointHash,
            ],
            [
                'endpoint'         => $data['endpoint'],
                'endpoint_hash'    => $endpointHash,
                'p256dh'           => $data['keys']['p256dh'],
                'auth_token'       => $data['keys']['auth'],
                'content_encoding' => $data['contentEncoding'] ?? 'aesgcm',
            ]
        );

        return response()->json(['status' => 'subscribed']);
    }

    /**
     * Remove a push subscription for the authenticated user.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscription::where('user_id', Auth::id())
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))
            ->delete();

        return response()->json(['status' => 'unsubscribed']);
    }

    /**
     * Send a test push notification to the currently logged-in user.
     */
    public function testPush(Request $request): JsonResponse
    {
        $user = Auth::user();
        $subs = PushSubscription::where('user_id', $user->id)->count();

        if ($subs === 0) {
            return response()->json([
                'status'  => 'no_subscription',
                'message' => 'No push subscription found. Reload the page to register your browser.',
            ], 422);
        }

        try {
            app(NotificationDispatchService::class)->sendPushToUser(
                $user,
                '\u{1F514} SwiftKudi Push Test',
                'Push notifications are working! Sent at ' . now()->format('H:i:s'),
                ['url' => '/dashboard']
            );

            return response()->json([
                'status'  => 'sent',
                'message' => 'Push sent! You should see a browser notification shortly.',
                'subs'    => $subs,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
