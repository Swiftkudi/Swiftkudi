<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jobs') && Schema::hasColumn('jobs', 'views') && !Schema::hasColumn('jobs', 'views_count')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->renameColumn('views', 'views_count');
            });
        }

        if (Schema::hasTable('job_bookmarks')) {
            Schema::table('job_bookmarks', function (Blueprint $table) {
                $table->index(['user_id', 'job_id'], 'job_bookmarks_user_job_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('jobs') && Schema::hasColumn('jobs', 'views_count') && !Schema::hasColumn('jobs', 'views')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->renameColumn('views_count', 'views');
            });
        }

        if (Schema::hasTable('job_bookmarks')) {
            Schema::table('job_bookmarks', function (Blueprint $table) {
                $table->dropIndex('job_bookmarks_user_job_index');
            });
        }
    }
};
