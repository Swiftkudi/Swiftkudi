<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_boosts')) {
            Schema::create('user_boosts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('package_id')->nullable();
                $table->string('target_type')->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['target_type', 'target_id']);
            });
        }

        // Add foreign key constraint if boost_packages table exists (created in later migration)
        if (Schema::hasTable('boost_packages') && Schema::hasTable('user_boosts')) {
            Schema::table('user_boosts', function (Blueprint $table) {
                try {
                    // Check if foreign key already exists before adding
                    $table->foreign('package_id')->references('id')->on('boost_packages')->onDelete('cascade');
                } catch (\Exception $e) {
                    // Foreign key may already exist or boost_packages doesn't have id column yet
                    // This is safe to ignore
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_boosts');
    }
};
