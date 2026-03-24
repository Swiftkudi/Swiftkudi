<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Boost packages (for featured listings)
        Schema::create('boost_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Gold Boost", "Silver Boost"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('duration_days'); // Duration in days
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->string('icon')->nullable();
            $table->string('color')->default('#6B7280');
            $table->integer('position')->default(0); // Display order
            $table->timestamps();
        });

        // Subscription plans
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Basic", "Pro", "Enterprise"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->default(0);
            $table->integer('max_tasks')->default(0); // 0 = unlimited
            $table->integer('max_services')->default(0);
            $table->integer('max_listings')->default(0);
            $table->boolean('featured_included')->default(false);
            $table->integer('featured_limit')->default(0);
            $table->boolean('priority_support')->default(false);
            $table->boolean('analytics')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->string('icon')->nullable();
            $table->string('color')->default('#6B7280');
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        // User subscriptions
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->string('status')->default('active'); // active, cancelled, expired
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly
            $table->decimal('amount_paid', 10, 2);
            $table->timestamp('started_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });

        // Active boosts (for services, products, listings)
        Schema::create('boosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained('boost_packages')->onDelete('cascade');
            $table->string('boostable_type'); // App\Models\Service, App\Models\DigitalProduct, etc.
            $table->unsignedBigInteger('boostable_id');
            $table->string('status')->default('active'); // active, expired, cancelled
            $table->decimal('amount_paid', 10, 2);
            $table->timestamp('started_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Add foreign key to user_boosts if table exists (from earlier migration)
        if (Schema::hasTable('user_boosts')) {
            Schema::table('user_boosts', function (Blueprint $table) {
                try {
                    // Add the deferred foreign key constraint from Feb 19 migration
                    $table->foreign('package_id')->references('id')->on('boost_packages')->onDelete('cascade');
                } catch (\Exception $e) {
                    // Foreign key may already exist if migration was run before
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('boosts');
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('boost_packages');
    }
};
