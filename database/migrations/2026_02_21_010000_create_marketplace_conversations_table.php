<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('marketplace_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // task, professional_service, growth_service, digital_product, job
            $table->unsignedBigInteger('reference_id');
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('seller_id');
            $table->enum('status', ['active', 'closed', 'resolved'])->default('active');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['type', 'reference_id']);
            $table->index(['buyer_id', 'seller_id']);
        });

        Schema::create('marketplace_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('message');
            $table->string('attachment_type')->nullable();
            $table->string('attachment_path')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('marketplace_conversations')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('marketplace_messages');
        Schema::dropIfExists('marketplace_conversations');
    }
};
