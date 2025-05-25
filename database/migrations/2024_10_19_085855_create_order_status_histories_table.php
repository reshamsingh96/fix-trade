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
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id')->comment('Foreign key to orders table');
            $table->uuid('user_id')->nullable()->comment('User uuid for registered users');
            $table->enum('old_status', ['Pending', 'Progress','Delivered','Completed','Cancel'])->nullable()->comment('Pending, Progress, Delivered, Completed, Cancel');
            $table->enum('status', ['Pending', 'Progress','Delivered','Completed','Cancel'])->default('Pending')->comment('Pending, Progress, Delivered, Completed, Cancel');
            $table->string('comment')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
