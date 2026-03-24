<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add referral_bonus_task_enabled setting
        \DB::table('system_settings')->updateOrInsert(
            ['key' => 'referral_bonus_task_enabled'],
            [
                'value' => '1',
                'group' => 'general',
                'type' => 'boolean',
                'description' => 'Enable permanent referral bonus task for all users',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Add referral_bonus_amount setting (per referral)
        \DB::table('system_settings')->updateOrInsert(
            ['key' => 'referral_bonus_amount'],
            [
                'value' => '500',
                'group' => 'general',
                'type' => 'number',
                'description' => 'Amount earned per activated referral (₦500 x 20 = ₦10,000 bonus)',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Add referral_bonus_target setting (number of referrals needed)
        \DB::table('system_settings')->updateOrInsert(
            ['key' => 'referral_bonus_target'],
            [
                'value' => '20',
                'group' => 'general',
                'type' => 'number',
                'description' => 'Number of activated referrals needed to earn the bonus',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('system_settings')->whereIn('key', [
            'referral_bonus_task_enabled',
            'referral_bonus_amount',
            'referral_bonus_target',
        ])->delete();
    }
};
