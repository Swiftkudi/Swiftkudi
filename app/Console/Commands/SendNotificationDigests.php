<?php

namespace App\Console\Commands;

use App\Jobs\SendUserEmail;
use App\Models\NotificationDigestEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendNotificationDigests extends Command
{
    protected $signature = 'notifications:send-digests {frequency : daily or weekly}';
    protected $description = 'Queue grouped non-critical notification email digests.';

    public function handle(): int
    {
        $frequency = strtolower((string) $this->argument('frequency'));
        if (!in_array($frequency, ['daily', 'weekly'], true)) {
            $this->error('Frequency must be daily or weekly.');
            return self::INVALID;
        }

        $query = NotificationDigestEntry::query()
            ->where('frequency', $frequency)
            ->whereNull('sent_at')
            ->with('user')
            ->orderBy('user_id')
            ->orderBy('created_at');

        $processed = 0;
        $query->chunkById(250, function ($entries) use (&$processed, $frequency) {
            foreach ($entries->groupBy('user_id') as $userEntries) {
                $user = $userEntries->first()->user;
                if (!$user || empty($user->email)) {
                    $userEntries->each->update(['sent_at' => now()]);
                    continue;
                }

                $lines = [];
                foreach ($userEntries as $entry) {
                    $lines[] = '• ' . trim($entry->title) . ' — ' . trim($entry->message);
                    if ($entry->action_url) {
                        $lines[] = '  ' . $entry->action_url;
                    }
                }

                $subject = config('app.name', 'SwiftKudi') . ' ' . ucfirst($frequency) . ' notification digest';
                $message = "Here are your recent non-critical updates:\n\n" . implode("\n", $lines);

                SendUserEmail::dispatch($user, $subject, $message, route('notifications.index'))
                    ->onQueue('notifications');

                DB::transaction(function () use ($userEntries) {
                    NotificationDigestEntry::whereIn('id', $userEntries->pluck('id'))->update(['sent_at' => now()]);
                });
                $processed += $userEntries->count();
            }
        });

        // Retain a short audit window, but avoid unbounded growth.
        NotificationDigestEntry::whereNotNull('sent_at')
            ->where('sent_at', '<', now()->subDays(30))
            ->delete();

        $this->info("Queued {$processed} {$frequency} digest entries.");
        return self::SUCCESS;
    }
}
