<?php

namespace App\Services;

use App\Models\Notification as AppNotification;
use App\Models\PushSubscription;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class NotificationDispatchService
{
    public function createInAppNotification(
        User $user,
        string $title,
        string $message,
        string $type = AppNotification::TYPE_SYSTEM,
        array $data = []
    ): ?AppNotification {
        try {
            return AppNotification::sendTo($user, $title, $message, $type, $data);
        } catch (\Throwable $e) {
            Log::warning('In-app notification dispatch failed', [
                'user_id' => $user->id,
                'title' => $title,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function sendToUser(
        User $user,
        string $title,
        string $message,
        string $type = AppNotification::TYPE_SYSTEM,
        array $data = [],
        ?string $settingKey = null,
        bool $notifyAdmins = false,
        bool $sendInApp = true,
        bool $sendEmail = true
    ): void {
        $userEventEnabled = !$settingKey || SystemSetting::getBool($settingKey, true);
        $inAppEnabled = SystemSetting::getBool('notify_in_app_enabled', true);
        $emailEnabled = SystemSetting::getBool('notify_email_enabled', true);

        if ($userEventEnabled && $sendInApp && $inAppEnabled) {
            $this->sendInApp($user, $title, $message, $type, $data);
        }

        if ($userEventEnabled && $sendEmail && $emailEnabled) {
            [$emailSubject, $emailMessage] = $this->resolveEmailTemplate($settingKey, $title, $message, $user, $data);
            $this->sendEmail($user, $emailSubject, $emailMessage);
        }

        if ($userEventEnabled) {
            $this->sendPush($user, $title, $message, $data);
        }

        if ($notifyAdmins || SystemSetting::getBool('notify_admin_all_activity', true)) {
            $this->notifyAdmins(
                'Activity: ' . $title,
                $message,
                array_merge($data, ['target_user_id' => $user->id]),
                $user->id,
                !$notifyAdmins
            );
        }
    }

    public function notifyAdmins(string $title, string $message, array $data = [], ?int $excludeUserId = null, bool $respectGlobalSetting = true): void
    {
        if ($respectGlobalSetting && !SystemSetting::getBool('notify_admin_all_activity', true)) {
            return;
        }

        $inAppEnabled = SystemSetting::getBool('notify_in_app_enabled', true);
        $emailEnabled = SystemSetting::getBool('notify_email_enabled', true);

        $admins = User::query()
            ->where(function ($query) {
                $query->where('is_admin', true)
                    ->orWhereNotNull('admin_role_id');
            })
            ->get();

        foreach ($admins as $admin) {
            if (!$admin instanceof User) {
                continue;
            }

            if ($excludeUserId !== null && (int) $admin->id === $excludeUserId) {
                continue;
            }

            if ($inAppEnabled) {
                $this->sendInApp($admin, $title, $message, AppNotification::TYPE_SYSTEM, $data);
            }

            if ($emailEnabled) {
                $this->sendEmail($admin, $title, $message);
            }

            $this->sendPush($admin, $title, $message, $data);
        }
    }

    protected function resolveEmailTemplate(?string $settingKey, string $title, string $message, User $user, array $data = []): array
    {
        if (!$settingKey) {
            return $this->normalizeEmailContent($title, $message);
        }

        $templateMap = [
            'notify_task_approval'  => ['subject' => 'notif_task_approved_subject',  'body' => 'notif_task_approved_body'],
            'notify_task_rejection' => ['subject' => 'notif_task_rejected_subject',  'body' => 'notif_task_rejected_body'],
            'notify_referral_bonus' => ['subject' => 'notif_referral_bonus_subject', 'body' => 'notif_referral_bonus_body'],
            'notify_withdrawal'     => ['subject' => 'notif_withdrawal_subject',      'body' => 'notif_withdrawal_body'],
            'notify_task_bundle'    => ['subject' => 'notif_task_bundle_subject',    'body' => 'notif_task_bundle_body'],
            'notify_task_created'   => ['subject' => 'notif_task_created_subject',   'body' => 'notif_task_created_body'],
        ];

        $mapping = $templateMap[$settingKey] ?? null;
        if (!$mapping) {
            return $this->normalizeEmailContent($title, $message);
        }

        $subjectTemplate = (string) SystemSetting::get($mapping['subject'], $title);
        $bodyTemplate = (string) SystemSetting::get($mapping['body'], $message);

        $replacements = [
            '{{site_name}}' => (string) config('app.name', 'SwiftKudi'),
            '{{user_name}}' => (string) ($user->name ?? ''),
            '{{email}}' => (string) ($user->email ?? ''),
            '{{amount}}' => (string) ($data['amount'] ?? ''),
            '{{wallet_balance}}' => (string) ($data['wallet_balance'] ?? ''),
            '{{task_title}}' => (string) ($data['task_title'] ?? $data['title'] ?? $data['task_name'] ?? ''),
            '{{earnings}}' => (string) ($data['earnings'] ?? ''),
            '{{rejection_reason}}' => (string) ($data['reason'] ?? ''),
            '{{method}}' => (string) ($data['method'] ?? ''),
            '{{net_amount}}' => (string) ($data['net_amount'] ?? ''),
            '{{bonus_amount}}' => (string) ($data['bonus_amount'] ?? ''),
            '{{referred_user}}' => (string) ($data['referred_user'] ?? ''),
            '{{referral_code}}' => (string) ($data['referral_code'] ?? ''),
            '{{task_url}}' => (string) ($data['action_url'] ?? ''),
        ];

        $resolvedSubject = trim(strtr($subjectTemplate, $replacements));
        $resolvedBody = trim(strtr($bodyTemplate, $replacements));

        [$demoSubject, $demoBody] = $this->getDemoNotificationContent($settingKey, $title, $message);

        return $this->normalizeEmailContent(
            $resolvedSubject !== '' ? $resolvedSubject : $demoSubject,
            $resolvedBody !== '' ? $resolvedBody : $demoBody
        );
    }

    protected function sendInApp(User $user, string $title, string $message, string $type, array $data = []): void
    {
        $this->createInAppNotification($user, $title, $message, $type, $data);
    }

    /**
     * Public alias so controllers can trigger a push directly.
     */
    public function sendPushToUser(User $user, string $title, string $message, array $data = []): void
    {
        $this->sendPush($user, $title, $message, $data);
    }

    /**
     * Send a Web Push notification to all subscribed browsers of this user.
     */
    protected function sendPush(User $user, string $title, string $message, array $data = []): void
    {
        $publicKey  = config('services.vapid.public_key');
        $privateKey = config('services.vapid.private_key');
        $subject    = config('services.vapid.subject');

        if (empty($publicKey) || empty($privateKey)) {
            // VAPID keys not configured — skip silently
            return;
        }

        $subscriptions = PushSubscription::where('user_id', $user->id)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject'    => $subject,
                    'publicKey'  => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ]);
            $webPush->setReuseVAPIDHeaders(true);

            $payload = json_encode([
                'title' => $title,
                'body'  => $message,
                'icon'  => '/favicon.svg',
                'badge' => '/favicon.ico',
                'url'   => $data['action_url'] ?? $data['url'] ?? '/dashboard',
            ]);

            $staleEndpoints = [];

            foreach ($subscriptions as $sub) {
                $subscription = Subscription::create([
                    'endpoint'        => $sub->endpoint,
                    'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
                    'keys' => [
                        'p256dh' => $sub->p256dh,
                        'auth'   => $sub->auth_token,
                    ],
                ]);

                $report = $webPush->sendOneNotification($subscription, $payload);

                // Remove subscription if the endpoint is gone (410) or expired (404)
                if ($report instanceof \Minishlink\WebPush\MessageSentReport) {
                    $statusCode = $report->getResponse() ? $report->getResponse()->getStatusCode() : null;
                    if (in_array($statusCode, [404, 410], true)) {
                        $staleEndpoints[] = hash('sha256', $sub->endpoint);
                    }
                }
            }

            if (!empty($staleEndpoints)) {
                PushSubscription::where('user_id', $user->id)
                    ->whereIn('endpoint_hash', $staleEndpoints)
                    ->delete();
            }
        } catch (\Throwable $e) {
            Log::warning('Web push notification failed', [
                'user_id' => $user->id,
                'title'   => $title,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    protected function sendEmail(User $user, string $subject, string $message): void
    {
        if (!SystemSetting::getBool('smtp_enabled', false) || empty($user->email)) {
            return;
        }

        try {
            $this->applyMailConfigFromSettings();
            [$subject, $message] = $this->normalizeEmailContent($subject, $message);
            $html = $this->buildHtmlEmail($subject, $message, $user);

            Mail::send([], [], function ($mail) use ($user, $subject, $html) {
                $mail->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($user->email)
                    ->subject($subject)
                    ->setBody($html, 'text/html');
            });
        } catch (\Throwable $e) {
            Log::warning('Email notification dispatch failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function normalizeEmailContent(string $subject, string $message): array
    {
        $subject = trim($subject);
        $message = trim($message);

        if ($subject === '') {
            $subject = 'SwiftKudi Notification Update';
        }

        if ($message === '') {
            $message = "Hello,\n\nThis is a notification update from " . config('app.name', 'SwiftKudi') . ".\n\nPlease log in to your dashboard for full details.";
        }

        return [$subject, $message];
    }

    protected function getDemoNotificationContent(?string $settingKey, string $fallbackTitle, string $fallbackMessage): array
    {
        $siteName = (string) config('app.name', 'SwiftKudi');

        $map = [
            'notify_task_created' => [
                'subject' => 'Your Task Has Been Created Successfully!',
                'body' => "Hello,\n\nYour task has been created successfully on {$siteName}.\n\nYou can now monitor submissions from your dashboard.",
            ],
            'notify_task_bundle' => [
                'subject' => 'New Task Bundle Available',
                'body' => "Hello,\n\nA new task bundle is now available on {$siteName}.\n\nLog in now and start earning.",
            ],
            'notify_task_approval' => [
                'subject' => 'Task Submission Approved',
                'body' => "Hello,\n\nGreat news! Your task submission has been approved.\n\nKeep up the great work.",
            ],
            'notify_task_rejection' => [
                'subject' => 'Task Submission Rejected',
                'body' => "Hello,\n\nYour task submission was rejected.\n\nPlease check the feedback and resubmit.",
            ],
            'notify_withdrawal' => [
                'subject' => 'Withdrawal Status Update',
                'body' => "Hello,\n\nThere is an update on your withdrawal request.\n\nPlease check your wallet page for details.",
            ],
            'notify_referral_bonus' => [
                'subject' => 'Referral Bonus Earned',
                'body' => "Hello,\n\nCongratulations! You earned a referral bonus on {$siteName}.\n\nKeep sharing your referral link.",
            ],
        ];

        $entry = $settingKey && isset($map[$settingKey]) ? $map[$settingKey] : null;

        return [
            (string) ($entry['subject'] ?? $fallbackTitle),
            (string) ($entry['body'] ?? $fallbackMessage),
        ];
    }

    protected function buildHtmlEmail(string $subject, string $message, User $user): string
    {
        $siteName = e((string) config('app.name', 'SwiftKudi'));
        $recipientName = e((string) ($user->name ?: 'there'));
        $safeSubject = e($subject);
        $safeMessage = nl2br(e($message));
        $dashboardUrl = e((string) url('/dashboard'));
        $year = date('Y');

        return '<!doctype html>' .
            '<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>' .
            '<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">' .
            '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 12px;">' .
            '<tr><td align="center">' .
            '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border-radius:12px;overflow:hidden;">' .
            '<tr><td style="background:#4f46e5;padding:20px 24px;color:#ffffff;font-size:20px;font-weight:700;">' . $siteName . '</td></tr>' .
            '<tr><td style="padding:24px;">' .
            '<h2 style="margin:0 0 12px 0;font-size:20px;line-height:1.3;color:#111827;">' . $safeSubject . '</h2>' .
            '<p style="margin:0 0 14px 0;font-size:14px;color:#374151;">Hello ' . $recipientName . ',</p>' .
            '<div style="font-size:15px;line-height:1.7;color:#111827;">' . $safeMessage . '</div>' .
            '<div style="margin-top:24px;">' .
            '<a href="' . $dashboardUrl . '" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:8px;font-size:14px;font-weight:600;">Open Dashboard</a>' .
            '</div>' .
            '</td></tr>' .
            '<tr><td style="padding:16px 24px;background:#f9fafb;font-size:12px;color:#6b7280;">© ' . $year . ' ' . $siteName . '. This is an automated notification email.</td></tr>' .
            '</table>' .
            '</td></tr>' .
            '</table>' .
            '</body></html>';
    }

    protected function applyMailConfigFromSettings(): void
    {
        $selectedDriver = SystemSetting::get('smtp_driver', config('mail.default'));
        $isTurbo = $selectedDriver === 'turbosmtp';
        $driver = $isTurbo ? 'smtp' : $selectedDriver;

        $host = SystemSetting::get('smtp_host', config('mail.mailers.smtp.host'));
        if (empty($host) && $isTurbo) {
            $host = config('services.turbosmtp.server', $host);
        }

        $port = SystemSetting::getNumber('smtp_port', config('mail.mailers.smtp.port'));
        if ((empty($port) || $port <= 0) && $isTurbo) {
            $port = (int) config('services.turbosmtp.port', 587);
        }

        $username = SystemSetting::get('smtp_username', config('mail.mailers.smtp.username'));
        if (empty($username) && $isTurbo) {
            $username = config('services.turbosmtp.username', $username);
        }

        $password = SystemSetting::getDecrypted('smtp_password', config('mail.mailers.smtp.password'));
        if (empty($password) && $isTurbo) {
            $password = config('services.turbosmtp.password', $password);
        }

        $encryption = strtolower((string) SystemSetting::get('smtp_encryption', config('mail.mailers.smtp.encryption')));
        if (in_array($encryption, ['', 'none', 'null'], true)) {
            $encryption = null;
        }

        $port = (int) $port;
        if ($port <= 0) {
            $port = $encryption === 'ssl' ? 465 : 587;
        }

        if ($encryption === 'ssl' && $port === 587) {
            $port = 465;
        }
        if (($encryption === 'tls' || $encryption === null) && $port === 465) {
            $port = 587;
        }
        $fromAddress = SystemSetting::get('smtp_from_email', config('mail.from.address'));
        $fromName = SystemSetting::get('smtp_from_name', config('mail.from.name'));

        Config::set('mail.default', $driver);
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', $port);
        Config::set('mail.mailers.smtp.username', $username);
        Config::set('mail.mailers.smtp.password', $password);
        Config::set('mail.mailers.smtp.encryption', $encryption);
        Config::set('mail.mailers.smtp.timeout', 30);
        Config::set('mail.mailers.smtp.auth_mode', null);
        Config::set('mail.from.address', $fromAddress);
        Config::set('mail.from.name', $fromName);

        app()->forgetInstance('mail.manager');
        app()->forgetInstance('mailer');
    }
}
