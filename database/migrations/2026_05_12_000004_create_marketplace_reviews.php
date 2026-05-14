<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('marketplace_reviews')) {
            Schema::create('marketplace_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('marketplace_orders')->onDelete('cascade');
                $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('reviewed_id')->constrained('users')->onDelete('cascade');
                $table->unsignedTinyInteger('rating');
                $table->text('comment')->nullable();
                $table->json('images')->nullable();
                $table->boolean('is_approved')->default(false);
                $table->timestamps();

                $table->unique(['order_id', 'reviewer_id']);
                $table->index('reviewed_id');
                $table->index('is_approved');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_reviews');
    }
};