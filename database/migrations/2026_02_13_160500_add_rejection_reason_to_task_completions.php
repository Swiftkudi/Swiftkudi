<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('task_completions')) {
            return;
        }

        Schema::table('task_completions', function (Blueprint $table) {
            if (! Schema::hasColumn('task_completions', 'rejection_reason')) {
                $table->string('rejection_reason')->nullable()->after('admin_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasTable('task_completions')) {
            return;
        }

        Schema::table('task_completions', function (Blueprint $table) {
            if (Schema::hasColumn('task_completions', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
