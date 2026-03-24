<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * DUPLICATE MIGRATION - Consolidated into 2026_02_20_150000_create_job_board_tables.php
     * This file is kept for reference only and does nothing when run.
     * The skeleton jobs table creation is superseded by the full job board implementation.
     */
    public function up()
    {
        // jobs, job_applications, and job_bookmarks are created in 2026_02_20_150000_create_job_board_tables.php
    }

    public function down()
    {
        // All table drops are handled in 2026_02_20_150000_create_job_board_tables.php
    }
}
