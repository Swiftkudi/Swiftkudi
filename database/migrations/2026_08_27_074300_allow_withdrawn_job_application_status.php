<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('job_applications') || !Schema::hasColumn('job_applications', 'status')) {
            return;
        }

        // The original MySQL enum omitted "withdrawn" even though the application
        // already used that status. A varchar keeps the workflow extensible and
        // avoids another destructive enum migration for future proposal states.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE job_applications MODIFY status VARCHAR(32) NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        // Do not restore the restrictive enum: production rows may legitimately
        // contain workflow states added after this migration (including withdrawn).
    }
};
