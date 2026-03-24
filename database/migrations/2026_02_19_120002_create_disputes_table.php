<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DUPLICATE MIGRATION - Consolidated into 2026_02_19_200000_create_marketplace_tables.php
     * This file is kept for reference only and does nothing when run.
     * disputes creation is handled in the marketplace migration with a more complete schema.
     */
    public function up(): void
    {
        // disputes is created in 2026_02_19_200000_create_marketplace_tables.php
        
        /*
        Schema::create('disputes', function (Blueprint $table) {
        })
        */
    }

    public function down(): void
    {
        // All table drops are handled in 2026_02_19_200000_create_marketplace_tables.php
    }
};
