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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('store_id');
            $table->string('name');
            $table->string('slug');
            $table->longText('description')->nullable();
            $table->enum('discount_type', ['Fixed', 'Percentage'])->default('Fixed')->comment('Fixed , Percentage');
            $table->integer('discount')->default(0)->comment('discount_type : Percentage (100% maximum) ,Fixed set amount');
            $table->uuid('category_id');
            $table->uuid('sub_category_id');
            $table->enum('type', ['Buyer', 'Seller','Rent'])->default('Seller')->comment('Seller, Buyer,Rent');
            $table->enum('tax_type', ['Inclusive', 'Exclusive'])->default('Exclusive')->comment('1->Inclusive,0->Exclusive');
            $table->uuid('tax_id')->nullable()->comment('Foreign key to taxes table');
            $table->enum('status', ['Active', 'In-Active'])->default('Active')->comment('Active , In-Active');
            $table->integer('quantity')->default(1);
            $table->float('unit_price')->default(0);
            $table->uuid('unit_id')->nullable();
            $table->integer('duration')->default(0)->comment('Rent Buy product only Hours amount');
            $table->float('discount_unit_price')->default(0);
            $table->longText('comment')->nullable();
            $table->decimal('latitude', 10, 7)->nullable(); 
            $table->decimal('longitude', 10, 7)->nullable();
            $table->longText('product_search')->nullable();
            # foreign
           // $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('set null');
            $table->foreign('user_id')->references('uuid')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('sub_category_id')->references('id')->on('sub_categories')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
