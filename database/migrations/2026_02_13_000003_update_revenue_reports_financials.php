<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('revenue_reports', function (Blueprint $table) {
            $table->decimal('total_transactions_amount', 16, 4)->default(0)->after('pending_withdrawals');
            $table->decimal('total_wallet_balance', 16, 4)->default(0)->after('total_transactions_amount');
            $table->decimal('total_withdrawable_balance', 16, 4)->default(0)->after('total_wallet_balance');
            $table->decimal('total_withdrawn', 16, 4)->default(0)->after('total_withdrawable_balance');
            $table->decimal('admin_deposits', 16, 4)->default(0)->after('total_withdrawn');
            $table->decimal('activation_fees', 16, 4)->default(0)->after('admin_deposits');
            $table->decimal('commission_fees', 16, 4)->default(0)->after('activation_fees');
        });
    }

    public function down()
    {
        Schema::table('revenue_reports', function (Blueprint $table) {
            $table->dropColumn([
                'total_transactions_amount',
                'total_wallet_balance',
                'total_withdrawable_balance',
                'total_withdrawn',
                'admin_deposits',
                'activation_fees',
                'commission_fees',
            ]);
        });
    }
};
