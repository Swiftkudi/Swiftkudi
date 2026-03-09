<?php

// Script to remove seeded/sample tasks and related data safely.
// Run with: php scripts/remove_sample_tasks.php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the console kernel to set up database config
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Helper to delete related rows for a given list of task ids
function deleteForTaskIds(array $ids)
{
    if (empty($ids)) {
        echo "No matching sample tasks found.\n";
        return 0;
    }

    echo "Deleting " . count($ids) . " tasks and related records...\n";

    DB::transaction(function() use ($ids) {
        // delete completions
        if (Schema::hasTable('task_completions')) {
            DB::table('task_completions')->whereIn('task_id', $ids)->delete();
        }

        // delete bundle pivot links if exists
        if (Schema::hasTable('bundle_tasks')) {
            DB::table('bundle_tasks')->whereIn('task_id', $ids)->delete();
        }

        // delete tasks
        DB::table('tasks')->whereIn('id', $ids)->delete();
    });

    echo "Deletion complete.\n";
    return count($ids);
}

// Try to detect is_sample column
$ids = [];
if (Schema::hasColumn('tasks', 'is_sample')) {
    $ids = DB::table('tasks')->where('is_sample', true)->pluck('id')->toArray();
} else {
    // Fallback: delete tasks owned by seeded demo emails
    $demoEmails = ['client@swiftkudi.com', 'worker@swiftkudi.com'];
    $ids = DB::table('tasks')
        ->join('users', 'users.id', '=', 'tasks.user_id')
        ->whereIn('users.email', $demoEmails)
        ->pluck('tasks.id')
        ->toArray();
}

$count = deleteForTaskIds($ids);
echo "Total tasks removed: {$count}\n";
