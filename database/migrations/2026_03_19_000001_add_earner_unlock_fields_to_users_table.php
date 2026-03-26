<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'referral_task_completed')) {
                $table->boolean('referral_task_completed')->default(false)->after('activation_paid');
            }
            if (!Schema::hasColumn('users', 'referral_task_skipped')) {
                $table->boolean('referral_task_skipped')->default(false)->after('referral_task_completed');
            }
            if (!Schema::hasColumn('users', 'earner_features')) {
                $table->json('earner_features')->nullable()->after('referral_task_skipped');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['referral_task_completed', 'referral_task_skipped', 'earner_features']);
        });
    }
};