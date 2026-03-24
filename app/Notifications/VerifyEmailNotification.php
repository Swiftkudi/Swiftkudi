<?php

namespace App\Notifications;

use App\Models\SystemSetting;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    protected function buildMailMessage($url)
    {
        $subject = SystemSetting::get('notif_email_verify_subject', 'Verify Your Email Address');
        $bodyTemplate = SystemSetting::get(
            'notif_email_verify_body',
            "Hello {{user_name}},\n\nPlease verify your email address by clicking the link below:\n\n{{verify_link}}\n\nIf you did not create an account, please ignore this email."
        );

        $replacements = [
            '{{site_name}}' => config('app.name', 'SwiftKudi'),
            '{{user_name}}' => 'there',
            '{{verify_link}}' => $url,
        ];

        $bodyText = str_replace(array_keys($replacements), array_values($replacements), (string) $bodyTemplate);

        $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $bodyText))));

        $mail = (new MailMessage)->subject($subject)->greeting('Hello!');

        foreach ($lines as $line) {
            if (strpos($line, $url) !== false) {
                continue;
            }

            $mail->line($line);
        }

        $mail->action('Verify Email Address', $url)
            ->line('If you did not create an account, no further action is required.');

        return $mail;
    }
}
