<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('marketplace_categories')) {
            Schema::create('marketplace_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('type'); // task, professional, growth, digital_product, job
                $table->string('icon')->nullable();
                $table->string('color')->default('#6366f1');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('order')->default(0);
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->timestamps();

                $table->foreign('parent_id')->references('id')->on('marketplace_categories')->onDelete('set null');
                $table->index(['type', 'is_active']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('marketplace_categories');
    }
};
