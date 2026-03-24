<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('task_completions')) {
            Schema::table('task_completions', function (Blueprint $table) {
                if (!Schema::hasColumn('task_completions', 'xp_earned')) {
                    $table->integer('xp_earned')->default(0)->after('promo_credit_earned');
                }
                if (!Schema::hasColumn('task_completions', 'promo_credit_earned')) {
                    $table->decimal('promo_credit_earned', 12, 2)->default(0)->after('reward_amount');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('task_completions')) {
            Schema::table('task_completions', function (Blueprint $table) {
                if (Schema::hasColumn('task_completions', 'xp_earned')) {
                    $table->dropColumn('xp_earned');
                }
                if (Schema::hasColumn('task_completions', 'promo_credit_earned')) {
                    $table->dropColumn('promo_credit_earned');
                }
            });
        }
    }
};
