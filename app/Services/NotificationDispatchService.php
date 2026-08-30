<?php

namespace App\Services;

use App\Models\Notification as AppNotification;
use App\Models\PushSubscription;
use App\Models\NotificationPreference;
use App\Models\NotificationDigestEntry;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
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
        bool $sendEmail = true,
        bool $sendPush = false
    ): void {
        $data = $this->sanitizeActionData($data);
        $userEventEnabled = !$settingKey || SystemSetting::getBool($settingKey, true);
        $inAppEnabled = SystemSetting::getBool('notify_in_app_enabled', true);
        $emailEnabled = SystemSetting::getBool('notify_email_enabled', true);
        $pushEnabled = SystemSetting::getBool('notify_push_enabled', true);
        $preference = NotificationPreference::forUser($user);
        $category = $this->resolvePreferenceCategory($settingKey, $data);

        if ($userEventEnabled && $sendInApp && $inAppEnabled &&
            $preference->channelEnabled($category, NotificationPreference::CHANNEL_IN_APP)) {
            $this->sendInApp($user, $title, $message, $type, $data);
        }

        if ($userEventEnabled && $sendEmail && $emailEnabled &&
            $preference->channelEnabled($category, NotificationPreference::CHANNEL_EMAIL)) {
            [$emailSubject, $emailMessage] = $this->resolveEmailTemplate($settingKey, $title, $message, $user, $data);
            $frequency = (string) ($preference->email_frequency ?: 'instant');

            if ($frequency !== 'instant' && !$this->mustDeliverImmediately($category)) {
                $this->queueDigestEntry($user, $frequency, $category, $emailSubject, $emailMessage, $data);
            } else {
                \App\Jobs\SendUserEmail::dispatch(
                    $user,
                    $emailSubject,
                    $emailMessage,
                    $data['action_url'] ?? $data['url'] ?? null
                )->onQueue('notifications');
            }
        }

        if ($userEventEnabled && $sendPush && $pushEnabled &&
            $preference->channelEnabled($category, NotificationPreference::CHANNEL_PUSH)) {
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

        // Cache admin users for 5 minutes to avoid repeated DB queries
        $admins = \Illuminate\Support\Facades\Cache::remember('admin_users', 300, function () {
            return User::query()
                ->where(function ($query) {
                    $query->where('is_admin', true)
                        ->orWhereNotNull('admin_role_id');
                })
                ->get();
        });

        foreach ($admins as $admin) {
            if (!$admin instanceof User) {
                continue;
            }

            if ($excludeUserId !== null && (int) $admin->id === $excludeUserId) {
                continue;
            }

            $preference = NotificationPreference::forUser($admin);

            if ($inAppEnabled && $preference->channelEnabled('system', NotificationPreference::CHANNEL_IN_APP)) {
                $this->sendInApp($admin, $title, $message, AppNotification::TYPE_SYSTEM, $data);
            }

            if ($emailEnabled && $preference->channelEnabled('system', NotificationPreference::CHANNEL_EMAIL)) {
                $this->sendEmail($admin, $title, $message);
            }

            if (SystemSetting::getBool('notify_push_enabled', true) &&
                $preference->channelEnabled('system', NotificationPreference::CHANNEL_PUSH)) {
                $this->sendPush($admin, $title, $message, $data);
            }
        }
    }


    /**
     * Resolve legacy setting keys and modern event names to user-facing preference groups.
     */
    protected function resolvePreferenceCategory(?string $settingKey, array $data = []): string
    {
        $event = strtolower((string) ($data['event'] ?? $settingKey ?? 'system'));

        if (str_contains($event, 'chat') || str_contains($event, 'message')) {
            return 'messages';
        }
        if (str_contains($event, 'application') || str_contains($event, 'applicant') || str_contains($event, 'proposal')) {
            return 'proposals';
        }
        if (str_contains($event, 'contract') || str_contains($event, 'milestone') || str_contains($event, 'service_order') ||
            str_contains($event, 'service_delivered') || str_contains($event, 'revision')) {
            return 'contracts';
        }
        if (str_contains($event, 'escrow') || str_contains($event, 'withdrawal') || str_contains($event, 'earnings') ||
            str_contains($event, 'payment') || str_contains($event, 'wallet')) {
            return 'payments';
        }
        if (str_contains($event, 'review')) {
            return 'reviews';
        }
        if (str_contains($event, 'dispute')) {
            return 'disputes';
        }
        if (str_contains($event, 'activation') || str_contains($event, 'account') || str_contains($event, 'security') ||
            str_contains($event, 'password') || str_contains($event, 'verification') || str_contains($event, 'login')) {
            return 'security';
        }
        if (str_contains($event, 'bundle') || str_contains($event, 'recommend') || str_contains($event, 'marketing')) {
            return 'marketing';
        }
        if (str_starts_with($event, 'job_') || str_contains($event, 'job')) {
            return 'jobs';
        }

        return 'system';
    }

    /**
     * Time-sensitive marketplace and account notices stay instant even when the user
     * chooses a digest for low-priority email.
     */
    protected function mustDeliverImmediately(string $category): bool
    {
        return in_array($category, ['messages', 'proposals', 'contracts', 'payments', 'disputes', 'security'], true);
    }

    protected function queueDigestEntry(User $user, string $frequency, string $category, string $title, string $message, array $data = []): void
    {
        if (!in_array($frequency, ['daily', 'weekly'], true)) {
            $frequency = 'daily';
        }

        try {
            NotificationDigestEntry::create([
                'user_id' => $user->id,
                'frequency' => $frequency,
                'category' => $category,
                'title' => $title,
                'message' => $message,
                'action_url' => $data['action_url'] ?? $data['url'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Notification digest entry could not be stored; falling back to instant email.', [
                'user_id' => $user->id,
                'category' => $category,
                'error' => $e->getMessage(),
            ]);

            \App\Jobs\SendUserEmail::dispatch(
                $user,
                $title,
                $message,
                $data['action_url'] ?? $data['url'] ?? null
            )->onQueue('notifications');
        }
    }


    /**
     * Keep notification deep links on this application. Notification payloads can
     * be populated by multiple workflows, so validate once before any channel
     * (in-app, email or push) receives the URL.
     */
    protected function sanitizeActionData(array $data): array
    {
        foreach (['action_url', 'url'] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $value = is_string($data[$key]) ? trim($data[$key]) : '';
            if ($value === '' || strlen($value) > 2048) {
                unset($data[$key]);
                continue;
            }

            if (str_starts_with($value, '/') && !str_starts_with($value, '//')) {
                $data[$key] = $value;
                continue;
            }

            $parts = parse_url($value);
            $appParts = parse_url((string) config('app.url'));
            $scheme = strtolower((string) ($parts['scheme'] ?? ''));
            $sameHost = !empty($parts['host']) && !empty($appParts['host'])
                && strcasecmp((string) $parts['host'], (string) $appParts['host']) === 0;
            $samePort = (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80))
                === (int) ($appParts['port'] ?? (strtolower((string) ($appParts['scheme'] ?? '')) === 'https' ? 443 : 80));

            if (!in_array($scheme, ['http', 'https'], true) || !$sameHost || !$samePort) {
                unset($data[$key]);
            }
        }

        return $data;
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
    /**
     * Public push entry point used by queued jobs, admin broadcasts and test delivery.
     */
    public function sendPushToUser(User $user, string $title, string $message, array $data = []): void
    {
        $this->sendPush($user, $title, $message, $this->sanitizeActionData($data));
    }

    /**
     * Send push notification to multiple users (batched for scalability)
     */
    public function sendPushToUsers(array $userIds, string $title, string $message, array $data = []): void
    {
        if (empty($userIds)) {
            return;
        }

        // For small batches, process immediately
        if (count($userIds) <= 5) {
            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    $this->sendPushToUser($user, $title, $message, $data);
                }
            }
            return;
        }

        // For large batches, queue the job
        \App\Jobs\SendBulkPushNotifications::dispatch($userIds, $title, $message, $data);
    }

    /**
     * Send a Web Push notification to all subscribed browsers of this user.
     */
    protected function sendPush(User $user, string $title, string $message, array $data = []): void
    {
        $publicKey  = config('services.vapid.public_key');
        $privateKey = config('services.vapid.private_key');
        $subject    = (string) config('services.vapid.subject');

        if ($subject === '' || $subject === 'mailto:' || $subject === 'mailto') {
            $mailFrom = (string) config('mail.from.address');
            $subject = $mailFrom !== ''
                ? 'mailto:' . $mailFrom
                : (string) config('app.url', 'http://localhost');
        }

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

        [$subject, $message] = $this->normalizeEmailContent($subject, $message);
        \App\Jobs\SendUserEmail::dispatch($user, $subject, $message)->onQueue('notifications');
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
}
