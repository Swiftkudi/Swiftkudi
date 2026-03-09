<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupSeededData extends Command
{
    protected $signature = 'cleanup:seeded {--dry-run : Preview what will be deleted without removing data}';

    protected $description = 'Remove seeded/demo data while preserving protected admin account(s)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $protectedEmails = [
            'admin@swiftkudi.com',
        ];

        $seededEmails = [
            'client@swiftkudi.com',
            'worker@swiftkudi.com',
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

        $seededUserIds = $seededUsers->pluck('id')->map(fn ($id) => (int) $id)->all();

        $sampleTaskIds = [];
        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks', 'is_sample')) {
            $sampleTaskIds = DB::table('tasks')
                ->where('is_sample', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        if (empty($seededUserIds) && empty($sampleTaskIds)) {
            $this->info('No seeded users/tasks found. Nothing to remove.');
            return self::SUCCESS;
        }

        $this->line('Seeded users found: ' . count($seededUserIds));
        $this->line('Sample tasks found: ' . count($sampleTaskIds));

        if ($seededUsers->isNotEmpty()) {
            $this->line('Target seeded emails:');
            foreach ($seededUsers as $user) {
                $note = !empty($user->is_admin) ? ' (admin flag set - still protected by email rules)' : '';
                $this->line(' - ' . $user->email . $note);
            }
        }

        if ($dryRun) {
            $this->warn('Running DRY RUN (no delete will happen).');
        } else {
            $this->info('Executing delete...');
        }

        $summary = [];

        DB::transaction(function () use (&$summary, $dryRun, $seededUserIds, $sampleTaskIds, $protectedEmails) {
            if (!empty($sampleTaskIds)) {
                $this->deleteByIn('task_completions', 'task_id', $sampleTaskIds, $summary, $dryRun);
                $this->deleteByIn('bundle_tasks', 'task_id', $sampleTaskIds, $summary, $dryRun);
                $this->deleteByIn('tasks', 'id', $sampleTaskIds, $summary, $dryRun);
            }

            if (!empty($seededUserIds)) {
                $userRefColumns = [
                    'user_id', 'buyer_id', 'seller_id', 'sender_id', 'recipient_id',
                    'provider_id', 'created_by', 'updated_by', 'approved_by',
                    'reviewed_by', 'referrer_id', 'referred_user_id',
                ];

                $tables = Schema::getTableListing();
                $skipTables = ['users', 'migrations'];

                foreach ($tables as $table) {
                    if (in_array($table, $skipTables, true)) {
                        continue;
                    }

                    $columns = Schema::getColumnListing($table);
                    foreach ($userRefColumns as $column) {
                        if (in_array($column, $columns, true)) {
                            $this->deleteByIn($table, $column, $seededUserIds, $summary, $dryRun);
                        }
                    }
                }

                if (Schema::hasTable('users')) {
                    $query = DB::table('users')
                        ->whereIn('id', $seededUserIds)
                        ->whereNotIn('email', $protectedEmails);

                    $count = (int) $query->count();
                    if ($count > 0 && !$dryRun) {
                        $query->delete();
                    }

                    if ($count > 0) {
                        $summary['users.id'] = ($summary['users.id'] ?? 0) + $count;
                    }
                }
            }
        });

        $this->line('');
        $this->line('Cleanup summary:');
        if (empty($summary)) {
            $this->line(' - No rows matched for deletion.');
        } else {
            foreach ($summary as $key => $count) {
                $this->line(" - {$key}: {$count}");
            }
        }

        $this->line('');
        if ($dryRun) {
            $this->info('DRY RUN complete. No data was deleted.');
        } else {
            $this->info('Cleanup complete. Seeded data removed; protected admin preserved.');
        }

        return self::SUCCESS;
    }

    private function deleteByIn(string $table, string $column, array $ids, array &$summary, bool $dryRun = false): void
    {
        if (empty($ids) || !Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $query = DB::table($table)->whereIn($column, $ids);
        $count = (int) $query->count();

        if ($count > 0 && !$dryRun) {
            $query->delete();
        }

        if ($count > 0) {
            $key = $table . '.' . $column;
            $summary[$key] = ($summary[$key] ?? 0) + $count;
        }
    }
}
