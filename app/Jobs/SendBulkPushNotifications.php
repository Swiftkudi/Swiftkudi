<?php

namespace App\Jobs;

use App\Models\PushSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class SendBulkPushNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $userIds,
        public string $title,
        public string $message,
        public array $data = []
    ) {}

    public function handle(): void
    {
        $publicKey  = config('services.vapid.public_key');
        $privateKey = config('services.vapid.private_key');

        if (empty($publicKey) || empty($privateKey)) {
            return;
        }

        // Get all subscriptions for these users in one query
        $subscriptions = PushSubscription::whereIn('user_id', $this->userIds)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject'    => config('services.vapid.subject', config('mail.from.address')),
                'publicKey'  => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);

        $payload = json_encode([
            'title' => $this->title,
            'body'  => $this->message,
            'url'   => $this->data['action_url'] ?? $this->data['url'] ?? '/dashboard',
        ]);

        $staleEndpoints = [];

        // Send to all subscriptions in batch
        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint'        => $sub->endpoint,
                'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
                'keys' => [
                    'p256dh' => $sub->p256dh,
                    'auth'   => $sub->auth_token,
                ],
            ]);

            $report = $webPush->sendOneNotification($subscription, $payload);

            if ($report instanceof \Minishlink\WebPush\MessageSentReport) {
                $statusCode = $report->getResponse() ? $report->getResponse()->getStatusCode() : null;
                if (in_array($statusCode, [404, 410], true)) {
                    $staleEndpoints[] = hash('sha256', $sub->endpoint);
                }
            }
        }

        // Clean up stale subscriptions
        if (!empty($staleEndpoints)) {
            PushSubscription::whereIn('endpoint_hash', $staleEndpoints)->delete();
        }

        Log::info('Bulk push notifications sent', [
            'user_count' => count($this->userIds),
            'subscription_count' => $subscriptions->count(),
            'stale_cleaned' => count($staleEndpoints)
        ]);
    }
}