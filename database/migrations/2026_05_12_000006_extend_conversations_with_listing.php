<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marketplace_conversations')) {
            Schema::table('marketplace_conversations', function (Blueprint $table) {
                if (!Schema::hasColumn('marketplace_conversations', 'listing_id')) {
                    $table->unsignedBigInteger('listing_id')->nullable()->after('reference_id');
                    $table->foreign('listing_id')->references('id')->on('marketplace_listings')->onDelete('set null');
                    $table->index('listing_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('marketplace_conversations') && Schema::hasColumn('marketplace_conversations', 'listing_id')) {
            Schema::table('marketplace_conversations', function (Blueprint $table) {
                $table->dropForeign(['listing_id']);
                $table->dropColumn('listing_id');
            });
        }
    }
};