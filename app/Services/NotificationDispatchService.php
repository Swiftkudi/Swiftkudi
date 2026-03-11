<?php

namespace App\Services;

use App\Models\Notification as AppNotification;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        if ($notifyAdmins || SystemSetting::getBool('notify_admin_all_activity', true)) {
            $this->notifyAdmins(
                'Activity: ' . $title,
                $message,
                array_merge($data, ['target_user_id' => $user->id]),
                $user->id
            );
        }
    }

    public function notifyAdmins(string $title, string $message, array $data = [], ?int $excludeUserId = null): void
    {
        if (!SystemSetting::getBool('notify_admin_all_activity', true)) {
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
        }
    }

    protected function resolveEmailTemplate(?string $settingKey, string $title, string $message, User $user, array $data = []): array
    {
        if (!$settingKey) {
            return [$title, $message];
        }

        $templateMap = [
            'notify_task_approval' => ['subject' => 'notif_task_approved_subject', 'body' => 'notif_task_approved_body'],
            'notify_task_rejection' => ['subject' => 'notif_task_rejected_subject', 'body' => 'notif_task_rejected_body'],
            'notify_referral_bonus' => ['subject' => 'notif_referral_bonus_subject', 'body' => 'notif_referral_bonus_body'],
            'notify_withdrawal' => ['subject' => 'notif_withdrawal_subject', 'body' => 'notif_withdrawal_body'],
            'notify_task_bundle' => ['subject' => 'notif_welcome_subject', 'body' => 'notif_welcome_body'],
        ];

        $mapping = $templateMap[$settingKey] ?? null;
        if (!$mapping) {
            return [$title, $message];
        }

        $subjectTemplate = (string) SystemSetting::get($mapping['subject'], $title);
        $bodyTemplate = (string) SystemSetting::get($mapping['body'], $message);

        $replacements = [
            '{{site_name}}' => (string) config('app.name', 'SwiftKudi'),
            '{{user_name}}' => (string) ($user->name ?? ''),
            '{{email}}' => (string) ($user->email ?? ''),
            '{{amount}}' => (string) ($data['amount'] ?? ''),
            '{{wallet_balance}}' => (string) ($data['wallet_balance'] ?? ''),
            '{{task_title}}' => (string) ($data['task_title'] ?? $data['title'] ?? ''),
            '{{earnings}}' => (string) ($data['earnings'] ?? ''),
            '{{rejection_reason}}' => (string) ($data['reason'] ?? ''),
            '{{method}}' => (string) ($data['method'] ?? ''),
            '{{net_amount}}' => (string) ($data['net_amount'] ?? ''),
            '{{bonus_amount}}' => (string) ($data['bonus_amount'] ?? ''),
            '{{referred_user}}' => (string) ($data['referred_user'] ?? ''),
            '{{referral_code}}' => (string) ($data['referral_code'] ?? ''),
            '{{task_url}}' => (string) ($data['action_url'] ?? ''),
        ];

        return [
            strtr($subjectTemplate, $replacements),
            strtr($bodyTemplate, $replacements),
        ];
    }

    protected function sendInApp(User $user, string $title, string $message, string $type, array $data = []): void
    {
        $this->createInAppNotification($user, $title, $message, $type, $data);
    }

    protected function sendEmail(User $user, string $subject, string $message): void
    {
        if (!SystemSetting::getBool('smtp_enabled', false) || empty($user->email)) {
            return;
        }

        try {
            $this->applyMailConfigFromSettings();

            Mail::raw($message, function ($mail) use ($user, $subject) {
                $mail->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($user->email)
                    ->subject($subject);
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
