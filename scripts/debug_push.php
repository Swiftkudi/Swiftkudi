<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use App\Models\PushSubscription;

echo "=== VAPID CONFIG ===\n";
$publicKey  = config('services.vapid.public_key');
$privateKey = config('services.vapid.private_key');
$subject    = (string) config('services.vapid.subject');

// Normalise subject
if ($subject === '' || $subject === 'mailto:' || $subject === 'mailto') {
    $mailFrom = (string) config('mail.from.address');
    $subject = ($mailFrom !== '' && $mailFrom !== 'null')
        ? 'mailto:' . $mailFrom
        : (string) config('app.url', 'http://localhost');
}

echo "subject    = $subject\n";
echo "public_key = " . ($publicKey ? substr($publicKey, 0, 30) . '...' : 'MISSING') . "\n";
echo "private_key= " . ($privateKey ? 'set' : 'MISSING') . "\n";

echo "\n=== SUBSCRIPTIONS ===\n";
$subs = PushSubscription::all();
if ($subs->isEmpty()) {
    echo "NO SUBSCRIPTIONS FOUND - browser has not subscribed yet\n";
    exit(0);
}

foreach ($subs as $sub) {
    echo "id={$sub->id} user_id={$sub->user_id} enc={$sub->content_encoding}\n";
    echo "  endpoint=" . substr($sub->endpoint, 0, 80) . "...\n";
    echo "  p256dh  =" . substr($sub->p256dh, 0, 40) . "\n";
    echo "  auth    =" . substr($sub->auth_token, 0, 20) . "\n";
}

echo "\n=== SENDING TEST PUSH ===\n";
$sub = $subs->first();
try {
    $webPush = new WebPush([
        'VAPID' => [
            'subject'    => $subject,
            'publicKey'  => $publicKey,
            'privateKey' => $privateKey,
        ],
    ]);

    $payload = json_encode([
        'title' => '🔔 SwiftKudi Push Test',
        'body'  => 'Direct push pipeline test - ' . date('H:i:s'),
        'icon'  => '/favicon.svg',
        'url'   => '/dashboard',
    ]);

    $subscription = Subscription::create([
        'endpoint'        => $sub->endpoint,
        'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
        'keys' => [
            'p256dh' => $sub->p256dh,
            'auth'   => $sub->auth_token,
        ],
    ]);

    $report = $webPush->sendOneNotification($subscription, $payload);

    echo "success: " . ($report->isSuccess() ? 'yes' : 'no') . "\n";
    if ($report->getResponse()) {
        echo "http_status: " . $report->getResponse()->getStatusCode() . "\n";
        echo "response_body: " . $report->getResponse()->getBody() . "\n";
    }
    if ($report->getReason()) {
        echo "reason: " . $report->getReason() . "\n";
    }
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
