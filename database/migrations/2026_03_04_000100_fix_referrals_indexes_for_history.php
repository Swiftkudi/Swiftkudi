<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            try {
                $table->dropUnique('referrals_referral_code_unique');
            } catch (\Throwable $e) {
                // Ignore if index does not exist in current environment
            }

            try {
                $table->index('referral_code', 'referrals_referral_code_index');
            } catch (\Throwable $e) {
                // Ignore if already exists
            }

            try {
                $table->unique(['user_id', 'referred_user_id'], 'referrals_user_referred_unique');
            } catch (\Throwable $e) {
                // Ignore if already exists
            }

            try {
                $table->index(['user_id', 'created_at'], 'referrals_user_created_index');
            } catch (\Throwable $e) {
                // Ignore if already exists
            }
        });
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            try {
                $table->dropIndex('referrals_user_created_index');
            } catch (\Throwable $e) {
                // Ignore if index does not exist
            }

            try {
                $table->dropUnique('referrals_user_referred_unique');
            } catch (\Throwable $e) {
                // Ignore if index does not exist
            }

            try {
                $table->dropIndex('referrals_referral_code_index');
            } catch (\Throwable $e) {
                // Ignore if index does not exist
            }

            try {
                $table->unique('referral_code', 'referrals_referral_code_unique');
            } catch (\Throwable $e) {
                // Ignore if index already exists
            }
        });
    }
};
