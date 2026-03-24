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
        Schema::create('user_experience', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('total_xp')->default(0);
            $table->integer('current_level')->default(1);
            $table->integer('xp_for_current_level')->default(0);
            $table->integer('xp_for_next_level')->default(100);
            $table->integer('tasks_completed')->default(0);
            $table->integer('referrals_made')->default(0);
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->date('streak_start_date')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('current_level');
            $table->index('total_xp');
        });

        // XP History table to track all XP transactions
        Schema::create('xp_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('xp_amount');
            $table->string('action_type'); // task_completed, referral, streak, milestone, etc.
            $table->string('description')->nullable();
            $table->foreignId('related_id')->nullable()->unsignedBigInteger('related_id')->index()->nullOnDelete();
            $table->timestamps();

            $table->index('user_id');
            $table->index('action_type');
        });

        // Level rewards table
        Schema::create('level_rewards', function (Blueprint $table) {
            $table->id();
            $table->integer('level');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('bonus_amount', 15, 2)->default(0);
            $table->string('badge_icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_rewards');
        Schema::dropIfExists('xp_history');
        Schema::dropIfExists('user_experience');
    }
};
