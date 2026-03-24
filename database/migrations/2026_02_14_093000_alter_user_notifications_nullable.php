<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Make title and message nullable so Laravel's Notification insert works
        $driver = config('database.default');
        if ($driver === 'mysql' || $driver === 'mysqli') {
            DB::statement("ALTER TABLE `user_notifications` MODIFY `title` VARCHAR(255) NULL;");
            DB::statement("ALTER TABLE `user_notifications` MODIFY `message` TEXT NULL;");
            DB::statement("ALTER TABLE `user_notifications` MODIFY `type` VARCHAR(255) NULL;");
        } else {
            // fallback for other drivers using schema builder where possible
            if (Schema::hasTable('user_notifications')) {
                Schema::table('user_notifications', function ($table) {
                    $table->string('title')->nullable()->change();
                    $table->text('message')->nullable()->change();
                    $table->string('type')->nullable()->change();
                });
            }
        }
    }

    public function down()
    {
        // Revert to not nullable (best-effort)
        $driver = config('database.default');
        if ($driver === 'mysql' || $driver === 'mysqli') {
            DB::statement("ALTER TABLE `user_notifications` MODIFY `title` VARCHAR(255) NOT NULL;");
            DB::statement("ALTER TABLE `user_notifications` MODIFY `message` TEXT NOT NULL;");
            DB::statement("ALTER TABLE `user_notifications` MODIFY `type` VARCHAR(255) NOT NULL;");
        } else {
            if (Schema::hasTable('user_notifications')) {
                Schema::table('user_notifications', function ($table) {
                    $table->string('title')->nullable(false)->change();
                    $table->text('message')->nullable(false)->change();
                    $table->string('type')->nullable(false)->change();
                });
            }
        }
    }
};
