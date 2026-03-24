<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // New Tasks Table
        Schema::create('tasks_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category'); // micro, ugc, growth, premium
            $table->string('title');
            $table->text('description');
            $table->text('proof_instructions')->nullable();
            $table->decimal('budget_total', 15, 2);
            $table->decimal('reward_per_user', 15, 2);
            $table->integer('max_workers');
            $table->integer('workers_completed_count')->default(0);
            $table->integer('workers_accepted_count')->default(0);
            
            // Status: draft, pending_funding, active, paused, completed, cancelled, expired
            $table->enum('status', [
                'draft', 
                'pending_funding', 
                'active', 
                'paused', 
                'completed', 
                'cancelled',
                'expired'
            ])->default('draft');
            
            $table->decimal('escrow_balance', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->decimal('commission_earned', 15, 2)->default(0);
            
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('status');
            $table->index('user_id');
            $table->index('category');
            $table->index('created_at');
        });

        // New Task Submissions Table
        Schema::create('task_submissions_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks_new')->onDelete('cascade');
            $table->foreignId('worker_id')->constrained('users')->onDelete('cascade');
            $table->json('proof_data')->nullable();
            $table->text('notes')->nullable();
            
            // Status: pending, approved, rejected
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('task_id');
            $table->index('worker_id');
            $table->index('status');
            
            // Prevent duplicate submissions - one per worker per task
            $table->unique(['task_id', 'worker_id']);
        });

        // Task Wallet Transactions Table
        Schema::create('task_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks_new')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // fund, payout, refund, commission
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('task_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
        });

        // Add system settings for task system
        if (Schema::hasTable('system_settings')) {
            $settings = [
                ['key' => 'task_auto_approve_days', 'value' => '3', 'group' => 'tasks', 'type' => 'number', 'description' => 'Days before auto-approving submissions'],
                ['key' => 'task_min_reward', 'value' => '10', 'group' => 'tasks', 'type' => 'number', 'description' => 'Minimum reward per user'],
                ['key' => 'task_max_workers', 'value' => '10000', 'group' => 'tasks', 'type' => 'number', 'description' => 'Maximum workers per task'],
                ['key' => 'task_commission_rate', 'value' => '25', 'group' => 'tasks', 'type' => 'number', 'description' => 'Platform commission percentage'],
            ];

            foreach ($settings as $setting) {
                if (!\App\Models\SystemSetting::where('key', $setting['key'])->exists()) {
                    \App\Models\SystemSetting::create($setting);
                }
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('task_wallet_transactions');
        Schema::dropIfExists('task_submissions_new');
        Schema::dropIfExists('tasks_new');
    }
};
