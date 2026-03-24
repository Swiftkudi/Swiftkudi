<?php

// Script to check sample and total tasks counts
// Run with: php scripts/check_sample_tasks.php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$sampleCount = 0;
if (Schema::hasColumn('tasks', 'is_sample')) {
    $sampleCount = DB::table('tasks')->where('is_sample', true)->count();
} else {
    $demoEmails = ['client@swiftkudi.com', 'worker@swiftkudi.com'];
    $sampleCount = DB::table('tasks')
        ->join('users', 'users.id', '=', 'tasks.user_id')
        ->whereIn('users.email', $demoEmails)
        ->count();
}

total:
$totalCount = DB::table('tasks')->count();

echo "Sample tasks: {$sampleCount}\n";
echo "Total tasks: {$totalCount}\n";
