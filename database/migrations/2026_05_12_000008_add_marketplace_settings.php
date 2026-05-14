<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
$settings = [
             ['key' => 'marketplace_enabled', 'value' => '1', 'group' => 'modules'],
             ['key' => 'marketplace_commission_rate', 'value' => '5', 'group' => 'modules'],
             ['key' => 'marketplace_premium_commission_rate', 'value' => '2', 'group' => 'modules'],
             ['key' => 'marketplace_featured_listing_cost', 'value' => '500', 'group' => 'modules'],
             ['key' => 'marketplace_promoted_listing_cost', 'value' => '200', 'group' => 'modules'],
             ['key' => 'marketplace_listing_boost_cost', 'value' => '100', 'group' => 'modules'],
             ['key' => 'marketplace_urgent_listing_cost', 'value' => '150', 'group' => 'modules'],
             ['key' => 'marketplace_premium_seller_monthly', 'value' => '2000', 'group' => 'modules'],
             ['key' => 'marketplace_auto_release_days', 'value' => '7', 'group' => 'modules'],
             ['key' => 'marketplace_listing_max_images', 'value' => '10', 'group' => 'modules'],
             ['key' => 'marketplace_max_title_length', 'value' => '255', 'group' => 'modules'],
             ['key' => 'marketplace_max_description_length', 'value' => '5000', 'group' => 'modules'],
             ['key' => 'marketplace_auto_approve_reviews', 'value' => '1', 'group' => 'modules'],
             ['key' => 'marketplace_buyer_features', 'value' => json_encode(['browse', 'purchase', 'review', 'chat', 'favourite']), 'group' => 'modules'],
             ['key' => 'marketplace_seller_features', 'value' => json_encode(['list', 'price', 'ship', 'chat', 'analytics']), 'group' => 'modules'],
         ];

        foreach ($settings as $setting) {
$exists = DB::table('system_settings')
                 ->where('key', $setting['key'])
                 ->exists();
             if (!$exists) {
DB::table('system_settings')->insert([
                     'key' => $setting['key'],
                     'value' => $setting['value'],
                     'group' => $setting['group'] ?? 'modules',
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