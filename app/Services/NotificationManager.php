<?php

namespace App\Services;

use App\Models\Notification as AppNotification;
use App\Models\SystemSetting;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Centralized Notification Manager
 *
 * Single entry point for all notification operations in the system.
 * Handles routing, configuration, and delivery across all channels.
 */
class NotificationManager
{
    private NotificationDispatchService $dispatchService;

    // Notification event types
    public const EVENT_TASK_APPROVED = 'task_approved';
    public const EVENT_TASK_REJECTED = 'task_rejected';
    public const EVENT_TASK_CREATED = 'task_created';
    public const EVENT_TASK_BUNDLE_AVAILABLE = 'task_bundle_available';
    public const EVENT_WITHDRAWAL_REQUESTED = 'withdrawal_requested';
    public const EVENT_WITHDRAWAL_STATUS = 'withdrawal_status';
    public const EVENT_LARGE_WITHDRAWAL_ALERT = 'large_withdrawal_alert';
    public const EVENT_REFERRAL_BONUS = 'referral_bonus';
    public const EVENT_EARNINGS_UNLOCKED = 'earnings_unlocked';
    public const EVENT_ACCOUNT_TYPE_REMINDER = 'account_type_reminder';
    public const EVENT_ACTIVATION_REMINDER = 'activation_reminder';
    public const EVENT_ACTIVATION_RETRY = 'activation_retry';
    public const EVENT_ONBOARDING_COMPLETE = 'onboarding_complete';
    public const EVENT_ONBOARDING_REMINDER = 'onboarding_reminder';
    public const EVENT_PRODUCT_PURCHASED = 'product_purchased';
    public const EVENT_PRODUCT_PURCHASED_SELLER = 'product_purchased_seller';
    public const EVENT_PRODUCT_PURCHASED_BUYER = 'product_purchased_buyer';
    public const EVENT_PRODUCT_DOWNLOADED = 'product_downloaded';
    public const EVENT_PRODUCT_CONFIRMED_SELLER = 'product_confirmed_seller';
    public const EVENT_PRODUCT_CONFIRMED_BUYER = 'product_confirmed_buyer';
    public const EVENT_PRODUCT_CREATED = 'product_created';
    public const EVENT_PRODUCT_APPROVED = 'product_approved';
    public const EVENT_PRODUCT_REJECTED = 'product_rejected';
    public const EVENT_SERVICE_PURCHASED = 'service_purchased';
    public const EVENT_SERVICE_CONFIRMED_SELLER = 'service_confirmed_seller';
    public const EVENT_SERVICE_CONFIRMED_BUYER = 'service_confirmed_buyer';
    public const EVENT_SERVICE_CREATED = 'service_created';
    public const EVENT_SERVICE_APPROVED = 'service_approved';
    public const EVENT_SERVICE_REJECTED = 'service_rejected';
    public const EVENT_SERVICE_ORDER_SELLER = 'service_order_seller';
    public const EVENT_SERVICE_ORDER_BUYER = 'service_order_buyer';
    public const EVENT_SERVICE_DELIVERED = 'service_delivered';
    public const EVENT_SERVICE_REVISION_REQUESTED = 'service_revision_requested';
    public const EVENT_SERVICE_MESSAGE_RECEIVED = 'service_message_received';
    public const EVENT_GROWTH_LISTING_APPROVED = 'growth_listing_approved';
    public const EVENT_GROWTH_LISTING_REJECTED = 'growth_listing_rejected';
    public const EVENT_GROWTH_LISTING_CREATED = 'growth_listing_created';
    public const EVENT_GROWTH_MESSAGE_RECEIVED = 'growth_message_received';
    public const EVENT_GROWTH_ORDER_CREATED = 'growth_order_created';
     public const EVENT_PUSH_TEST = 'push_test';
     public const EVENT_CHAT_MESSAGE_RECEIVED = 'chat_message_received';
     public const EVENT_ADMIN_ACTIVITY = 'admin_activity';
     public const EVENT_ADMIN_PUSH = 'admin_push';
     public const EVENT_FEATURE_EXPIRING_SOON = 'feature_expiring_soon';
     public const EVENT_FEATURE_EXPIRED = 'feature_expired';
     // Job events
     public const EVENT_JOB_CREATED = 'job_created';
     public const EVENT_JOB_APPLICATION_SUBMITTED = 'job_application_submitted';
     public const EVENT_JOB_APPLICANT_HIRED = 'job_applicant_hired';
     public const EVENT_JOB_APPLICANT_REJECTED = 'job_applicant_rejected';
     public const EVENT_JOB_CLOSED = 'job_closed';
    public const EVENT_CONTRACT_STARTED = 'contract_started';
    public const EVENT_MILESTONE_FUNDED = 'milestone_funded';
    public const EVENT_MILESTONE_SUBMITTED = 'milestone_submitted';
    public const EVENT_MILESTONE_REVISION_REQUESTED = 'milestone_revision_requested';
    public const EVENT_MILESTONE_RELEASED = 'milestone_released';

    // Delivery channels
    public const CHANNEL_IN_APP = 'in_app';
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_PUSH = 'push';

    // Escrow events
    public const EVENT_ESCROW_HELD = 'escrow_held';
    public const EVENT_ESCROW_RELEASED = 'escrow_released';
    public const EVENT_ESCROW_REFUNDED = 'escrow_refunded';
    public const EVENT_ESCROW_DISPUTED = 'escrow_disputed';
    public const EVENT_ESCROW_CANCELLED = 'escrow_cancelled';

    // Review events
    public const EVENT_REVIEW_SUBMITTED = 'review_submitted';
    public const EVENT_REVIEW_APPROVED = 'review_approved';
    public const EVENT_REVIEW_REJECTED = 'review_rejected';
    public const EVENT_REVIEW_REPLY_RECEIVED = 'review_reply_received';

    public function __construct(NotificationDispatchService $dispatchService)
    {
        $this->dispatchService = $dispatchService;
    }

    /**
     * Send notification for a specific event - QUEUED for performance
     */
    public function notify(string $event, ?User $user, array $data = [], ?User $actor = null): void
    {
        // Check if this event type is enabled globally
        if (!$this->isEventEnabled($event)) {
            return;
        }

        // Handle admin notifications
        if ($user === null) {
            $this->dispatchService->notifyAdmins(
                $this->getEventContent($event, null, $data, $actor)['title'],
                $this->getEventContent($event, null, $data, $actor)['message'],
                array_merge($data, ['event' => $event]),
                isset($data['exclude_user_id']) ? $data['exclude_user_id'] : null
            );
            return;
        }

        // Get notification configuration for this event
        $config = $this->getEventConfig($event);

        // Get enabled channels for this event
        $channels = $this->getEnabledChannels($event, $user);

        if (empty($channels)) {
            return;
        }

        // Get notification content
        $content = $this->getEventContent($event, $user, $data, $actor);

        // Queue the notification for async processing to avoid blocking requests
        \App\Jobs\SendUserNotification::dispatch(
            $user,
            $content['title'],
            $content['message'],
            $content['type'],
            array_merge($data, ['event' => $event, 'channels' => $channels]),
            $event,
            $this->shouldNotifyAdmins($event),
            in_array(self::CHANNEL_IN_APP, $channels),
            in_array(self::CHANNEL_EMAIL, $channels),
            in_array(self::CHANNEL_PUSH, $channels)
        );
    }

    /**
     * Send notification to multiple users (queued for scalability)
     */
    public function notifyMultiple(string $event, Collection $users, array $data = [], ?User $actor = null): void
    {
        // For small batches, process immediately
        if ($users->count() <= 10) {
            foreach ($users as $user) {
                $this->notify($event, $user, $data, $actor);
            }
            return;
        }

        // For large batches, queue the job
        \App\Jobs\SendBulkNotifications::dispatch($event, $users, $data, $actor);
    }

    /**
     * Send admin bulk notification to users
     */
    public function notifyAdminBulk(Collection $users, string $title, string $message, array $channels = []): void
    {
        foreach ($users as $user) {
            $data = [
                'title' => $title,
                'message' => $message,
                'source' => 'admin_push'
            ];

            // One dispatch path ensures global settings and the user's per-channel
            // preferences are respected consistently for admin broadcasts too.
            $this->dispatchService->sendToUser(
                $user,
                $title,
                $message,
                AppNotification::TYPE_SYSTEM,
                $data,
                null,
                false,
                in_array('database', $channels),
                in_array('email', $channels),
                in_array('push', $channels)
            );
        }
    }

    /**
     * Check if an event type is enabled
     */
    private function isEventEnabled(string $event): bool
    {
        // Global notification switch
        if (!SystemSetting::getBool('notifications_enabled', true)) {
            return false;
        }

        // Event-specific switch
        return SystemSetting::getBool("notify_{$event}", true);
    }

    /**
     * Get enabled channels for an event
     */
    private function getEnabledChannels(string $event, ?User $user = null): array
    {
        $channels = [];

        $category = $this->categoryForEvent($event);
        $preference = $user ? NotificationPreference::forUser($user) : null;

        // Global admin configuration and the user's own preferences must both allow a channel.
        if (SystemSetting::getBool("notify_{$event}_in_app", true) &&
            SystemSetting::getBool('notify_in_app_enabled', true) &&
            (!$preference || $preference->channelEnabled($category, NotificationPreference::CHANNEL_IN_APP))) {
            $channels[] = self::CHANNEL_IN_APP;
        }

        if (SystemSetting::getBool("notify_{$event}_email", true) &&
            SystemSetting::getBool('notify_email_enabled', true) &&
            (!$preference || $preference->channelEnabled($category, NotificationPreference::CHANNEL_EMAIL))) {
            $channels[] = self::CHANNEL_EMAIL;
        }

        if (SystemSetting::getBool("notify_{$event}_push", true) &&
            SystemSetting::getBool('notify_push_enabled', true) &&
            (!$preference || $preference->channelEnabled($category, NotificationPreference::CHANNEL_PUSH))) {
            $channels[] = self::CHANNEL_PUSH;
        }

        return $channels;
    }

    /**
     * Map low-level events to the user-facing preference groups.
     */
    private function categoryForEvent(string $event): string
    {
        if (str_contains($event, 'message') || $event === self::EVENT_CHAT_MESSAGE_RECEIVED) {
            return 'messages';
        }
        if (str_contains($event, 'application') || str_contains($event, 'applicant')) {
            return 'proposals';
        }
        if (str_starts_with($event, 'job_')) {
            return 'jobs';
        }
        if (str_starts_with($event, 'escrow_') || str_contains($event, 'withdrawal') || str_contains($event, 'earnings') || str_contains($event, 'payment')) {
            return 'payments';
        }
        if (str_starts_with($event, 'review_')) {
            return 'reviews';
        }
        if (str_contains($event, 'dispute')) {
            return 'disputes';
        }
        if (str_contains($event, 'contract') || str_contains($event, 'milestone') || str_contains($event, 'service_delivered') || str_contains($event, 'revision')) {
            return 'contracts';
        }
        if (str_contains($event, 'activation') || str_contains($event, 'account') || str_contains($event, 'onboarding')) {
            return 'security';
        }
        if (str_contains($event, 'bundle') || str_contains($event, 'recommend')) {
            return 'marketing';
        }

        return 'system';
    }

    /**
     * Check if admins should be notified for this event
     */
    private function shouldNotifyAdmins(string $event): bool
    {
        $adminEvents = [
            self::EVENT_TASK_CREATED,
            self::EVENT_PRODUCT_PURCHASED,
            self::EVENT_SERVICE_PURCHASED,
            self::EVENT_GROWTH_LISTING_APPROVED,
            self::EVENT_LARGE_WITHDRAWAL_ALERT,
        ];

        return in_array($event, $adminEvents) && SystemSetting::getBool('notify_admin_activity', true);
    }

    /**
     * Get notification content for an event
     */
    private function getEventContent(string $event, ?User $user, array $data, ?User $actor): array
    {
        return match($event) {
            self::EVENT_TASK_APPROVED => [
                'title' => 'Task Approved! 🎉',
                'message' => 'Your submission for "' . ($data['task_title'] ?? 'task') . '" has been approved. You earned ₦' . number_format($data['amount'] ?? 0, 2),
                'type' => AppNotification::TYPE_TASK_APPROVED,
            ],
            self::EVENT_TASK_REJECTED => [
                'title' => 'Task Submission Rejected',
                'message' => 'Your submission for "' . ($data['task_title'] ?? 'task') . '" was rejected: ' . ($data['reason'] ?? 'No reason provided'),
                'type' => AppNotification::TYPE_TASK_REJECTED,
            ],
            self::EVENT_TASK_CREATED => [
                'title' => 'Task Created Successfully!',
                'message' => 'Your task "' . ($data['task_title'] ?? 'task') . '" has been created and is now live.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_TASK_BUNDLE_AVAILABLE => [
                'title' => 'New Task Bundle Available',
                'message' => 'A new task bundle "' . ($data['bundle_name'] ?? 'bundle') . '" is now available for ₦' . number_format($data['price'] ?? 0, 2),
                'type' => AppNotification::TYPE_NEW_TASK,
            ],
            self::EVENT_WITHDRAWAL_REQUESTED => [
                'title' => 'Withdrawal Request Received',
                'message' => 'Your withdrawal request of ' . ($data['amount'] ?? '₦0.00') . ' has been received and is being processed.',
                'type' => AppNotification::TYPE_WITHDRAWAL,
            ],
            self::EVENT_WITHDRAWAL_STATUS => [
                'title' => 'Withdrawal Status Update',
                'message' => 'Your withdrawal request of ₦' . number_format($data['amount'] ?? 0, 2) . ' has been ' . ($data['status'] ?? 'processed'),
                'type' => AppNotification::TYPE_WITHDRAWAL,
            ],
            self::EVENT_LARGE_WITHDRAWAL_ALERT => [
                'title' => 'Large Withdrawal Alert',
                'message' => 'A large withdrawal request of ' . ($data['amount'] ?? '₦0.00') . ' was submitted by user ID: ' . ($data['user_id'] ?? 'unknown'),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_REFERRAL_BONUS => [
                'title' => 'Referral Bonus Earned',
                'message' => 'Congratulations! You earned ₦' . number_format($data['bonus_amount'] ?? 0, 2) . ' for referring ' . ($data['referred_user'] ?? 'a user'),
                'type' => AppNotification::TYPE_REFERRAL,
            ],
            self::EVENT_EARNINGS_UNLOCKED => [
                'title' => 'Earnings Access Unlocked! 🎉',
                'message' => 'Great news! You can now start completing tasks and earning money. Your first campaign has been created.',
                'type' => AppNotification::TYPE_EARNINGS,
            ],
            self::EVENT_ACCOUNT_TYPE_REMINDER => [
                'title' => 'Account Type Reminder',
                'message' => 'Your account type is already set to: ' . ($data['account_type_label'] ?? $user->account_type),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_ACTIVATION_REMINDER => [
                'title' => 'Complete Your Activation',
                'message' => 'Don\'t forget to activate your account to start earning. You need ₦' . number_format($data['activation_fee'] ?? 1500, 2),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_ACTIVATION_RETRY => [
                'title' => 'Activation Retry Required',
                'message' => 'Your activation attempt failed earlier and has been re-queued. Please retry your activation to continue.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_ONBOARDING_COMPLETE => [
                'title' => 'Onboarding Complete! 🎉',
                'message' => 'Congratulations! You have completed the onboarding process and can now access all features.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_ONBOARDING_REMINDER => [
                'title' => 'Complete Your Onboarding',
                'message' => 'Don\'t forget to complete your onboarding process to unlock all features and start earning!',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_PRODUCT_CREATED => [
                'title' => 'Product Created',
                'message' => 'Your digital product "' . ($data['product_title'] ?? 'product') . '" has been created successfully.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_PRODUCT_APPROVED => [
                'title' => 'Product Approved',
                'message' => 'Your digital product "' . ($data['product_title'] ?? 'product') . '" has been approved and is now live!',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_PRODUCT_REJECTED => [
                'title' => 'Product Rejected',
                'message' => 'Your digital product "' . ($data['product_title'] ?? 'product') . '" was not approved. Reason: ' . ($data['reason'] ?? 'Not specified'),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_PRODUCT_PURCHASED => [
                'title' => 'Product Purchased Successfully',
                'message' => 'You have successfully purchased "' . ($data['product_name'] ?? 'product') . '" for ₦' . number_format($data['amount'] ?? 0, 2),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_PRODUCT_PURCHASED_SELLER => [
                'title' => 'New Product Purchase',
                'message' => 'Your product "' . ($data['product_title'] ?? 'product') . '" was purchased. Order: ' . ($data['order_number'] ?? ''),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_PRODUCT_PURCHASED_BUYER => [
                'title' => 'Purchase Successful',
                'message' => 'You purchased "' . ($data['product_title'] ?? 'product') . '" successfully. Order: ' . ($data['order_number'] ?? ''),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_PRODUCT_DOWNLOADED => [
                'title' => 'Product Downloaded',
                'message' => 'Your download for "' . ($data['product_name'] ?? 'product') . '" is ready.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],            self::EVENT_PRODUCT_CONFIRMED_SELLER => [
                'title' => 'Product Payment Released',
                'message' => 'Buyer confirmed receipt and reviewed "' . ($data['product_title'] ?? 'your product') . '". Your payout has been released.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_PRODUCT_CONFIRMED_BUYER => [
                'title' => 'Product Confirmed Successfully',
                'message' => 'You confirmed receipt and submitted your review. Payment has now been released to the creator.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],            self::EVENT_SERVICE_PURCHASED => [
                'title' => 'Service Purchased Successfully',
                'message' => 'You have successfully purchased "' . ($data['service_name'] ?? 'service') . '" for ₦' . number_format($data['amount'] ?? 0, 2),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_SERVICE_CONFIRMED_SELLER => [
                'title' => 'Service Payment Released',
                'message' => 'Buyer confirmed satisfaction for "' . ($data['service_title'] ?? 'your service') . '". Payment has been released to your wallet.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_SERVICE_CONFIRMED_BUYER => [
                'title' => 'Service Confirmed Successfully',
                'message' => 'You confirmed delivery and submitted a review. Payment has been released to the provider.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_SERVICE_CREATED => [
                'title' => 'Service Created',
                'message' => 'Your service "' . ($data['service_title'] ?? 'service') . '" has been submitted successfully.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_SERVICE_APPROVED => [
                'title' => 'Service Approved',
                'message' => 'Your service "' . ($data['service_title'] ?? 'service') . '" has been approved and is now live!',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_SERVICE_REJECTED => [
                'title' => 'Service Rejected',
                'message' => 'Your service "' . ($data['service_title'] ?? 'service') . '" was not approved. Reason: ' . ($data['reason'] ?? 'Not specified'),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_SERVICE_ORDER_SELLER => [
                'title' => 'New Service Order Received',
                'message' => 'You received a new order for "' . ($data['service_title'] ?? 'Professional Service') . '".',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_SERVICE_ORDER_BUYER => [
                'title' => 'Service Order Confirmed',
                'message' => 'Your order for "' . ($data['service_title'] ?? 'Professional Service') . '" has been placed successfully.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_SERVICE_DELIVERED => [
                'title' => 'Service Delivered',
                'message' => 'Your service "' . ($data['service_title'] ?? 'service') . '" has been delivered. Please review and confirm to release payment.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
             self::EVENT_SERVICE_MESSAGE_RECEIVED => [
                'title' => 'New Message from ' . ($data['sender_name'] ?? 'sender'),
                'message' => 'Subject: ' . ($data['subject'] ?? '') . "\n\n" . ($data['message'] ?? ''),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            // Job events
            self::EVENT_JOB_CREATED => [
                'title' => 'Job Posted Successfully! 💼',
                'message' => 'Your job "' . ($data['job_title'] ?? 'job') . '" has been created and is now live.',
                'type' => AppNotification::TYPE_JOB_CREATED,
            ],
            self::EVENT_JOB_APPLICATION_SUBMITTED => [
                'title' => ($data['recipient_role'] ?? null) === 'client' ? 'New Proposal Received' : 'Proposal Submitted',
                'message' => ($data['recipient_role'] ?? null) === 'client'
                    ? (($data['applicant_name'] ?? 'A freelancer') . ' submitted a proposal for "' . ($data['job_title'] ?? 'job') . '".')
                    : ('Your proposal for "' . ($data['job_title'] ?? 'job') . '" was submitted successfully.'),
                'type' => AppNotification::TYPE_JOB_APPLICATION_SUBMITTED,
            ],
            self::EVENT_JOB_APPLICANT_HIRED => [
                'title' => ($data['recipient_role'] ?? null) === 'client' ? 'Freelancer Hired' : 'You Were Hired',
                'message' => ($data['recipient_role'] ?? null) === 'client'
                    ? (($data['applicant_name'] ?? 'The freelancer') . ' has been hired for "' . ($data['job_title'] ?? 'job') . '". Your contract workroom is ready.')
                    : ('You have been hired for "' . ($data['job_title'] ?? 'job') . '". Open the contract workroom to continue.'),
                'type' => AppNotification::TYPE_JOB_APPLICANT_HIRED,
            ],
            self::EVENT_JOB_APPLICANT_REJECTED => [
                'title' => ($data['recipient_role'] ?? null) === 'client' ? 'Proposal Declined' : 'Proposal Update',
                'message' => ($data['recipient_role'] ?? null) === 'client'
                    ? ('You declined ' . ($data['applicant_name'] ?? 'the freelancer') . '\'s proposal for "' . ($data['job_title'] ?? 'job') . '".')
                    : ('Your proposal for "' . ($data['job_title'] ?? 'job') . '" was not selected this time.'),
                'type' => AppNotification::TYPE_JOB_APPLICANT_REJECTED,
            ],
            self::EVENT_JOB_CLOSED => [
                'title' => 'Job Closed',
                'message' => 'Your job "' . ($data['job_title'] ?? 'job') . '" has been closed.',
                'type' => AppNotification::TYPE_JOB_CLOSED,
            ],
            self::EVENT_CONTRACT_STARTED => [
                'title' => 'Contract Started',
                'message' => 'A contract for "' . ($data['contract_title'] ?? 'project') . '" is now active.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_MILESTONE_FUNDED => [
                'title' => 'Milestone Funded',
                'message' => '"' . ($data['milestone_title'] ?? 'Milestone') . '" has been funded with ₦' . number_format($data['amount'] ?? 0, 2) . ' held in escrow.',
                'type' => AppNotification::TYPE_PAYMENT,
            ],
            self::EVENT_MILESTONE_SUBMITTED => [
                'title' => 'Milestone Submitted for Review',
                'message' => 'Work for "' . ($data['milestone_title'] ?? 'milestone') . '" is ready for review.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_MILESTONE_REVISION_REQUESTED => [
                'title' => 'Revision Requested',
                'message' => 'The client requested changes to "' . ($data['milestone_title'] ?? 'milestone') . '".',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_MILESTONE_RELEASED => [
                'title' => 'Milestone Approved and Paid',
                'message' => '"' . ($data['milestone_title'] ?? 'Milestone') . '" was approved and ₦' . number_format($data['amount'] ?? 0, 2) . ' was released.',
                'type' => AppNotification::TYPE_EARNINGS,
            ],
            self::EVENT_GROWTH_LISTING_APPROVED => [
                'title' => 'Growth Listing Approved',
                'message' => 'Your growth listing "' . ($data['listing_title'] ?? 'listing') . '" has been approved and is now live.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_GROWTH_LISTING_REJECTED => [
                'title' => 'Growth Listing Rejected',
                'message' => 'Your growth listing "' . ($data['listing_title'] ?? 'listing') . '" was not approved. Reason: ' . ($data['reason'] ?? 'Not specified'),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_GROWTH_LISTING_CREATED => [
                'title' => 'Growth Listing Created',
                'message' => 'Your listing "' . ($data['listing_title'] ?? 'listing') . '" has been submitted successfully.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_GROWTH_MESSAGE_RECEIVED => [
                'title' => 'New Message on ' . ($data['listing_title'] ?? 'listing'),
                'message' => 'You have a new message from ' . ($data['sender_name'] ?? 'a buyer') . ' about your growth listing.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_GROWTH_ORDER_CREATED => [
                'title' => 'New Growth Order Received',
                'message' => 'You have received a new order for "' . ($data['listing_title'] ?? 'growth service') . '". Order #: ' . ($data['order_id'] ?? ''),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_PUSH_TEST => [
                'title' => '🔔 SwiftKudi Push Test',
                'message' => 'Push notifications are working! Sent at ' . ($data['timestamp'] ?? now()->format('H:i:s')),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
             self::EVENT_ADMIN_PUSH => [
                 'title' => $data['title'] ?? 'Admin Notification',
                 'message' => $data['message'] ?? 'You have a notification from the admin.',
                 'type' => AppNotification::TYPE_SYSTEM,
             ],
             // Feature expiry events
             self::EVENT_FEATURE_EXPIRING_SOON => [
                 'title' => 'Feature Expiring Soon ⏰',
                 'message' => 'Your "' . ($data['feature_label'] ?? $data['feature'] ?? 'feature') . '" access will expire on ' . ($data['expires_at'] ?? 'soon') . '. Renew now to avoid interruption.',
                 'type' => AppNotification::TYPE_FEATURE_EXPIRING_SOON,
             ],
             self::EVENT_FEATURE_EXPIRED => [
                 'title' => 'Feature Access Expired',
                 'message' => 'Your "' . ($data['feature_label'] ?? $data['feature'] ?? 'feature') . '" access has expired. Renew now to continue using this feature.',
                 'type' => AppNotification::TYPE_FEATURE_EXPIRED,
             ],
            // Escrow events
            self::EVENT_ESCROW_HELD => [
                'title' => 'Payment Held in Escrow',
                'message' => 'Your payment of ₦' . number_format($data['amount'] ?? 0, 2) . ' for "' . ($data['order_title'] ?? 'order') . '" is now held in escrow.',
                'type' => AppNotification::TYPE_PAYMENT,
            ],
            self::EVENT_ESCROW_RELEASED => [
                'title' => 'Escrow Funds Released',
                'message' => '₦' . number_format($data['amount'] ?? 0, 2) . ' has been released to your wallet for "' . ($data['order_title'] ?? 'order') . '".',
                'type' => AppNotification::TYPE_EARNINGS,
            ],
            self::EVENT_ESCROW_REFUNDED => [
                'title' => 'Escrow Refunded',
                'message' => 'Your escrow payment of ₦' . number_format($data['amount'] ?? 0, 2) . ' for "' . ($data['order_title'] ?? 'order') . '" has been refunded.',
                'type' => AppNotification::TYPE_PAYMENT,
            ],
            self::EVENT_ESCROW_DISPUTED => [
                'title' => 'Escrow Dispute Opened',
                'message' => 'A dispute has been opened for "' . ($data['order_title'] ?? 'order') . '". Our team will review and contact you.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_ESCROW_CANCELLED => [
                'title' => 'Escrow Cancelled',
                'message' => 'The escrow for "' . ($data['order_title'] ?? 'order') . '" has been cancelled.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            // Review events
            self::EVENT_REVIEW_SUBMITTED => [
                'title' => 'New Review Received',
                'message' => 'You received a ' . ($data['rating'] ?? '') . '-star review for "' . ($data['item_title'] ?? 'your service') . '".',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_REVIEW_APPROVED => [
                'title' => 'Review Published',
                'message' => 'Your review for "' . ($data['item_title'] ?? 'item') . '" has been published.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_REVIEW_REJECTED => [
                'title' => 'Review Not Published',
                'message' => 'Your review for "' . ($data['item_title'] ?? 'item') . '" was not published. Reason: ' . ($data['reason'] ?? 'Not specified'),
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            self::EVENT_REVIEW_REPLY_RECEIVED => [
                'title' => 'Reply to Your Review',
                'message' => ($data['seller_name'] ?? 'Seller') . ' replied to your review on "' . ($data['item_title'] ?? 'item') . '".',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
            default => [
                'title' => 'Notification',
                'message' => $data['message'] ?? 'You have a new notification.',
                'type' => AppNotification::TYPE_SYSTEM,
            ],
        };
    }

    /**
     * Get event configuration
     */
    private function getEventConfig(string $event): array
    {
        return [
            'enabled' => true,
            'channels' => [self::CHANNEL_IN_APP, self::CHANNEL_EMAIL],
            'admin_notify' => false,
        ];
    }

    /**
     * Get all available events
     */
     public static function getAvailableEvents(): array
     {
         return [
             self::EVENT_TASK_APPROVED,
             self::EVENT_TASK_REJECTED,
             self::EVENT_TASK_CREATED,
             self::EVENT_TASK_BUNDLE_AVAILABLE,
             self::EVENT_WITHDRAWAL_STATUS,
             self::EVENT_REFERRAL_BONUS,
             self::EVENT_EARNINGS_UNLOCKED,
             self::EVENT_ACCOUNT_TYPE_REMINDER,
             self::EVENT_ACTIVATION_REMINDER,
             self::EVENT_ACTIVATION_RETRY,
             self::EVENT_ONBOARDING_COMPLETE,
             self::EVENT_ONBOARDING_REMINDER,
             self::EVENT_FEATURE_EXPIRING_SOON,
             self::EVENT_FEATURE_EXPIRED,
             self::EVENT_PRODUCT_PURCHASED,
             self::EVENT_PRODUCT_DOWNLOADED,
             self::EVENT_PRODUCT_APPROVED,
             self::EVENT_PRODUCT_REJECTED,
             self::EVENT_SERVICE_APPROVED,
             self::EVENT_SERVICE_REJECTED,
             self::EVENT_SERVICE_PURCHASED,
             self::EVENT_GROWTH_LISTING_APPROVED,
             self::EVENT_GROWTH_LISTING_REJECTED,
             self::EVENT_GROWTH_ORDER_CREATED,
             // Job events
             self::EVENT_JOB_CREATED,
             self::EVENT_JOB_APPLICATION_SUBMITTED,
             self::EVENT_JOB_APPLICANT_HIRED,
             self::EVENT_JOB_APPLICANT_REJECTED,
             self::EVENT_JOB_CLOSED,
             self::EVENT_CONTRACT_STARTED,
             self::EVENT_MILESTONE_FUNDED,
             self::EVENT_MILESTONE_SUBMITTED,
             self::EVENT_MILESTONE_REVISION_REQUESTED,
             self::EVENT_MILESTONE_RELEASED,
         ];
     }

    /**
     * Get all available channels
     */
    public static function getAvailableChannels(): array
    {
        return [
            self::CHANNEL_IN_APP,
            self::CHANNEL_EMAIL,
            self::CHANNEL_PUSH,
        ];
    }
}