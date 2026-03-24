<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
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
}
