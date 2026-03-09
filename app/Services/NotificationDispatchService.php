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
        if ($settingKey && !SystemSetting::getBool($settingKey, true)) {
            return;
        }

        if ($sendInApp) {
            $this->sendInApp($user, $title, $message, $type, $data);
        }

        if ($sendEmail) {
            $this->sendEmail($user, $title, $message);
        }

        if ($notifyAdmins) {
            $this->notifyAdmins(
                'Activity: ' . $title,
                $message,
                array_merge($data, ['target_user_id' => $user->id])
            );
        }
    }

    public function notifyAdmins(string $title, string $message, array $data = []): void
    {
        if (!SystemSetting::getBool('notify_admin_all_activity', true)) {
            return;
        }

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

            $this->sendInApp($admin, $title, $message, AppNotification::TYPE_SYSTEM, $data);
            $this->sendEmail($admin, $title, $message);
        }
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

        $encryption = SystemSetting::get('smtp_encryption', config('mail.mailers.smtp.encryption'));
        $fromAddress = SystemSetting::get('smtp_from_email', config('mail.from.address'));
        $fromName = SystemSetting::get('smtp_from_name', config('mail.from.name'));

        Config::set('mail.default', $driver);
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', $port);
        Config::set('mail.mailers.smtp.username', $username);
        Config::set('mail.mailers.smtp.password', $password);
        Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
        Config::set('mail.from.address', $fromAddress);
        Config::set('mail.from.name', $fromName);

        app()->forgetInstance('mail.manager');
        app()->forgetInstance('mailer');
    }
}
