<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User verification levels/badges
        Schema::create('user_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('id_number')->nullable(); // Government ID
            $table->string('id_type')->nullable(); // passport, national_id, drivers_license
            $table->string('id_document_path')->nullable();
            $table->string('selfie_path')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone_verified_at')->nullable();
            $table->string('id_verified_at')->nullable();
            $table->string('address_verified_at')->nullable();
            $table->enum('status', ['pending', 'submitted', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // User badges/levels
        Schema::create('user_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Newcomer, Bronze, Silver, Gold, Platinum
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('#6B7280'); // Gray
            $table->integer('min_tasks')->default(0);
            $table->integer('min_earnings')->default(0);
            $table->integer('min_referrals')->default(0);
            $table->integer('level_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // User level history
        Schema::create('user_level_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('level_id')->constrained('user_levels')->onDelete('cascade');
            $table->string('reason')->nullable(); // auto_promotion, manual, referral_bonus
            $table->timestamps();
        });

        // User performance metrics
        Schema::create('user_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->unique();
            $table->integer('total_tasks_completed')->default(0);
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->integer('total_referrals')->default(0);
            $table->integer('total_sales')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->integer('consecutive_days_active')->default(0);
            $table->date('last_active_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_performance_metrics');
        Schema::dropIfExists('user_level_history');
        Schema::dropIfExists('user_levels');
        Schema::dropIfExists('user_verifications');
    }
};
