<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->decimal('proposal_amount', 12, 2)->nullable()->after('cover_letter');
            $table->string('estimated_duration', 100)->nullable()->after('proposal_amount');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['proposal_amount', 'estimated_duration']);
        });
    }
};
