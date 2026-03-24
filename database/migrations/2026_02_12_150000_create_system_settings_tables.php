<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // System settings table
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group', 50)->index(); // smtp, payment, security, general, cron, currency, etc.
                $table->string('type', 20)->default('text'); // text, boolean, number, json, encrypted
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // Settings audit log table
        if (!Schema::hasTable('settings_audit_logs')) {
            Schema::create('settings_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id');
                $table->string('setting_key');
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['admin_id']);
                $table->index(['setting_key']);
                $table->index(['created_at']);
            });
        }

        // Admin roles/permissions table
        if (!Schema::hasTable('admin_roles')) {
            Schema::create('admin_roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // super_admin, finance_admin, support_admin
                $table->string('display_name');
                $table->json('permissions');
                $table->timestamps();
            });
        }

        // Add role_id to users table
        if (!Schema::hasColumn('users', 'admin_role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('admin_role_id')->nullable()->after('is_admin');
                $table->foreign('admin_role_id')->references('id')->on('admin_roles')->onDelete('set null');
            });
        }

        // Maintenance mode settings
        if (!Schema::hasTable('maintenance_mode')) {
            Schema::create('maintenance_mode', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_enabled')->default(false);
                $table->text('message')->nullable();
                $table->timestamp('enabled_at')->nullable();
                $table->unsignedBigInteger('enabled_by')->nullable();
                $table->timestamps();

                $table->foreign('enabled_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('maintenance_mode');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['admin_role_id']);
            $table->dropColumn('admin_role_id');
        });
        Schema::dropIfExists('settings_audit_logs');
        Schema::dropIfExists('admin_roles');
        Schema::dropIfExists('system_settings');
    }
};
