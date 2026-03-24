<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add task creation gate fields to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'total_created_task_budget')) {
                $table->decimal('total_created_task_budget', 15, 2)->default(0)->after('total_earnings');
            }
            if (!Schema::hasColumn('users', 'has_completed_task_creation_gate')) {
                $table->boolean('has_completed_task_creation_gate')->default(false)->after('total_created_task_budget');
            }
            if (!Schema::hasColumn('users', 'task_creation_gate_completed_at')) {
                $table->timestamp('task_creation_gate_completed_at')->nullable()->after('has_completed_task_creation_gate');
            }
        });

        // Add system setting for minimum required creation amount
        Schema::table('system_settings', function (Blueprint $table) {
            // Ensure the table exists first
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'total_created_task_budget',
                'has_completed_task_creation_gate',
                'task_creation_gate_completed_at',
            ]);
        });
    }
};
