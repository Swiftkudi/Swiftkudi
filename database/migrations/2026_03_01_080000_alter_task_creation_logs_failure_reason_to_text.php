<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('task_creation_logs') || !Schema::hasColumn('task_creation_logs', 'failure_reason')) {
            return;
        }

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE task_creation_logs MODIFY failure_reason TEXT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE task_creation_logs ALTER COLUMN failure_reason TYPE TEXT');
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement('ALTER TABLE task_creation_logs ALTER COLUMN failure_reason NVARCHAR(MAX) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('task_creation_logs') || !Schema::hasColumn('task_creation_logs', 'failure_reason')) {
            return;
        }

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE task_creation_logs MODIFY failure_reason VARCHAR(255) NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE task_creation_logs ALTER COLUMN failure_reason TYPE VARCHAR(255)');
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement('ALTER TABLE task_creation_logs ALTER COLUMN failure_reason NVARCHAR(255) NULL');
        }
    }
};
