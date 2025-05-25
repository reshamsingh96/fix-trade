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
        Schema::create('stores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->comment('User uuid for registered users');
            $table->string('store_url')->nullable();
            $table->string('type')->nullable();
            $table->string('store_name')->nullable();
            $table->longText('full_address')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('store_phone')->nullable();
            $table->enum('status', ['Active', 'In-Active'])->default('Active')->comment('Active , In-Active');
            $table->longText('description')->nullable();
            $table->string('store_public_id')->nullable();
            $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
