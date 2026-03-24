<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type', 50);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('NGN');
            $table->decimal('exchange_rate', 15, 8)->default(1);
            $table->string('payment_method', 50)->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('reference', 100)->nullable();
            $table->text('description')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
            
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['wallet_id', 'type']);
            $table->index(['user_id', 'created_at']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
