<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('tasks', 'is_sample')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->boolean('is_sample')->default(false)->after('is_featured');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tasks', 'is_sample')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('is_sample');
            });
        }
    }
};