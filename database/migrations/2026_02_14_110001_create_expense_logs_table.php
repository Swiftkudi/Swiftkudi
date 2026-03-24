<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table for logging manual and auto-tracked expenses
     */
    public function up(): void
    {
        Schema::create('expense_logs', function (Blueprint $table) {
            $table->id();
            $table->string('expense_type'); // gateway_fee, server_cost, email_cost, staff_cost, referral_bonus, custom
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('NGN');
            $table->string('payment_gateway')->nullable(); // For gateway fees
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->date('expense_date');
            $table->string('status')->default('approved'); // pending, approved, rejected
            $table->text('notes')->nullable();
            $table->string('attachment_url')->nullable(); // Receipt attachment
            $table->string('recurring_type')->nullable(); // daily, weekly, monthly, yearly
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();

            // Indexes
            $table->index(['expense_type']);
            $table->index(['expense_date']);
            $table->index(['status']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_logs');
    }
};
