<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Professional Service Categories (must be first due to foreign key)
        Schema::create('professional_service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Professional Services (service listings)
        Schema::create('professional_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->foreignId('category_id')->nullable()->constrained('professional_service_categories')->onDelete('set null');
            $table->decimal('price', 10, 2);
            $table->integer('delivery_days');
            $table->integer('revisions_included')->default(1);
            $table->text('portfolio_links')->nullable(); // JSON array of URLs
            $table->text('portfolio_images')->nullable(); // JSON array of image paths
            $table->enum('status', ['draft', 'pending', 'active', 'paused', 'rejected', 'deleted'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('featured_until')->nullable(); // timestamp
            $table->timestamps();
            
            $table->index(['status', 'category_id']);
            $table->index('user_id');
        });

        // Professional Service Add-ons
        Schema::create('professional_service_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('professional_services')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('delivery_days_extra')->default(0);
            $table->timestamps();
        });

        // Professional Service Orders
        Schema::create('professional_service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('professional_services')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->decimal('service_price', 10, 2);
            $table->decimal('addons_total', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('platform_commission', 10, 2)->default(0);
            $table->decimal('seller_payout', 10, 2)->default(0);
            $table->decimal('escrow_amount', 10, 2)->default(0); // Amount held in escrow
            $table->decimal('paid_amount', 10, 2)->default(0); // Amount paid by buyer
            $table->enum('status', [
                'pending',      // Order created, waiting for payment
                'paid',        // Payment received, in escrow
                'in_progress', // Seller started working
                'delivered',   // Seller delivered
                'revision',    // Buyer requested revision
                'completed',   // Buyer approved
                'cancelled',   // Order cancelled
                'disputed',    // Dispute opened
                'refunded'     // Refunded to buyer
            ])->default('pending');
            $table->text('requirements')->nullable(); // Buyer's initial requirements
            $table->text('delivery_notes')->nullable(); // Seller's delivery notes
            $table->text('delivery_files')->nullable(); // JSON of delivered files/links
            $table->integer('revisions_used')->default(0);
            $table->integer('revisions_requested')->default(0);
            $table->text('revision_notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index('service_id');
        });

        // Professional Service Messages (inside orders)
        Schema::create('professional_service_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('professional_service_orders')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->text('attachments')->nullable(); // JSON array of file paths
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // Professional Service Reviews
        Schema::create('professional_service_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('professional_service_orders')->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewee_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->text('response')->nullable(); // Seller's response
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            
            $table->unique(['order_id', 'reviewer_id']);
            $table->index('reviewee_id');
        });

        // Service Provider Profiles (for directory)
        Schema::create('service_provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_available')->default(true);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->text('bio')->nullable();
            $table->text('skills')->nullable(); // JSON array of skills/tags
            $table->text('portfolio_links')->nullable(); // JSON array
            $table->text('certifications')->nullable(); // JSON array
            $table->integer('total_orders_completed')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_provider_profiles');
        Schema::dropIfExists('professional_service_reviews');
        Schema::dropIfExists('professional_service_messages');
        Schema::dropIfExists('professional_service_orders');
        Schema::dropIfExists('professional_service_addons');
        Schema::dropIfExists('professional_services');
        Schema::dropIfExists('professional_service_categories');
    }
};
