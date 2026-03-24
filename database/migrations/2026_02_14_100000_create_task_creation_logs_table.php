<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This table tracks task creation requests for idempotency - prevents duplicate submissions.
     */
    public function up(): void
    {
        Schema::create('task_creation_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique()->comment('Unique idempotency token for each creation attempt');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('pending')->comment('pending, processing, completed, failed, duplicate');
            $table->json('request_payload')->comment('Stores sanitized request data');
            $table->json('response_data')->nullable()->comment('Stores response data');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_creation_logs');
    }
};
