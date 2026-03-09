<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendActivationReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swiftkudi:send-activation-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send activation reminder emails to users who haven\'t completed activation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting activation reminder process...');

        $now = Carbon::now();

        // Send first reminder (6 hours after registration)
        $this->sendFirstReminders($now);

        // Send second reminder (24 hours after registration)
        $this->sendSecondReminders($now);

        // Send third reminder (48 hours after registration)
        $this->sendThirdReminders($now);

        $this->info('Activation reminder process completed.');
        return Command::SUCCESS;
    }

    /**
     * Send first reminder to users registered 6 hours ago.
     */
    protected function sendFirstReminders(Carbon $now): void
    {
        $threshold = $now->copy()->subHours(6);

        $users = User::where('created_at', '>=', $threshold)
            ->where('created_at', '<', $now->copy()->subHours(5)->subMinute())
            ->where('wallet_balance', '<', config('swiftkudi.activation_fee', 1000))
            ->whereDoesntHave('notifications', function ($query) {
                $query->where('type', 'activation_reminder');
            })
            ->get();

        $this->info("Sending first reminders to {$users->count()} users...");

        foreach ($users as $user) {
            try {
                app(\App\Services\NotificationDispatchService::class)->sendToUser(
                    $user,
                    'Complete Your Registration - Tasks Await! 🎯',
                    'You registered on SwiftKudi but haven\'t completed your activation yet. Activate now to start completing tasks and earning real money.',
                    'activation_reminder',
                    ['reminder_type' => 'first', 'action_url' => route('wallet.activate')]
                );
                Log::info("First activation reminder sent to user {$user->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send first reminder to user {$user->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Send second reminder to users registered 24 hours ago.
     */
    protected function sendSecondReminders(Carbon $now): void
    {
        $threshold = $now->copy()->subHours(24);

        $users = User::where('created_at', '>=', $threshold)
            ->where('created_at', '<', $now->copy()->subHours(23)->subMinute())
            ->where('wallet_balance', '<', config('swiftkudi.activation_fee', 1000))
            ->whereHas('notifications', function ($query) {
                $query->where('type', 'activation_reminder')
                    ->where('data', 'LIKE', '%"reminder_type":"first"%');
            })
            ->whereDoesntHave('notifications', function ($query) {
                $query->where('type', 'activation_reminder')
                    ->where('data', 'LIKE', '%"reminder_type":"second"%');
            })
            ->get();

        $this->info("Sending second reminders to {$users->count()} users...");

        foreach ($users as $user) {
            try {
                app(\App\Services\NotificationDispatchService::class)->sendToUser(
                    $user,
                    'High-Paying Tasks Available Now! 💰',
                    'We noticed you haven\'t activated your account yet. High-paying tasks are available now—activate and start earning.',
                    'activation_reminder',
                    ['reminder_type' => 'second', 'action_url' => route('wallet.activate')]
                );
                Log::info("Second activation reminder sent to user {$user->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send second reminder to user {$user->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Send third reminder to users registered 48 hours ago.
     */
    protected function sendThirdReminders(Carbon $now): void
    {
        $threshold = $now->copy()->subHours(48);

        $users = User::where('created_at', '>=', $threshold)
            ->where('created_at', '<', $now->copy()->subHours(47)->subMinute())
            ->where('wallet_balance', '<', config('swiftkudi.activation_fee', 1000))
            ->whereHas('notifications', function ($query) {
                $query->where('type', 'activation_reminder')
                    ->where('data', 'LIKE', '%"reminder_type":"second"%');
            })
            ->whereDoesntHave('notifications', function ($query) {
                $query->where('type', 'activation_reminder')
                    ->where('data', 'LIKE', '%"reminder_type":"third"%');
            })
            ->get();

        $this->info("Sending third reminders to {$users->count()} users...");

        foreach ($users as $user) {
            try {
                app(\App\Services\NotificationDispatchService::class)->sendToUser(
                    $user,
                    'Join Thousands Earning on SwiftKudi - Last Chance! 🚀',
                    'Last reminder before you miss out. Activate your account now and start earning from available tasks.',
                    'activation_reminder',
                    ['reminder_type' => 'third', 'action_url' => route('wallet.activate')]
                );
                Log::info("Third activation reminder sent to user {$user->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send third reminder to user {$user->id}: {$e->getMessage()}");
            }
        }
    }
}
