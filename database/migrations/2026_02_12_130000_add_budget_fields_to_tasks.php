<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Budget enforcement fields
            if (!Schema::hasColumn('tasks', 'minimum_budget')) {
                $table->decimal('minimum_budget', 15, 2)->default(2500)->after('budget');
            }
            if (!Schema::hasColumn('tasks', 'is_bundled')) {
                $table->boolean('is_bundled')->default(false)->after('minimum_budget');
            }
            if (!Schema::hasColumn('tasks', 'bundle_id')) {
                $table->unsignedBigInteger('bundle_id')->nullable()->after('is_bundled');
                $table->foreign('bundle_id')->references('id')->on('task_bundles')->onDelete('set null');
            }
            // Add worker_reward_per_task if not exists
            if (!Schema::hasColumn('tasks', 'worker_reward_per_task')) {
                $table->decimal('worker_reward_per_task', 10, 2)->default(0)->after('bundle_id');
            }
            // Add platform_commission if not exists
            if (!Schema::hasColumn('tasks', 'platform_commission')) {
                $table->decimal('platform_commission', 15, 2)->default(0)->after('worker_reward_per_task');
            }
            // Add task_type_id if not exists
            if (!Schema::hasColumn('tasks', 'task_type_id')) {
                $table->unsignedBigInteger('task_type_id')->nullable()->after('category_id');
                $table->foreign('task_type_id')->references('id')->on('task_types')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['bundle_id']);
            $table->dropForeign(['task_type_id']);
            $table->dropColumn([
                'minimum_budget',
                'is_bundled',
                'bundle_id',
                'worker_reward_per_task',
                'platform_commission',
                'task_type_id',
            ]);
        });
    }
};
