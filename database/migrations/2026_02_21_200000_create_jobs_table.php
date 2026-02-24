<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DUPLICATE MIGRATION - Consolidated into 2026_02_20_150000_create_job_board_tables.php
     * This file is kept for reference only and does nothing when run.
     * jobs and job_applications are defined in the earlier job_board_tables migration (Feb 20).
     */
    public function up(): void
    {
        // jobs and job_applications are created in 2026_02_20_150000_create_job_board_tables.php
        
        /*
        Schema::create('jobs', function (Blueprint $table) {
        })
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // All table drops are handled in 2026_02_20_150000_create_job_board_tables.php
    }
};
