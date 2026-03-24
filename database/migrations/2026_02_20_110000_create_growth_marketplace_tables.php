<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DUPLICATE MIGRATION - Consolidated into 2026_02_19_200000_create_marketplace_tables.php
     * This file is kept for reference only and does nothing when run.
     * All growth_listings, growth_orders, and growth_categories creation is handled
     * in the earlier migration to prevent duplication.
     */
    public function up(): void
    {
        // All tables are created in 2026_02_19_200000_create_marketplace_tables.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // All table drops are handled in 2026_02_19_200000_create_marketplace_tables.php
    }
};
