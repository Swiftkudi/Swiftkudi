<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this->subject('SwiftKudi SMTP Test Email')
                    ->view('emails.test')
                    ->with(['message' => 'This is a test email sent from SwiftKudi to verify SMTP settings.']);
    }
}
