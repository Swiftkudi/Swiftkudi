<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DUPLICATE MIGRATION - Consolidated into 2026_02_20_140000_create_boost_tables.php
     * This file is kept for reference only and does nothing when run.
     * The more complete boost_packages (with slug, icon, color, position) is in the later migration.
     */
    public function up(): void
    {
        // boost_packages is created in 2026_02_20_140000_create_boost_tables.php
    }

    public function down(): void
    {
        // All table drops are handled in 2026_02_20_140000_create_boost_tables.php
    }
};
