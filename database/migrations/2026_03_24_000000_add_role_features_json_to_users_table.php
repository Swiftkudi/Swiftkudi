<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Buyer features JSON
            if (!Schema::hasColumn('users', 'buyer_features')) {
                $table->json('buyer_features')->nullable()->after('buyer_onboarding_completed');
            }
            
            // Task Creator features JSON
            if (!Schema::hasColumn('users', 'task_creator_features')) {
                $table->json('task_creator_features')->nullable()->after('buyer_features');
            }
            
            // Freelancer features JSON
            if (!Schema::hasColumn('users', 'freelancer_features')) {
                $table->json('freelancer_features')->nullable()->after('task_creator_features');
            }
            
            // Digital Seller features JSON
            if (!Schema::hasColumn('users', 'digital_seller_features')) {
                $table->json('digital_seller_features')->nullable()->after('freelancer_features');
            }
            
            // Growth Seller features JSON
            if (!Schema::hasColumn('users', 'growth_seller_features')) {
                $table->json('growth_seller_features')->nullable()->after('digital_seller_features');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'buyer_features',
                'task_creator_features',
                'freelancer_features',
                'digital_seller_features',
                'growth_seller_features',
            ]);
        });
    }
};
