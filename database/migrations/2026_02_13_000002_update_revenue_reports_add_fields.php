<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('revenue_reports', function (Blueprint $table) {
            $table->integer('transaction_count')->default(0)->after('platform_net');
            $table->decimal('task_amount', 16, 4)->default(0)->after('transaction_count');
            $table->decimal('total_deposits', 16, 4)->default(0)->after('task_amount');
            $table->decimal('pending_withdrawals', 16, 4)->default(0)->after('total_deposits');
        });
    }

    public function down()
    {
        Schema::table('revenue_reports', function (Blueprint $table) {
            $table->dropColumn(['transaction_count','task_amount','total_deposits','pending_withdrawals']);
        });
    }
};
