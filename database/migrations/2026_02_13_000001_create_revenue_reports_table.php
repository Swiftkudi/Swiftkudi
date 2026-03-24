<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('revenue_reports', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('currency', 8)->nullable();
            $table->string('gateway')->nullable();
            $table->decimal('gross_amount', 20, 4)->default(0);
            $table->decimal('gateway_fees', 20, 4)->default(0);
            $table->decimal('refunds', 20, 4)->default(0);
            $table->decimal('worker_payouts', 20, 4)->default(0);
            $table->decimal('commissions_paid', 20, 4)->default(0);
            $table->decimal('taxes', 20, 4)->default(0);
            $table->decimal('platform_net', 20, 4)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['date','currency','gateway'], 'revenue_reports_uq');
            $table->index(['date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_reports');
    }
};
