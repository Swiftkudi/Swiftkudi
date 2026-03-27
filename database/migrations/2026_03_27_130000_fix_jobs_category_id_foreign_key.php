<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('marketplace_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('task_categories')->onDelete('set null');
        });
    }
};
