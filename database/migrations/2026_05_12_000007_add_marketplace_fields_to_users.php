<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'university_id')) {
                $table->unsignedBigInteger('university_id')->nullable()->after('id');
                $table->foreign('university_id')->references('id')->on('universities')->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'campus')) {
                $table->string('campus')->nullable()->after('university_id');
            }
            if (!Schema::hasColumn('users', 'faculty')) {
                $table->string('faculty')->nullable()->after('campus');
            }
            if (!Schema::hasColumn('users', 'year_of_study')) {
                $table->string('year_of_study', 10)->nullable()->after('faculty');
            }
            if (!Schema::hasColumn('users', 'marketplace_avatar')) {
                $table->string('marketplace_avatar', 512)->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'seller_rating')) {
                $table->decimal('seller_rating', 3, 2)->default(0)->after('marketplace_avatar');
            }
            if (!Schema::hasColumn('users', 'seller_rating_count')) {
                $table->integer('seller_rating_count')->default(0)->after('seller_rating');
            }
            if (!Schema::hasColumn('users', 'marketplace_seller_verified')) {
                $table->boolean('marketplace_seller_verified')->default(false)->after('seller_rating_count');
            }
            if (!Schema::hasColumn('users', 'marketplace_bio')) {
                $table->text('marketplace_bio')->nullable()->after('marketplace_seller_verified');
            }
            if (!Schema::hasColumn('users', 'marketplace_contact_preferences')) {
                $table->json('marketplace_contact_preferences')->nullable()->after('marketplace_bio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['university_id']);
            $table->dropColumn([
                'university_id', 'campus', 'faculty', 'year_of_study',
                'marketplace_avatar', 'seller_rating', 'seller_rating_count',
                'marketplace_seller_verified', 'marketplace_bio',
                'marketplace_contact_preferences',
            ]);
        });
    }
};