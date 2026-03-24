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
        if (!Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('system_settings', 'task_approval_expiry_enabled')) {
                $table->boolean('task_approval_expiry_enabled')->default(false);
            }

            if (!Schema::hasColumn('system_settings', 'task_approval_expiry_value')) {
                $table->integer('task_approval_expiry_value')->default(24);
            }

            if (!Schema::hasColumn('system_settings', 'task_approval_expiry_unit')) {
                $table->enum('task_approval_expiry_unit', ['hours', 'days'])->default('hours');
            }

            if (!Schema::hasColumn('system_settings', 'task_approval_expiry_action')) {
                $table->enum('task_approval_expiry_action', ['auto_approve', 'expire'])->default('auto_approve');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table) {
            if (Schema::hasColumn('system_settings', 'task_approval_expiry_enabled')) {
                $table->dropColumn('task_approval_expiry_enabled');
            }

            if (Schema::hasColumn('system_settings', 'task_approval_expiry_value')) {
                $table->dropColumn('task_approval_expiry_value');
            }

            if (Schema::hasColumn('system_settings', 'task_approval_expiry_unit')) {
                $table->dropColumn('task_approval_expiry_unit');
            }

            if (Schema::hasColumn('system_settings', 'task_approval_expiry_action')) {
                $table->dropColumn('task_approval_expiry_action');
            }
        });
    }
};
