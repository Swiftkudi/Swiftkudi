<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PushSubscription;

// Show keys format before deleting
foreach (PushSubscription::all() as $sub) {
    $hasPlus   = str_contains($sub->p256dh, '+') || str_contains($sub->auth_token, '+');
    $hasSlash  = str_contains($sub->p256dh, '/') || str_contains($sub->auth_token, '/');
    $paddedP   = str_ends_with($sub->p256dh, '=');
    echo "sub id={$sub->id} user_id={$sub->user_id}\n";
    echo "  p256dh has +/: " . ($hasPlus || $hasSlash ? 'YES (standard base64 - BAD)' : 'no (base64url - OK)') . "\n";
    echo "  p256dh padded: " . ($paddedP ? 'YES (may break)' : 'no') . "\n";
}

// Wipe all so browsers re-subscribe with correct toJSON() keys
$deleted = PushSubscription::query()->delete();
echo "\nDeleted $deleted old subscriptions.\n";
echo "Browser will re-subscribe automatically on next page load.\n";
