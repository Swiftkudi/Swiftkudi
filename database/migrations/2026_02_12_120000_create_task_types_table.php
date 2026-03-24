<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('task_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Instagram Like", "TikTok Follow"
            $table->string('slug')->unique();
            $table->string('category'); // micro, ugc, referral, premium
            $table->string('platform'); // instagram, tiktok, twitter, youtube, facebook, etc.
            $table->string('task_action'); // like, comment, follow, share, subscribe, etc.
            $table->decimal('min_price', 10, 2); // Minimum price per action
            $table->decimal('max_price', 10, 2); // Maximum price per action
            $table->decimal('base_price', 10, 2)->default(0); // Base/default price
            $table->string('proof_type')->default('screenshot'); // screenshot, video, audio, link
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->string('icon', 100)->nullable(); // Emoji or icon class
            $table->integer('min_level_required')->default(1); // 1 for all, 2+ for premium
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add task_type_id foreign key to tasks table
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'task_type_id')) {
                $table->unsignedBigInteger('task_type_id')->nullable()->after('category_id');
                $table->foreign('task_type_id')->references('id')->on('task_types')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['task_type_id']);
            $table->dropColumn('task_type_id');
        });
        Schema::dropIfExists('task_types');
    }
};
