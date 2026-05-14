<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['key' => 'marketplace_enabled', 'value' => '1'],
            ['key' => 'marketplace_commission_rate', 'value' => '5'],
            ['key' => 'marketplace_premium_commission_rate', 'value' => '2'],
            ['key' => 'marketplace_featured_listing_cost', 'value' => '500'],
            ['key' => 'marketplace_promoted_listing_cost', 'value' => '200'],
            ['key' => 'marketplace_listing_boost_cost', 'value' => '100'],
            ['key' => 'marketplace_urgent_listing_cost', 'value' => '150'],
            ['key' => 'marketplace_premium_seller_monthly', 'value' => '2000'],
            ['key' => 'marketplace_auto_release_days', 'value' => '7'],
            ['key' => 'marketplace_listing_max_images', 'value' => '10'],
            ['key' => 'marketplace_max_title_length', 'value' => '255'],
            ['key' => 'marketplace_max_description_length', 'value' => '5000'],
            ['key' => 'marketplace_auto_approve_reviews', 'value' => '1'],
            ['key' => 'marketplace_buyer_features', 'value' => json_encode(['browse', 'purchase', 'review', 'chat', 'favourite'])],
            ['key' => 'marketplace_seller_features', 'value' => json_encode(['list', 'price', 'ship', 'chat', 'analytics'])],
        ];

        foreach ($settings as $setting) {
            $exists = DB::table('system_settings')
                ->where('setting_key', $setting['key'])
                ->exists();
            if (!$exists) {
                DB::table('system_settings')->insert([
                    'setting_key' => $setting['key'],
                    'setting_value' => $setting['value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $keys = [
            'marketplace_enabled', 'marketplace_commission_rate',
            'marketplace_premium_commission_rate', 'marketplace_featured_listing_cost',
            'marketplace_promoted_listing_cost', 'marketplace_listing_boost_cost',
            'marketplace_urgent_listing_cost', 'marketplace_premium_seller_monthly',
            'marketplace_auto_release_days', 'marketplace_listing_max_images',
            'marketplace_max_title_length', 'marketplace_max_description_length',
            'marketplace_auto_approve_reviews', 'marketplace_buyer_features',
            'marketplace_seller_features',
        ];
        DB::table('system_settings')->whereIn('setting_key', $keys)->delete();
    }
};