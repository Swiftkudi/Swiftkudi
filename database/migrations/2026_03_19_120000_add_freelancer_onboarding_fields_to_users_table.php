<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFreelancerOnboardingFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'freelancer_activation_paid')) {
                $table->boolean('freelancer_activation_paid')->default(false)->after('activation_paid');
            }
            if (!Schema::hasColumn('users', 'freelancer_profile_completed')) {
                $table->boolean('freelancer_profile_completed')->default(false)->after('freelancer_activation_paid');
            }
            if (!Schema::hasColumn('users', 'freelancer_service_created')) {
                $table->boolean('freelancer_service_created')->default(false)->after('freelancer_profile_completed');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['freelancer_activation_paid', 'freelancer_profile_completed', 'freelancer_service_created']);
        });
    }
}
