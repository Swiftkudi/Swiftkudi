<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('marketplace_listings')) {
            Schema::create('marketplace_listings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('category_id')->nullable()->constrained('marketplace_categories')->onDelete('set null');
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description');
                $table->enum('condition', ['new', 'like_new', 'good', 'fair', 'used'])->default('good');
                $table->decimal('price', 15, 2);
                $table->boolean('negotiable')->default(false);
                $table->json('images')->nullable();
                $table->string('thumbnail')->nullable();
                $table->json('tags')->nullable();
                $table->string('location')->nullable();
                $table->boolean('available_for_shipping')->default(false);
                $table->decimal('shipping_cost', 10, 2)->default(0);
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_active')->default(true);
                $table->enum('status', ['draft', 'pending_review', 'active', 'sold', 'expired', 'flagged', 'removed'])->default('draft');
                $table->integer('views_count')->default(0);
                $table->integer('favourites_count')->default(0);
                $table->timestamp('sold_at')->nullable();
                $table->timestamps();

                $table->index(['category_id', 'status']);
                $table->index(['user_id', 'status']);
                $table->index(['is_featured', 'is_active']);
                $table->index('created_at');
                $table->fullText(['title', 'description']);
$table->index('tags');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_listings');
    }
};