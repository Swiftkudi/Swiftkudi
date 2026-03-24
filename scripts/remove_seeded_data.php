<?php

// One-command seeded data cleanup for production/live use.
// Keeps admin account intact and removes only known seeded/demo data.
//
// Usage:
//   php scripts/remove_seeded_data.php
//   php scripts/remove_seeded_data.php --dry-run

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$dryRun = in_array('--dry-run', $argv, true);

$protectedEmails = [
    'admin@swiftkudi.com',
];

$seededEmails = [
    // core demo users
    'client@swiftkudi.com',
    'worker@swiftkudi.com',

    // marketplace seeded providers
    'chioma@swiftkudi.com',
    'techpro@swiftkudi.com',
    'contentkings@swiftkudi.com',
    'videomagic@swiftkudi.com',
    'socialgurus@swiftkudi.com',
];

$seededEmails = array_values(array_diff($seededEmails, $protectedEmails));

$seededUsers = DB::table('users')
    ->select('id', 'email', 'is_admin')
    ->whereIn('email', $seededEmails)
    ->get();

$seededUserIds = $seededUsers->pluck('id')->map(fn($id) => (int) $id)->all();

$sampleTaskIds = [];
if (Schema::hasTable('tasks')) {
    if (Schema::hasColumn('tasks', 'is_sample')) {
        $sampleTaskIds = DB::table('tasks')->where('is_sample', true)->pluck('id')->map(fn($id) => (int) $id)->all();
    }
}

$summary = [
    'seeded_users_found' => count($seededUserIds),
    'sample_tasks_found' => count($sampleTaskIds),
    'deleted_rows' => [],
];

function countByIn(string $table, string $column, array $ids): int
{
    if (empty($ids) || !Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
        return 0;
    }

    return (int) DB::table($table)->whereIn($column, $ids)->count();
}

function deleteByIn(string $table, string $column, array $ids, array &$summary, bool $dryRun = false): int
{
    if (empty($ids) || !Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
        return 0;
    }

    $query = DB::table($table)->whereIn($column, $ids);
    $count = (int) $query->count();

    if ($count > 0 && !$dryRun) {
        $query->delete();
    }

    if ($count > 0) {
        $key = $table . '.' . $column;
        $summary['deleted_rows'][$key] = ($summary['deleted_rows'][$key] ?? 0) + $count;
    }

    return $count;
}

// Early safety exit
if (empty($seededUserIds) && empty($sampleTaskIds)) {
    echo "No seeded users/tasks found. Nothing to remove.\n";
    exit(0);
}

echo "Seeded users found: {$summary['seeded_users_found']}\n";
echo "Sample tasks found: {$summary['sample_tasks_found']}\n";

if (!empty($seededUsers)) {
    echo "Target seeded emails:\n";
    foreach ($seededUsers as $u) {
        echo " - {$u->email}";
        if (!empty($u->is_admin)) {
            echo " (admin flag set - will still be protected by email rules)";
        }
        echo "\n";
    }
}

echo $dryRun
    ? "\nRunning DRY RUN (no delete will happen).\n"
    : "\nExecuting delete...\n";

DB::transaction(function () use (&$summary, $dryRun, $seededUserIds, $sampleTaskIds, $protectedEmails) {
    // 1) Remove sample task dependencies first
    if (!empty($sampleTaskIds)) {
        deleteByIn('task_completions', 'task_id', $sampleTaskIds, $summary, $dryRun);
        deleteByIn('bundle_tasks', 'task_id', $sampleTaskIds, $summary, $dryRun);
        deleteByIn('tasks', 'id', $sampleTaskIds, $summary, $dryRun);
    }

    // 2) Remove rows in tables with user-reference columns pointing to seeded users
    if (!empty($seededUserIds)) {
        $userRefColumns = [
            'user_id',
            'buyer_id',
            'seller_id',
            'sender_id',
            'recipient_id',
            'provider_id',
            'created_by',
            'updated_by',
            'approved_by',
            'reviewed_by',
            'referrer_id',
            'referred_user_id',
        ];

        $tables = Schema::getTableListing();

        // Keep users table for final step
        $skipTables = [
            'users',
            'migrations',
        ];

        foreach ($tables as $table) {
            if (in_array($table, $skipTables, true)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            foreach ($userRefColumns as $column) {
                if (in_array($column, $columns, true)) {
                    deleteByIn($table, $column, $seededUserIds, $summary, $dryRun);
                }
            }
        }

        // 3) Final delete: seeded users only, never protected emails
        if (Schema::hasTable('users')) {
            $query = DB::table('users')
                ->whereIn('id', $seededUserIds)
                ->whereNotIn('email', $protectedEmails);

            $count = (int) $query->count();
            if ($count > 0 && !$dryRun) {
                $query->delete();
            }

            if ($count > 0) {
                $summary['deleted_rows']['users.id'] = ($summary['deleted_rows']['users.id'] ?? 0) + $count;
            }
        }
    }
});

echo "\nCleanup summary:\n";
if (empty($summary['deleted_rows'])) {
    echo " - No rows matched for deletion.\n";
} else {
    foreach ($summary['deleted_rows'] as $key => $count) {
        echo " - {$key}: {$count}\n";
    }
}

echo $dryRun
    ? "\nDRY RUN complete. No data was deleted.\n"
    : "\nCleanup complete. Seeded data removed; protected admin preserved.\n";
