<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('task_categories')->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->string('location')->nullable();
            $table->string('job_type')->default('full_time');
            $table->string('experience_level')->nullable();
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('salary_currency')->default('NGN');
            $table->boolean('salary_visible')->default(true);
            $table->string('application_email')->nullable();
            $table->string('application_url')->nullable();
            $table->json('requirements')->nullable();
            $table->json('benefits')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['draft', 'active', 'paused', 'closed', 'expired'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->integer('views')->default(0);
            $table->integer('applications_count')->default(0);
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('cover_letter')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->json('answers')->nullable();
            $table->enum('status', ['pending', 'reviewing', 'shortlisted', 'rejected', 'hired'])->default('pending');
            $table->text('employer_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('job_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_bookmarks');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('jobs');
    }
};
