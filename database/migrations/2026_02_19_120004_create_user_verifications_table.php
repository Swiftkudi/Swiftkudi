<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DUPLICATE MIGRATION - Consolidated into 2026_02_20_130000_create_user_verification_tables.php
     * This file is kept for reference only and does nothing when run.
     * The user verification system including levels and metrics is in the later, more complete migration.
     */
    public function up(): void
    {
        // user_verifications and related tables are created in 2026_02_20_130000_create_user_verification_tables.php
    }

    public function down(): void
    {
        // All table drops are handled in 2026_02_20_130000_create_user_verification_tables.php
    }
};
