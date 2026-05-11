<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_account_configs', function (Blueprint $table) {
            $table->id();
            $table->string('account_type', 50)->unique();
            $table->boolean('activation_required')->default(false);
            $table->decimal('activation_fee', 10, 2)->default(0);
            $table->boolean('enabled')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default configurations using DB insert
        $defaults = [
            ['account_type' => 'buyer', 'activation_required' => false, 'activation_fee' => 0, 'enabled' => true, 'description' => 'Buyer account - no activation fee'],
            ['account_type' => 'earner', 'activation_required' => true, 'activation_fee' => 1500, 'enabled' => true, 'description' => 'Earner account - requires activation fee'],
            ['account_type' => 'task_creator', 'activation_required' => false, 'activation_fee' => 0, 'enabled' => true, 'description' => 'Task Creator account - free activation'],
            ['account_type' => 'freelancer', 'activation_required' => false, 'activation_fee' => 0, 'enabled' => true, 'description' => 'Freelancer account - free activation'],
            ['account_type' => 'digital_seller', 'activation_required' => false, 'activation_fee' => 0, 'enabled' => true, 'description' => 'Digital Seller account - free activation'],
            ['account_type' => 'growth_seller', 'activation_required' => false, 'activation_fee' => 0, 'enabled' => true, 'description' => 'Growth Seller account - free activation'],
        ];

        foreach ($defaults as $default) {
            DB::table('onboarding_account_configs')->insert($default);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_account_configs');
    }
};