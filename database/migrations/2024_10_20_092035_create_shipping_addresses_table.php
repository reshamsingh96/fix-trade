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
            Schema::create('shipping_addresses', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('order_id')->comment('Related order ID');
                $table->string('name')->comment('Shipping Name');
                $table->string('address_line1')->comment('Shipping Address Line 1');
                $table->string('address_line2')->nullable()->comment('Shipping Address Line 2');
                $table->string('address_line3')->nullable()->comment('Shipping Address Line 3');
                $table->uuid('country_id')->comment('Shipping Country');
                $table->uuid('state_id')->comment('Shipping State');
                $table->uuid('city_id')->comment('Shipping City');
                $table->string('pin_code')->comment('Shipping Pin code');
                $table->string('phone')->nullable()->comment('Shipping Phone');
                $table->string('email')->nullable()->comment('Shipping Email');
                $table->timestamps();
                // Foreign Key Constraint
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
                $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
                $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};
