<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration removes the auto-assignment of 'earner' account type to users during registration.
     * It ensures that users must select their account type during onboarding instead of being auto-assigned.
     */
    public function up(): void
    {
        // Reset any users who may have been incorrectly auto-assigned 'earner' as their account type
        // This ensures users go through the proper onboarding flow to select their account type
        DB::table('users')
            ->where('account_type', 'earner')
            ->whereNull('onboarding_completed')
            ->update(['account_type' => null]);

        // Also reset any users who have account_type but haven't completed onboarding
        // This catches users who might have been assigned other account types incorrectly
        DB::table('users')
            ->whereNotNull('account_type')
            ->where('onboarding_completed', false)
            ->update(['account_type' => null]);

        // Ensure account_type column has no default value - users must select during onboarding
        // Use raw SQL to avoid Doctrine DBAL enum type issues
        DB::statement("ALTER TABLE users MODIFY COLUMN account_type ENUM('earner', 'task_creator', 'freelancer', 'digital_seller', 'growth_seller', 'buyer') NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is one-way - we don't want to re-assign 'earner' to users
        // The reversal would require manual intervention to determine which users should have 'earner'
    }
};
