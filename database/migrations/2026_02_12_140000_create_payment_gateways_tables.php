<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Payment gateway settings table
        if (!Schema::hasTable('payment_gateways')) {
            Schema::create('payment_gateways', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // paystack, kora, stripe
                $table->string('display_name'); // Paystack, Kora, Stripe
                $table->text('api_key')->nullable();
                $table->text('secret_key')->nullable();
                $table->text('webhook_secret')->nullable();
                $table->text('public_key')->nullable();
                $table->boolean('is_sandbox')->default(true);
                $table->boolean('is_active')->default(false);
                $table->json('supported_currencies')->nullable(); // ["NGN", "USD", "USDT"]
                $table->json('settings')->nullable(); // Additional gateway-specific settings
                $table->timestamps();
            });
        }

        // Payment transactions table
        if (!Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 100)->unique();
                $table->string('gateway_reference', 200)->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('gateway'); // paystack, kora, stripe
                $table->string('type'); // deposit, activation, withdrawal, refund
                $table->string('currency', 10)->default('NGN');
                $table->decimal('amount', 15, 2);
                $table->decimal('fee', 15, 2)->default(0);
                $table->decimal('net_amount', 15, 2)->default(0);
                $table->string('status', 50)->default('pending'); // pending, processing, completed, failed, refunded
                $table->string('payment_method', 50)->nullable(); // card, bank, usdt, etc.
                $table->json('gateway_response')->nullable();
                $table->json('metadata')->nullable();
                $table->text('failure_reason')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                $table->index(['user_id', 'status']);
                $table->index(['gateway', 'status']);
                $table->index(['reference']);
                $table->index(['created_at']);
            });
        }

        // Currency rates table (for manual/override rates)
        if (!Schema::hasTable('currency_rates')) {
            Schema::create('currency_rates', function (Blueprint $table) {
                $table->id();
                $table->string('currency', 10)->unique(); // USD, EUR, GBP, USDT
                $table->decimal('rate_to_ngn', 15, 8); // Rate to NGN
                $table->boolean('is_auto')->default(false); // Is fetched from API
                $table->timestamp('last_updated')->nullable();
                $table->timestamps();
            });
        }

        // User currency preferences
        if (!Schema::hasTable('user_currency_preferences')) {
            Schema::create('user_currency_preferences', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('currency', 10)->default('NGN');
                $table->string('detected_currency', 10)->nullable();
                $table->string('country_code', 10)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->boolean('is_manual')->default(false);
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_currency_preferences');
        Schema::dropIfExists('currency_rates');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payment_gateways');
    }
};
