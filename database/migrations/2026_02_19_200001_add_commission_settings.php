<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add commission settings to system_settings
        if (Schema::hasTable('system_settings')) {
            $settings = [
                ['key' => 'service_commission_rate', 'value' => '10', 'group' => 'marketplace', 'type' => 'number', 'description' => 'Platform commission for professional services (%)'],
                ['key' => 'growth_commission_rate', 'value' => '10', 'group' => 'marketplace', 'type' => 'number', 'description' => 'Platform commission for growth marketplace (%)'],
            ];

            foreach ($settings as $setting) {
                if (!\App\Models\SystemSetting::where('key', $setting['key'])->exists()) {
                    \App\Models\SystemSetting::create($setting);
                }
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('system_settings')) {
            \App\Models\SystemSetting::where('key', 'service_commission_rate')->delete();
            \App\Models\SystemSetting::where('key', 'growth_commission_rate')->delete();
        }
    }
};
