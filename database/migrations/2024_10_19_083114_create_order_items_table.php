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
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id')->comment('Foreign key to orders table');
            $table->uuid('product_id')->comment('Foreign key to products table');
            $table->uuid('store_id')->comment('Foreign key to store table');
            $table->string('product_name')->comment('Snapshot of the product name at the time of order');
            $table->enum('order_type', ['Buyer', 'Seller','Rent'])->default('Buyer')->comment('Seller, Buyer, Rent');
            $table->float('quantity')->default(1)->comment('Quantity of the product ordered');
            $table->float('unit_price')->default(0)->comment('Price per unit at the time of order');
            $table->float('discount_amount')->default(0)->comment('Discount amount applied to this item');
            $table->integer('duration')->default(0)->comment('Rent Buy product only Hours amount');
            $table->enum('tax_type', ['Inclusive', 'Exclusive'])->default('Exclusive')->comment('1->Inclusive,0->Exclusive');
            $table->uuid('tax_id')->nullable()->comment('Foreign key to taxes table');
            $table->float('gst_rate')->default(0)->comment('GST ids to sum');
            $table->float('sale_price')->default(0)->comment('discount and gst in inclusive sale rate');
            $table->float('gst_amount')->default(0)->comment('GST applied to this item');
            $table->float('total_price')->default(0)->comment('Total price for the product (quantity * unit price)');
            $table->timestamps();
            
            // Foreign key relationships
            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('set null');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
