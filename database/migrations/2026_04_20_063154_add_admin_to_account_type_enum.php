<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAdminToAccountTypeEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds 'admin' to the account_type ENUM values and updates existing admin users.
     */
    public function up(): void
    {
        // Update the ENUM to include 'admin'
        DB::statement("ALTER TABLE users MODIFY COLUMN account_type ENUM('earner', 'task_creator', 'freelancer', 'digital_seller', 'growth_seller', 'buyer', 'admin') DEFAULT NULL");

        // Set account_type = 'admin' for all admin users (is_admin = true or has admin_role_id)
        DB::table('users')
            ->where('is_admin', true)
            ->orWhereNotNull('admin_role_id')
            ->update(['account_type' => 'admin']);
    }

    /**
     * Reverse the migrations.
     *
     * Removes 'admin' from ENUM and reverts admin users' account_type to NULL
     */
    public function down(): void
    {
        // Remove 'admin' from ENUM - set admin users' account_type to NULL first
        DB::table('users')
            ->where('account_type', 'admin')
            ->update(['account_type' => null]);

        // Revert ENUM to original values
        DB::statement("ALTER TABLE users MODIFY COLUMN account_type ENUM('earner', 'task_creator', 'freelancer', 'digital_seller', 'growth_seller', 'buyer') DEFAULT NULL");
    }
}
