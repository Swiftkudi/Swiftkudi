<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnboardingFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'account_type')) {
                $table->enum('account_type', ['earner', 'task_creator', 'freelancer', 'digital_seller', 'growth_seller', 'buyer'])->nullable()->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'onboarding_completed')) {
                $table->boolean('onboarding_completed')->default(false)->after('account_type');
            }
            if (!Schema::hasColumn('users', 'activation_paid')) {
                $table->boolean('activation_paid')->default(false)->after('onboarding_completed');
            }
            if (!Schema::hasColumn('users', 'initial_task_completed')) {
                $table->boolean('initial_task_completed')->default(false)->after('activation_paid');
            }
            if (!Schema::hasColumn('users', 'onboarding_started_at')) {
                $table->timestamp('onboarding_started_at')->nullable()->after('initial_task_completed');
            }
            if (!Schema::hasColumn('users', 'onboarding_completed_at')) {
                $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_started_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
