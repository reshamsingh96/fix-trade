<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'orders';
    protected $fillable = [
        'slug',
        'user_id',
        'guest_user_id',
        'status',
        'payment_type',
        'payment_status',
        'estimated_delivery_time',
        'customer_notes',
        'billing_address_id',
        'shipping_address_id',
        'coupon_discount_amount',
        'coupon_id',
        'coupon_code',
        'gst_amount',
        'total_amount',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'user_id');
    }

    public function guest_user()
    {
        return $this->hasOne(GuestUser::class, 'id', 'guest_user_id');
    }

    public function coupon()
    {
        return $this->hasOne(Coupon::class, 'id', 'coupon_id');
    }

    public function order_item()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
}
