<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('task_completions', function (Blueprint $table) {
            if (!Schema::hasColumn('task_completions', 'proof_data')) {
                $table->json('proof_data')->nullable()->after('proof_screenshot');
            }
            if (!Schema::hasColumn('task_completions', 'worker_notes')) {
                $table->text('worker_notes')->nullable()->after('proof_description');
            }
        });
    }

    public function down()
    {
        Schema::table('task_completions', function (Blueprint $table) {
            $table->dropColumn(['proof_data', 'worker_notes']);
        });
    }
};
