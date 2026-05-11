<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendBulkNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $event,
        public Collection $users,
        public array $data = [],
        public ?User $actor = null
    ) {}

    public function handle(NotificationManager $notificationManager): void
    {
        // Process in chunks to avoid memory issues
        $this->users->chunk(100)->each(function ($chunk) use ($notificationManager) {
            $notificationManager->notifyMultiple($this->event, $chunk, $this->data, $this->actor);
        });
    }
}