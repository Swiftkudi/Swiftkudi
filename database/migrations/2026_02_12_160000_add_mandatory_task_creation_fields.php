<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add mandatory task creation fields to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'total_created_task_budget')) {
                $table->decimal('total_created_task_budget', 15, 2)->default(0)->after('referred_by');
            }
            if (!Schema::hasColumn('users', 'has_completed_mandatory_creation')) {
                $table->boolean('has_completed_mandatory_creation')->default(false)->after('total_created_task_budget');
            }
            if (!Schema::hasColumn('users', 'task_creation_unlocked_at')) {
                $table->timestamp('task_creation_unlocked_at')->nullable()->after('has_completed_mandatory_creation');
            }
        });

        // Add bundle_id to track task bundles
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'bundle_id')) {
                $table->unsignedBigInteger('bundle_id')->nullable()->after('task_type_id');
                $table->foreign('bundle_id')->references('id')->on('task_bundles')->onDelete('set null');
            }
            if (!Schema::hasColumn('tasks', 'is_mandatory_bundle')) {
                $table->boolean('is_mandatory_bundle')->default(false)->after('bundle_id');
            }
        });

        // Add required creation budget to wallets
        Schema::table('wallets', function (Blueprint $table) {
            if (!Schema::hasColumn('wallets', 'total_spent_on_tasks')) {
                $table->decimal('total_spent_on_tasks', 15, 2)->default(0)->after('escrow_balance');
            }
        });

        // System settings for mandatory creation gate
        if (Schema::hasTable('system_settings')) {
            $settings = [
                ['key' => 'mandatory_task_creation_enabled', 'value' => 'true', 'group' => 'general', 'type' => 'boolean', 'description' => 'Enable mandatory task creation after activation'],
                ['key' => 'minimum_required_budget', 'value' => '2500', 'group' => 'general', 'type' => 'number', 'description' => 'Minimum budget required to unlock earning (in NGN)'],
                ['key' => 'mandatory_budget_currency', 'value' => 'NGN', 'group' => 'general', 'type' => 'text', 'description' => 'Currency for mandatory budget requirement'],
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'total_created_task_budget',
                'has_completed_mandatory_creation',
                'task_creation_unlocked_at',
            ]);
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('total_spent_on_tasks');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['bundle_id']);
            $table->dropColumn(['bundle_id', 'is_mandatory_bundle']);
        });
    }
};
