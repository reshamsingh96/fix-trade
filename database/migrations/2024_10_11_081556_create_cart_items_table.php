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
            Schema::create('cart_items', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('user_id')->nullable()->comment('User uuid for registered users');
                $table->uuid('guest_user_id')->nullable()->comment('Guest user ID for guest users');
                $table->uuid('product_id')->comment('Foreign key to products table');
                $table->string('product_name')->comment('Snapshot of the product name at the time it was added to the cart');
                $table->float('quantity')->default(1)->comment('Quantity of the product in the cart');
                $table->float('unit_price')->default(0)->comment('Price per unit when the item was added to the cart');
                $table->float('total_price')->default(0)->comment('Total price for the product in the cart (quantity * unit price)');
                $table->uuid('coupon_id')->nullable();
                $table->timestamps();
            
                // Foreign key relationships
                $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
                $table->foreign('guest_user_id')->references('id')->on('guest_users')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
