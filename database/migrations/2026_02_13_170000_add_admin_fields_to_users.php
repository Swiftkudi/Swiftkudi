<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add is_admin field if it doesn't exist
        if (!Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_admin')->default(false)->after('password');
            });
        }

        // Add admin_role_id field if it doesn't exist
        if (!Schema::hasColumn('users', 'admin_role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('admin_role_id')->nullable()->after('is_admin');
                $table->foreign('admin_role_id')->references('id')->on('admin_roles')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['admin_role_id']);
            $table->dropColumn(['is_admin', 'admin_role_id']);
        });
    }
};
