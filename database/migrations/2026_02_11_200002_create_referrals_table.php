<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // who refers
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->onDelete('cascade'); // who was referred
            $table->string('referral_code')->unique();
            $table->string('referred_email')->nullable();
            $table->boolean('is_registered')->default(false);
            $table->boolean('is_activated')->default(false);
            $table->decimal('reward_earned', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('referrals');
    }
};
