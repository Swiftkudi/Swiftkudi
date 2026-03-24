<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table for tracking user activations and revenue
     */
    public function up(): void
    {
        Schema::create('activation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('referrer_id')->nullable()->constrained('users')->onDelete('set null'); // Who referred
            $table->decimal('activation_fee', 15, 2);
            $table->decimal('referral_bonus', 15, 2)->default(0); // Bonus paid to referrer
            $table->decimal('platform_revenue', 15, 2); // Fee minus referral bonus
            $table->string('payment_method')->nullable(); // wallet, card, bank
            $table->string('payment_gateway')->nullable(); // paystack, stripe
            $table->string('reference')->nullable(); // Payment reference
            $table->string('status')->default('completed'); // pending, completed, failed, refunded
            $table->string('activation_type')->default('normal'); // normal, referral, promo
            $table->timestamp('activated_at')->useCurrent();
            $table->timestamps();

            // Indexes
            $table->index(['user_id']);
            $table->index(['referrer_id']);
            $table->index(['activated_at']);
            $table->index(['status']);
            $table->index(['activation_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activation_logs');
    }
};
