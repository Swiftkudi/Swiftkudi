<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('digital_products') || !Schema::hasTable('marketplace_categories')) {
            return;
        }

        $constraintName = 'digital_products_category_id_foreign';

        $constraintExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', 'digital_products')
            ->where('CONSTRAINT_NAME', $constraintName)
            ->exists();

        if ($constraintExists) {
            DB::statement('ALTER TABLE `digital_products` DROP FOREIGN KEY `digital_products_category_id_foreign`');
        }

        $constraintExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', 'digital_products')
            ->where('CONSTRAINT_NAME', $constraintName)
            ->exists();

        if (!$constraintExists) {
            DB::statement('ALTER TABLE `digital_products` ADD CONSTRAINT `digital_products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `marketplace_categories`(`id`) ON DELETE SET NULL');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('digital_products')) {
            return;
        }

        $constraintName = 'digital_products_category_id_foreign';

        $constraintExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', 'digital_products')
            ->where('CONSTRAINT_NAME', $constraintName)
            ->exists();

        if ($constraintExists) {
            DB::statement('ALTER TABLE `digital_products` DROP FOREIGN KEY `digital_products_category_id_foreign`');
        }

        if (Schema::hasTable('task_categories')) {
            $constraintExists = DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
                ->where('TABLE_NAME', 'digital_products')
                ->where('CONSTRAINT_NAME', $constraintName)
                ->exists();

            if (!$constraintExists) {
                DB::statement('ALTER TABLE `digital_products` ADD CONSTRAINT `digital_products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `task_categories`(`id`) ON DELETE SET NULL');
            }
        }
    }
};
