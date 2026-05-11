<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendUserNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;
    public $backoff = [5, 15];

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected User $user,
        protected string $title,
        protected string $message,
        protected string $type,
        protected array $data = [],
        protected string $event = '',
        protected bool $notifyAdmins = false,
        protected bool $sendInApp = true,
        protected bool $sendEmail = true,
        protected bool $sendPush = false
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationDispatchService $dispatchService): void
    {
        // Send through enabled channels
        $dispatchService->sendToUser(
            $this->user,
            $this->title,
            $this->message,
            $this->type,
            $this->data,
            $this->event,
            $this->notifyAdmins,
            $this->sendInApp,
            $this->sendEmail
        );

        // Send push notification if enabled
        if ($this->sendPush) {
            $dispatchService->sendPushToUser($this->user, $this->title, $this->message, $this->data);
        }

        Log::info("Notification sent", [
            'event' => $this->event,
            'user_id' => $this->user->id,
            'channels' => [
                'in_app' => $this->sendInApp,
                'email' => $this->sendEmail,
                'push' => $this->sendPush,
            ],
            'data' => $this->data
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Notification job failed', [
            'event' => $this->event,
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
