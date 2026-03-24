<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('task_completions', 'submitted_at')) {
            return;
        }

        if (Schema::hasColumn('task_completions', 'user_agent')) {
            Schema::table('task_completions', function (Blueprint $table) {
                $table->timestamp('submitted_at')->nullable()->after('user_agent');
            });
        } else {
            Schema::table('task_completions', function (Blueprint $table) {
                $table->timestamp('submitted_at')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('task_completions', 'submitted_at')) {
            Schema::table('task_completions', function (Blueprint $table) {
                $table->dropColumn('submitted_at');
            });
        }
    }
};
