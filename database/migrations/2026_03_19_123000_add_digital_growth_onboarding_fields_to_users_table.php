<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDigitalGrowthOnboardingFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'digital_activation_paid')) {
                $table->boolean('digital_activation_paid')->default(false)->after('freelancer_service_created');
            }
            if (!Schema::hasColumn('users', 'digital_product_uploaded')) {
                $table->boolean('digital_product_uploaded')->default(false)->after('digital_activation_paid');
            }
            if (!Schema::hasColumn('users', 'growth_activation_paid')) {
                $table->boolean('growth_activation_paid')->default(false)->after('digital_product_uploaded');
            }
            if (!Schema::hasColumn('users', 'growth_listing_created')) {
                $table->boolean('growth_listing_created')->default(false)->after('growth_activation_paid');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['digital_activation_paid', 'digital_product_uploaded', 'growth_activation_paid', 'growth_listing_created']);
        });
    }
}
