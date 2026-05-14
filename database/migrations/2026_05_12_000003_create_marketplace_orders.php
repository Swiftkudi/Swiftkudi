<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('marketplace_orders')) {
            Schema::create('marketplace_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('listing_id')->constrained('marketplace_listings')->onDelete('cascade');
                $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
                $table->decimal('amount', 15, 2);
                $table->decimal('platform_fee', 15, 2)->default(0);
                $table->decimal('shipping_cost', 10, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->decimal('escrow_amount', 15, 2)->default(0);
                $table->enum('status', [
                    'pending', 'paid', 'in_progress', 'delivered',
                    'completed', 'disputed', 'cancelled', 'refunded', 'expired',
                ])->default('pending');
                $table->string('shipping_address')->nullable();
                $table->string('shipping_method')->nullable();
                $table->text('buyer_notes')->nullable();
                $table->text('seller_notes')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index(['buyer_id', 'status']);
                $table->index(['seller_id', 'status']);
                $table->index(['listing_id', 'status']);
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_orders');
    }
};