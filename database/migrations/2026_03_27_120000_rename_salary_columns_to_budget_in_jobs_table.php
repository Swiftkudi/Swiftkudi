<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->renameColumn('salary_min', 'budget_min');
            $table->renameColumn('salary_max', 'budget_max');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->renameColumn('budget_min', 'salary_min');
            $table->renameColumn('budget_max', 'salary_max');
        });
    }
};
