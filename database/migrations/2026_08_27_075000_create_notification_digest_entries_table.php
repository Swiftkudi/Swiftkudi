<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_digest_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('frequency', 16)->default('daily')->index();
            $table->string('category', 32)->default('system')->index();
            $table->string('title');
            $table->text('message');
            $table->text('action_url')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamps();
            $table->index(['user_id', 'frequency', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_digest_entries');
    }
};
