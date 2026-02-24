<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if marketplace_categories constraint already exists
        if (!Schema::hasTable('digital_products')) {
            return;
        }

        Schema::table('digital_products', function (Blueprint $table) {
            // Safely drop the old constraint if it exists (task_categories reference)
            try {
                $table->dropForeign(['category_id']);
            } catch (\Exception $e) {
                // Constraint might already be corrected or not exist
            }
        });

        // Add marketplace_categories constraint (safe if already exists)
        Schema::table('digital_products', function (Blueprint $table) {
            try {
                $table->foreign('category_id')
                    ->references('id')
                    ->on('marketplace_categories')
                    ->onDelete('set null');
            } catch (\Exception $e) {
                // Foreign key may already exist
            }
        });
    }

    public function down(): void
    {
        Schema::table('digital_products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::table('digital_products', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('task_categories')
                ->onDelete('set null');
        });
    }
};
