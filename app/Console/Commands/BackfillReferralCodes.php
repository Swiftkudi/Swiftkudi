<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BackfillReferralCodes extends Command
{
    protected $signature = 'swiftkudi:backfill-referral-codes {--batch=100} {--dry-run}';
    protected $description = 'Generate referral_code for existing users that are missing one. Use --dry-run to preview changes.';

    public function handle()
    {
        $batch = (int) $this->option('batch') ?: 100;
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Starting backfill for referral codes' . ($dryRun ? ' (dry run)' : ''));

        $query = User::whereNull('referral_code')->orWhere('referral_code', '');
        $total = $query->count();

        if ($total === 0) {
            $this->info('No users require referral code generation.');
            return 0;
        }

        $this->info("Found {$total} users without referral codes. Processing in batches of {$batch}...");

        $processed = 0;

        $query->orderBy('id')->chunk($batch, function($users) use (&$processed, $dryRun) {
            foreach ($users as $user) {
                $preferred = $user->name ?: $user->email;
                $code = User::generateReferralCode($preferred);
                $this->line("#{$user->id} -> {$code}");
                if (!$dryRun) {
                    $user->referral_code = $code;
                    $user->save();
                }
                $processed++;
            }
        });

        $this->info("Completed. Processed {$processed} users.");
        return 0;
    }
}
