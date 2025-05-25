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
        Schema::create('billing_addresses', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('order_id')->comment('Related order ID');
                $table->string('name')->comment('Billing Name');
                $table->string('address_line1')->nullable()->comment('Billing Address Line 1');
                $table->string('address_line2')->nullable()->comment('Billing Address Line 2');
                $table->string('address_line3')->nullable()->comment('Billing Address Line 3');
                $table->uuid('country_id')->comment('Billing Country');
                $table->uuid('state_id')->comment('Billing State');
                $table->uuid('city_id')->comment('Billing City');
                $table->string('pin_code')->comment('Billing Pin code');
                $table->string('phone')->nullable()->comment('Billing Phone');
                $table->string('email')->nullable()->comment('Billing Email');
                $table->timestamps();
                # Foreign Key Constraint
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
        Schema::dropIfExists('billing_addresses');
    }
};
