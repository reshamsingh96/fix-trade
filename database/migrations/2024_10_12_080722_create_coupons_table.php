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
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('product_ids')->nullable()->comment('save is this in products id [1,2]');
            $table->json('user_ids')->nullable()->comment('save is this in users id [1,2]');
            $table->string('code')->unique()->comment('Unique coupon code');
            $table->enum('discount_type', ['Percentage', 'fixed'])->comment('Type of discount: Percentage or fixed amount');
            $table->float('discount_value')->comment('The value of the discount');
            $table->float('minimum_order_amount')->nullable()->comment('Minimum order amount to apply the coupon');
            $table->integer('per_user_limit')->default(1)->comment('Maximum times the coupon can be used');
            $table->dateTime('valid_from')->nullable()->comment('Coupon validity start date');
            $table->dateTime('valid_until')->nullable()->comment('Coupon validity end date');
            $table->boolean('is_active')->default(true)->comment('Is the coupon active');
            $table->uuid('created_by')->nullable()->comment('User ID for registered users');
            // foreign
            $table->foreign('created_by')->references('uuid')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
