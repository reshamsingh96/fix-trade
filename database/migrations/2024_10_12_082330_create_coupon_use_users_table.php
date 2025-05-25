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
        Schema::create('coupon_use_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('coupon_id')->nullable()->comment('Foreign key to the applied coupon');
            $table->uuid('user_id')->nullable()->comment('User uuid for registered users');
            $table->integer('use_count')->default(0);

            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
            $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_use_users');
    }
};
