<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendUserEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;
    public $backoff = [5, 10];

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected User $user,
        protected string $subject,
        protected string $message
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Apply mail configuration from settings
        $enabled = config('mail.mailers.smtp.transport') ?? 'smtp';
        $isTurbo = $enabled === 'turbosmtp';
        $driver = $isTurbo ? 'smtp' : $enabled;

        $host = config('mail.mailers.smtp.host');
        if (empty($host) && $isTurbo) {
            $host = config('services.turbosmtp.server', $host);
        }

        $port = config('mail.mailers.smtp.port', 587);
        if ((empty($port) || $port <= 0) && $isTurbo) {
            $port = (int) config('services.turbosmtp.port', 587);
        }

        $username = config('mail.mailers.smtp.username');
        if (empty($username) && $isTurbo) {
            $username = config('services.turbosmtp.username', $username);
        }

        $password = config('mail.mailers.smtp.password');
        if (empty($password) && $isTurbo) {
            $password = config('services.turbosmtp.password', $password);
        }

        $encryption = strtolower((string) config('mail.mailers.smtp.encryption', 'tls'));
        if (in_array($encryption, ['', 'none', 'null'], true)) {
            $encryption = null;
        }

        config([
            'mail.mailer' => $driver,
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'password' => $password,
                'encryption' => $encryption,
                'timeout' => 30,
                'local_domain' => config('mail.mailers.smtp.local_domain', null),
            ],
            'mail.from.address' => config('mail.from.address'),
            'mail.from.name' => config('mail.from.name'),
        ]);

        try {
            Mail::send([], [], function ($mail) {
                $mail->from(config('mail.from.address'), config('mail.from.name'))
                     ->to($this->user->email)
                     ->subject($this->subject)
                     ->setBody($this->buildHtmlEmail($this->subject, $this->message), 'text/html');
            });
        } catch (\Throwable $e) {
            Log::warning('Email notification failed', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'subject' => $this->subject,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Build HTML email content.
     */
    protected function buildHtmlEmail(string $subject, string $message): string
    {
        return sprintf('
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>%s</title>
            </head>
            <body style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
                <div style="background: linear-gradient(135deg, #667eea 0%%, #764ba2 100%%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                    <h1 style="color: white; margin: 0; font-size: 24px;">%s</h1>
                </div>
                <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0;">
                    <p style="margin: 0 0 20px 0; font-size: 16px;">%s</p>
                    <p style="margin: 20px 0 0 0; font-size: 12px; color: #666;">
                        This email was sent to %s. If you did not expect this email, please ignore it.
                    </p>
                </div>
                <p style="text-align: center; font-size: 12px; color: #999; margin-top: 20px;">
                    &copy; %s %s. All rights reserved.
                </p>
            </body>
            </html>
        ', htmlspecialchars($subject), htmlspecialchars($subject), nl2br(htmlspecialchars($message)), htmlspecialchars($this->user->email), date('Y'), config('app.name', 'SwiftKudi'));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Email notification job failed', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'subject' => $this->subject,
            'error' => $exception->getMessage(),
        ]);
    }
}
