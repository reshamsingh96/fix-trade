<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */ 
    public function up(): void
    {
        Schema::create('product_bookmarks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('is_bookmark')->default(1);
            $table->uuid('user_id');
            $table->uuid('product_id');
            $table->foreign('user_id')->references('uuid')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade')->onDelete('cascade');
            $table->uuid('guest_user_id')->nullable();
            $table->foreign('guest_user_id')->references('id')->on('guest_users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_bookmarks');
    }
};
