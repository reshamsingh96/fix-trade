<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory,HasUuids;
    protected $table = 'cart_items';
    protected $fillable = [
        'user_id',
        'guest_user_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_price',
    ];
}
