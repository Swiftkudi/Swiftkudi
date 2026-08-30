<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MarketplaceNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $recipient,
        public string $notificationSubject,
        public string $notificationMessage,
        public ?string $actionUrl = null,
        public string $actionLabel = 'Open SwiftKudi'
    ) {
    }

    public function build()
    {
        $mail = $this->subject($this->notificationSubject)
            ->view('emails.marketplace-notification')
            ->text('emails.marketplace-notification-text')
            ->with([
                'recipient' => $this->recipient,
                'subjectLine' => $this->notificationSubject,
                'messageText' => $this->notificationMessage,
                'actionUrl' => $this->actionUrl ?: url('/dashboard'),
                'actionLabel' => $this->actionLabel,
            ]);

        return $mail->withSwiftMessage(function ($message) {
            $headers = $message->getHeaders();
            if (!$headers->has('X-Auto-Response-Suppress')) {
                $headers->addTextHeader('X-Auto-Response-Suppress', 'OOF, AutoReply');
            }
            if (!$headers->has('X-Entity-Ref-ID')) {
                $headers->addTextHeader('X-Entity-Ref-ID', 'swiftkudi-' . sha1($this->recipient->id . '|' . $this->notificationSubject . '|' . now()->timestamp));
            }
        });
    }
}
