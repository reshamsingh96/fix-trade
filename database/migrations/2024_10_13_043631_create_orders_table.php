<?php

use App\Constants\StatusConst;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug');
            $table->uuid('user_id')->nullable()->comment('User uuid for registered users');
            $table->uuid('guest_user_id')->nullable()->comment('Guest user ID for guest users');
            $table->enum('status', ['Pending', 'Progress','Delivered','Completed','Cancel,CustomerRate,SellerRate'])->default('Pending')->comment('Pending, Progress, Delivered, Completed, Cancel');
            $table->enum('payment_type', StatusConst::PAYMENT_TYPE_LIST)->default('Cash On Delivery')->comment(StatusConst::PAYMENT_TYPE_COMMENT);
            $table->enum('payment_status', ['Pending', 'Paid', 'Failed'])->default('Pending')->comment('Status of the payment');
            $table->dateTime('estimated_delivery_time')->nullable()->comment('Estimated delivery time for pending orders');
            $table->uuid('coupon_id')->nullable()->comment('Foreign key to the applied coupon');
            $table->string('coupon_code')->nullable()->comment('Applied coupon code');
            $table->float('coupon_discount_amount')->default(0)->comment('Discount amount from the applied coupon');
            $table->float('gst_amount')->default(0);
            $table->float('total_amount')->default(0);
            $table->uuid('shipping_address_id')->nullable()->comment('Shipping address for the order');
            $table->uuid('billing_address_id')->nullable()->comment('Shipping address for the order');
            $table->text('customer_notes')->nullable()->comment('Additional notes provided by the customer');
            // foreign
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
            $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
            $table->foreign('guest_user_id')->references('id')->on('guest_users')->onDelete('cascade');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
