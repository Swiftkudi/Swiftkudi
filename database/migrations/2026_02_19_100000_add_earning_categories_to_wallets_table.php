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
        Schema::table('wallets', function (Blueprint $table) {
            // Add separate earning category fields
            $table->decimal('total_task_earnings', 15, 2)->default(0)->after('total_earned');
            $table->decimal('total_referral_bonuses', 15, 2)->default(0)->after('total_task_earnings');
            $table->decimal('total_deposits', 15, 2)->default(0)->after('total_referral_bonuses');
            $table->decimal('total_fees', 15, 2)->default(0)->after('total_deposits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn([
                'total_task_earnings',
                'total_referral_bonuses',
                'total_deposits',
                'total_fees',
            ]);
        });
    }
};
