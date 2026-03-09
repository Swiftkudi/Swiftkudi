<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class BackfillAdminEmailVerification extends Command
{
    protected $signature = 'admins:verify-email {--dry-run : Preview how many admin users will be marked as verified}';

    protected $description = 'Backfill email verification for existing admin users';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $query = User::query()
            ->whereNull('email_verified_at')
            ->where(function ($builder) {
                $builder->where('is_admin', true)
                    ->orWhereNotNull('admin_role_id');
            });

        $count = (int) $query->count();

        if ($count === 0) {
            $this->info('No unverified admin users found.');
            return self::SUCCESS;
        }

        $this->line("Unverified admin users found: {$count}");

        if ($dryRun) {
            $this->warn('Dry run complete. No users were updated.');
            return self::SUCCESS;
        }

        $updated = $query->update(['email_verified_at' => now()]);

        $this->info("Done. Marked {$updated} admin user(s) as verified.");

        return self::SUCCESS;
    }
}
