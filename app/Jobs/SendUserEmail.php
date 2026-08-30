<?php

namespace App\Jobs;

use App\Mail\MarketplaceNotificationMail;
use App\Models\EmailDeliveryLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\MailConfigurationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendUserEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 4;
    public $timeout = 45;
    public $backoff = [10, 30, 120];

    protected string $correlationId;

    public function __construct(
        protected User $user,
        protected string $subject,
        protected string $message,
        protected ?string $actionUrl = null
    ) {
        $this->correlationId = (string) Str::uuid();
        $this->onQueue('notifications');
    }

    public function handle(MailConfigurationService $mailConfiguration): void
    {
        if (empty($this->user->email)) {
            $this->record('skipped', 'Recipient has no email address.');
            return;
        }

        if (!SystemSetting::getBool('smtp_enabled', false)) {
            $this->record('skipped', 'SMTP delivery is disabled.');
            return;
        }

        if (!$mailConfiguration->apply()) {
            $this->record('failed', 'SMTP configuration is incomplete or could not be applied.');
            return;
        }

        $this->record('sending');

        try {
            Mail::to($this->user->email)->send(new MarketplaceNotificationMail(
                $this->user,
                trim($this->subject) ?: 'SwiftKudi notification',
                trim($this->message) ?: 'There is an update on your SwiftKudi account.',
                $this->actionUrl
            ));

            $this->record('sent', null, true);
        } catch (\Throwable $e) {
            $this->record('retrying', $e->getMessage());
            Log::warning('Email notification failed', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'subject' => $this->subject,
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->record('failed', $exception->getMessage());
        Log::error('Email notification job exhausted retries', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'subject' => $this->subject,
            'correlation_id' => $this->correlationId,
            'error' => $exception->getMessage(),
        ]);
    }

    protected function record(string $status, ?string $error = null, bool $sent = false): void
    {
        try {
            EmailDeliveryLog::updateOrCreate(
                ['correlation_id' => $this->correlationId],
                [
                    'user_id' => $this->user->id,
                    'recipient_email' => (string) $this->user->email,
                    'subject' => Str::limit(trim($this->subject) ?: 'SwiftKudi notification', 255, ''),
                    'status' => $status,
                    'attempts' => max(1, (int) $this->attempts()),
                    'last_error' => $error ? Str::limit($error, 5000, '') : null,
                    'sent_at' => $sent ? now() : null,
                ]
            );
        } catch (\Throwable $logError) {
            Log::debug('Email delivery audit could not be written', [
                'correlation_id' => $this->correlationId,
                'error' => $logError->getMessage(),
            ]);
        }
    }
}
