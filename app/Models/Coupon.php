<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'coupons';
    protected $fillable = [
        'product_ids',
        'user_ids',
        'code',
        'discount_type',
        'discount_value',
        'minimum_order_amount',
        'per_user_limit',
        'valid_from',
        'valid_until',
        'is_active',
        'created_by',
        'created_by'
    ];
}
