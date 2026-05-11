<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddNewNotificationSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settings = [
            // Global notification settings
            ['key' => 'notifications_enabled', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_in_app_enabled', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_email_enabled', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_push_enabled', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_admin_activity', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],

            // Task-related notifications
            ['key' => 'notify_task_approved', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_task_approved_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_task_approved_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_task_approved_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],

            ['key' => 'notify_task_rejected', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_task_rejected_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_task_rejected_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_task_rejected_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],

            ['key' => 'notify_task_created', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_task_created_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_task_created_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_task_created_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],

            ['key' => 'notify_task_bundle_available', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_task_bundle_available_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_task_bundle_available_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_task_bundle_available_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],

            // Financial notifications
            ['key' => 'notify_withdrawal_status', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_withdrawal_status_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_withdrawal_status_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_withdrawal_status_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],

            ['key' => 'notify_referral_bonus', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_referral_bonus_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_referral_bonus_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_referral_bonus_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],

            // Other notifications
            ['key' => 'notify_earnings_unlocked', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_earnings_unlocked_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_earnings_unlocked_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_earnings_unlocked_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],

            ['key' => 'notify_account_type_reminder', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_account_type_reminder_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_account_type_reminder_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_account_type_reminder_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],

            ['key' => 'notify_activation_reminder', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_activation_reminder_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_activation_reminder_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_activation_reminder_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],

            ['key' => 'notify_onboarding_complete', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_onboarding_complete_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_onboarding_complete_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_onboarding_complete_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],

            ['key' => 'notify_product_purchased', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_product_purchased_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_product_purchased_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_product_purchased_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],

            ['key' => 'notify_product_downloaded', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_product_downloaded_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_product_downloaded_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_product_downloaded_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],

            ['key' => 'notify_service_purchased', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_service_purchased_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_service_purchased_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_service_purchased_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],

            ['key' => 'notify_growth_listing_approved', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_growth_listing_approved_in_app', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_growth_listing_approved_email', 'value' => 'true', 'group' => 'notification', 'type' => 'boolean'],
            ['key' => 'notify_growth_listing_approved_push', 'value' => 'false', 'group' => 'notification', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'type' => $setting['type'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $keys = [
            'notifications_enabled',
            'notify_in_app_enabled',
            'notify_email_enabled',
            'notify_push_enabled',
            'notify_admin_activity',
            'notify_task_approved',
            'notify_task_approved_in_app',
            'notify_task_approved_email',
            'notify_task_approved_push',
            'notify_task_rejected',
            'notify_task_rejected_in_app',
            'notify_task_rejected_email',
            'notify_task_rejected_push',
            'notify_task_created',
            'notify_task_created_in_app',
            'notify_task_created_email',
            'notify_task_created_push',
            'notify_task_bundle_available',
            'notify_task_bundle_available_in_app',
            'notify_task_bundle_available_email',
            'notify_task_bundle_available_push',
            'notify_withdrawal_status',
            'notify_withdrawal_status_in_app',
            'notify_withdrawal_status_email',
            'notify_withdrawal_status_push',
            'notify_referral_bonus',
            'notify_referral_bonus_in_app',
            'notify_referral_bonus_email',
            'notify_referral_bonus_push',
            'notify_earnings_unlocked',
            'notify_earnings_unlocked_in_app',
            'notify_earnings_unlocked_email',
            'notify_earnings_unlocked_push',
            'notify_account_type_reminder',
            'notify_account_type_reminder_in_app',
            'notify_account_type_reminder_email',
            'notify_account_type_reminder_push',
            'notify_activation_reminder',
            'notify_activation_reminder_in_app',
            'notify_activation_reminder_email',
            'notify_activation_reminder_push',
            'notify_onboarding_complete',
            'notify_onboarding_complete_in_app',
            'notify_onboarding_complete_email',
            'notify_onboarding_complete_push',
            'notify_product_purchased',
            'notify_product_purchased_in_app',
            'notify_product_purchased_email',
            'notify_product_purchased_push',
            'notify_product_downloaded',
            'notify_product_downloaded_in_app',
            'notify_product_downloaded_email',
            'notify_product_downloaded_push',
            'notify_service_purchased',
            'notify_service_purchased_in_app',
            'notify_service_purchased_email',
            'notify_service_purchased_push',
            'notify_growth_listing_approved',
            'notify_growth_listing_approved_in_app',
            'notify_growth_listing_approved_email',
            'notify_growth_listing_approved_push',
        ];

        DB::table('system_settings')->whereIn('key', $keys)->delete();
    }
}
