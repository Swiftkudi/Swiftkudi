<?php

// Script to list number of tasks owned by each user
// Run with: php scripts/list_task_counts.php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('users')
    ->leftJoin('tasks', 'users.id', '=', 'tasks.user_id')
    ->select(
        'users.id',
        'users.email',
        DB::raw('COALESCE(MAX(users.name), users.email) as display_name'),
        DB::raw('COUNT(tasks.id) as task_count')
    )
    ->groupBy('users.id', 'users.email')
    ->orderByDesc('task_count')
    ->get();

echo "User Task Counts:\n";
echo str_repeat('=', 50) . "\n";
foreach ($rows as $r) {
    $id = $r->id;
    $email = $r->email ?? 'n/a';
    $display = $r->display_name ?? $email;
    $count = $r->task_count;
    echo sprintf("%4s | %-30s | %-20s | %3d\n", $id, $email, $display, $count);
}
