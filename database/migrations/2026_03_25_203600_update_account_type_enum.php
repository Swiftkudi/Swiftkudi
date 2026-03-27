<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateAccountTypeEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // MySQL requires changing enum columns via a workaround
        // We'll modify the column using raw SQL
        DB::statement("ALTER TABLE users MODIFY COLUMN account_type ENUM('earner', 'task_creator', 'freelancer', 'digital_seller', 'growth_seller', 'buyer') DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN account_type ENUM('earner', 'task_creator', 'freelancer', 'buyer') DEFAULT 'earner'");
    }
}