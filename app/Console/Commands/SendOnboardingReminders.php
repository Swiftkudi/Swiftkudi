<?php

namespace App\Console\Commands;

use App\Services\AccountTypeService;
use Illuminate\Console\Command;

class SendOnboardingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onboarding:send-reminders 
                            {--dry-run : Run without actually sending notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send onboarding reminders to users without an account type';

    /**
     * Execute the console command.
     */
    public function handle(AccountTypeService $accountTypeService): int
    {
        $this->info('Starting onboarding reminder process...');

        if ($this->option('dry-run')) {
            $this->warn('Running in dry-run mode - no notifications will be sent.');
            
            $users = \App\Models\User::whereNull('account_type')
                ->orWhere('account_type', '')
                ->where('is_suspended', false)
                ->whereNotNull('email_verified_at')
                ->count();
            
            $this->info("Found {$users} users without an account type that would receive reminders.");
            
            return Command::SUCCESS;
        }

        $sentCount = $accountTypeService->sendRemindersToUsersWithoutAccountType();

        $this->info("Successfully sent {$sentCount} onboarding reminders.");

        return Command::SUCCESS;
    }
}
