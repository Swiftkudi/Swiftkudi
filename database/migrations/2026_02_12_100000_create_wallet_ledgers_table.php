<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DUPLICATE MIGRATION - Consolidated into 2026_02_12_000000_create_earndesk_tables.php
     * This file is kept for reference only and does nothing when run.
     * wallet_ledgers creation is handled in the earlier migration to prevent duplication.
     */
    public function up()
    {
        // wallet_ledgers is created in 2026_02_12_000000_create_earndesk_tables.php
    }
    
    public function down()
    {
        // All table drops are handled in 2026_02_12_000000_create_earndesk_tables.php
    }
};
