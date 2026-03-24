<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('task_completions')) {
            return;
        }

        Schema::table('task_completions', function (Blueprint $table) {
            // Keep existing columns; only add missing ones used by the app
            if (! Schema::hasColumn('task_completions', 'reward_amount')) {
                $table->decimal('reward_amount', 10, 2)->default(0)->after('reward_earned');
            }

            if (! Schema::hasColumn('task_completions', 'proof_data')) {
                // JSON columns can be problematic on older SQLite/MySQL setups; use text for compatibility
                $table->text('proof_data')->nullable()->after('status');
            }

            if (! Schema::hasColumn('task_completions', 'worker_notes')) {
                $table->text('worker_notes')->nullable()->after('proof_description');
            }

            if (! Schema::hasColumn('task_completions', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('admin_note');
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('task_completions')) {
            return;
        }

        Schema::table('task_completions', function (Blueprint $table) {
            if (Schema::hasColumn('task_completions', 'reward_amount')) {
                $table->dropColumn('reward_amount');
            }

            if (Schema::hasColumn('task_completions', 'proof_data')) {
                $table->dropColumn('proof_data');
            }

            if (Schema::hasColumn('task_completions', 'worker_notes')) {
                $table->dropColumn('worker_notes');
            }

            if (Schema::hasColumn('task_completions', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
        });
    }
};
