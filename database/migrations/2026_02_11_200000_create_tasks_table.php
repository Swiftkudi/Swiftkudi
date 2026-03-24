<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('platform')->default('instagram');
            $table->string('task_type')->default('like');
            $table->string('target_url')->nullable();
            $table->string('target_account')->nullable();
            $table->string('hashtag')->nullable();
            $table->text('proof_instructions')->nullable();
            $table->string('proof_type')->default('screenshot');
            $table->decimal('budget', 15, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('worker_reward_per_task', 10, 2)->default(0);
            $table->decimal('platform_commission', 15, 2)->default(0);
            $table->decimal('escrow_amount', 15, 2)->default(0);
            $table->integer('total_slots')->default(1);
            $table->integer('completed_slots')->default(0);
            $table->integer('completed_count')->default(0);
            $table->integer('min_followers')->default(0);
            $table->integer('min_account_age_days')->default(0);
            $table->integer('max_submissions_per_user')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['platform', 'task_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
