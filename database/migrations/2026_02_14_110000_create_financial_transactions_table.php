<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Central table for all financial transactions
     */
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type'); // revenue, expense
            $table->string('category'); // activation, withdrawal, gateway_fee, referral_bonus, etc.
            $table->string('sub_category')->nullable(); // normal_activation, referral_activation
            $table->decimal('amount', 15, 2);
            $table->decimal('amount_usd', 15, 2)->nullable(); // For multi-currency
            $table->string('currency')->default('NGN');
            $table->string('payment_gateway')->nullable(); // paystack, stripe, etc.
            $table->string('reference')->nullable(); // External transaction reference
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Extra data
            $table->string('status')->default('completed'); // pending, completed, failed, cancelled
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamps();

            // Indexes for performance
            $table->index(['transaction_type', 'category']);
            $table->index(['transaction_date']);
            $table->index(['user_id']);
            $table->index(['payment_gateway']);
            $table->index(['status']);
            $table->index(['reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
