<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('digital_products')) {
            Schema::create('digital_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('description');
                $table->decimal('price', 10, 2);
                $table->decimal('sale_price', 10, 2)->nullable();
                $table->string('thumbnail')->nullable();
                $table->string('file_path')->nullable();
                $table->string('file_size')->nullable();
                $table->string('file_type')->nullable();
                $table->foreignId('category_id')->nullable()->constrained('marketplace_categories')->onDelete('set null');
                $table->json('tags')->nullable();
                $table->integer('downloads')->default(0);
                $table->integer('total_sales')->default(0);
                $table->decimal('rating', 3, 2)->default(0);
                $table->integer('rating_count')->default(0);
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_free')->default(false);
                $table->integer('license_type')->default(1); // 1=personal, 2=commercial, 3=extended
                $table->integer('version')->default(1);
                $table->text('changelog')->nullable();
                $table->text('requirements')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('digital_product_orders')) {
            Schema::create('digital_product_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('digital_products')->onDelete('cascade');
                $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
                $table->string('order_number')->unique();
                $table->decimal('amount', 10, 2);
                $table->decimal('commission', 10, 2)->default(0);
                $table->decimal('platform_fee', 10, 2)->default(0);
                $table->decimal('seller_earnings', 10, 2)->default(0);
                $table->string('license_type')->default('personal');
                $table->string('license_key')->nullable();
                $table->string('download_token')->nullable();
                $table->timestamp('download_expires_at')->nullable();
                $table->integer('download_count')->default(0);
                $table->integer('max_downloads')->default(5);
                $table->enum('status', ['pending', 'completed', 'refunded', 'disputed'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('digital_product_reviews')) {
            Schema::create('digital_product_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('digital_products')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->integer('rating');
                $table->text('comment')->nullable();
                $table->json('attachments')->nullable();
                $table->boolean('is_verified_purchase')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_product_reviews');
        Schema::dropIfExists('digital_product_orders');
        Schema::dropIfExists('digital_products');
    }
};
