<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Currencies table
        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('code', 10)->unique();
                $table->string('name', 50);
                $table->string('symbol', 10);
                $table->decimal('rate_to_ngn', 15, 8);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        
        // Task Categories table
        if (!Schema::hasTable('task_categories')) {
            Schema::create('task_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 100)->unique();
                $table->text('description')->nullable();
                $table->string('icon', 50)->nullable();
                $table->decimal('base_price', 10, 2)->default(0);
                $table->decimal('platform_margin', 5, 2)->default(25);
                $table->string('task_type', 50)->default('micro');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        
        // Task Bundles table
        if (!Schema::hasTable('task_bundles')) {
            Schema::create('task_bundles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name', 200);
                $table->text('description')->nullable();
                $table->decimal('total_price', 15, 2);
                $table->decimal('worker_reward', 15, 2);
                $table->decimal('platform_commission', 15, 2);
                $table->integer('total_tasks');
                $table->json('task_ids');
                $table->json('category_ids');
                $table->string('difficulty_level', 20)->default('easy');
                $table->boolean('is_active')->default(true);
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
        
        // Badges table
        if (!Schema::hasTable('badges')) {
            Schema::create('badges', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 100)->unique();
                $table->text('description')->nullable();
                $table->string('icon', 100)->nullable();
                $table->integer('xp_reward')->default(0);
                $table->json('requirements')->nullable();
                $table->timestamps();
            });
        }
        
        // User Badges table
        if (!Schema::hasTable('user_badges')) {
            Schema::create('user_badges', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('badge_id');
                $table->timestamp('earned_at')->nullable();
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('badge_id')->references('id')->on('badges')->onDelete('cascade');
                
                $table->unique(['user_id', 'badge_id']);
            });
        }
        
        // Fraud Logs table
        if (!Schema::hasTable('fraud_logs')) {
            Schema::create('fraud_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('type', 50);
                $table->string('severity', 20)->default('low');
                $table->text('description');
                $table->json('data')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->boolean('is_resolved')->default(false);
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                
                $table->index(['user_id', 'type']);
                $table->index(['type', 'severity']);
            });
        }
        
        // Withdrawals table
        if (!Schema::hasTable('withdrawals')) {
            Schema::create('withdrawals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('wallet_id');
                $table->decimal('amount', 15, 2);
                $table->decimal('fee', 15, 2)->default(0);
                $table->decimal('net_amount', 15, 2);
                $table->string('currency', 10)->default('NGN');
                $table->string('method', 50);
                $table->string('status', 20)->default('pending');
                $table->string('bank_name')->nullable();
                $table->string('account_number', 50)->nullable();
                $table->string('account_name', 100)->nullable();
                $table->string('usdt_address', 200)->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
                
                $table->index(['user_id', 'status']);
                $table->index(['status', 'created_at']);
            });
        }
        
        // Wallet Ledger table
        if (!Schema::hasTable('wallet_ledgers')) {
            Schema::create('wallet_ledgers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('wallet_id');
                $table->unsignedBigInteger('user_id');
                $table->string('type', 50);
                $table->decimal('amount', 15, 2);
                $table->decimal('withdrawable_before', 15, 2)->default(0);
                $table->decimal('withdrawable_after', 15, 2)->default(0);
                $table->decimal('promo_credit_before', 15, 2)->default(0);
                $table->decimal('promo_credit_after', 15, 2)->default(0);
                $table->string('currency', 10)->default('NGN');
                $table->string('status', 20)->default('completed');
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('description')->nullable();
                $table->text('metadata')->nullable();
                $table->timestamps();
                
                $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                
                $table->index(['wallet_id', 'type']);
                $table->index(['user_id', 'created_at']);
            });
        }
        
        // Add gamification fields to users table
        if (!Schema::hasColumn('users', 'level')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_admin')->default(false);
                $table->integer('level')->default(1);
                $table->integer('experience_points')->default(0);
                $table->integer('daily_streak')->default(0);
                $table->timestamp('last_activity_at')->nullable();
                $table->string('referral_code', 50)->unique()->nullable();
                $table->unsignedBigInteger('referred_by')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                
                $table->foreign('referred_by')->references('id')->on('users')->onDelete('set null');
            });
        }
        
        // Add fields to wallets table
        if (!Schema::hasColumn('wallets', 'withdrawable_balance')) {
            Schema::table('wallets', function (Blueprint $table) {
                $table->decimal('withdrawable_balance', 15, 2)->default(0);
                $table->decimal('promo_credit_balance', 15, 2)->default(0);
                $table->decimal('escrow_balance', 15, 2)->default(0);
            });
        }
        
        // Add fields to transactions table
        if (!Schema::hasColumn('transactions', 'currency')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('currency', 10)->default('NGN');
                $table->decimal('exchange_rate', 15, 8)->default(1);
                $table->string('payment_method', 50)->nullable();
            });
        }
        
        // Add fields to tasks table
        if (!Schema::hasColumn('tasks', 'category_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('proof_type', 50)->default('screenshot');
                $table->decimal('escrow_amount', 15, 2)->default(0);
                $table->decimal('worker_reward_per_task', 10, 2)->default(0);
                
                $table->foreign('category_id')->references('id')->on('task_categories')->onDelete('set null');
            });
        }
        
        // Add fields to task_completions table
        if (!Schema::hasColumn('task_completions', 'promo_credit_earned')) {
            Schema::table('task_completions', function (Blueprint $table) {
                $table->decimal('promo_credit_earned', 10, 2)->default(0);
                $table->text('admin_notes')->nullable();
                $table->timestamp('reviewed_at')->nullable();
            });
        }
        
        // Add fields to referrals table
        if (!Schema::hasColumn('referrals', 'activation_fee_paid')) {
            Schema::table('referrals', function (Blueprint $table) {
                $table->decimal('activation_fee_paid', 15, 2)->default(0);
                $table->decimal('referrer_bonus', 15, 2)->default(0);
                $table->decimal('pool_contribution', 15, 2)->default(0);
            });
        }
    }
    
    public function down()
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn(['activation_fee_paid', 'referrer_bonus', 'pool_contribution']);
        });
        
        Schema::table('task_completions', function (Blueprint $table) {
            $table->dropColumn(['promo_credit_earned', 'admin_notes', 'reviewed_at']);
        });
        
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'proof_type', 'escrow_amount', 'worker_reward_per_task']);
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['exchange_rate', 'payment_method']);
        });
        
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['withdrawable_balance', 'promo_credit_balance', 'escrow_balance']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn(['level', 'experience_points', 'daily_streak', 'last_activity_at', 'referral_code', 'referred_by', 'trial_ends_at']);
        });
        
        Schema::dropIfExists('wallet_ledgers');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('fraud_logs');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('task_bundles');
        Schema::dropIfExists('task_categories');
        Schema::dropIfExists('currencies');
    }
};
