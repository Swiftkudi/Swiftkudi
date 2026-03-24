<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\Illuminate\Support\Facades\Schema::dropIfExists('push_subscriptions');
\Illuminate\Support\Facades\DB::statement("DELETE FROM migrations WHERE migration LIKE '%push_subscriptions%'");
echo "Cleaned up push_subscriptions.\n";
