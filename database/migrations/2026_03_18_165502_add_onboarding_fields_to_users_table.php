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
            $table->enum('account_type', ['earner', 'task_creator', 'freelancer', 'digital_seller', 'growth_seller', 'buyer'])->default('earner')->after('email_verified_at');
            $table->boolean('onboarding_completed')->default(false)->after('account_type');
            $table->boolean('activation_paid')->default(false)->after('onboarding_completed');
            $table->boolean('initial_task_completed')->default(false)->after('activation_paid');
            $table->timestamp('onboarding_started_at')->nullable()->after('initial_task_completed');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_started_at');
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
