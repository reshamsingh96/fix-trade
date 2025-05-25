<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'shipping_addresses';
    protected $fillable = [
        'order_id',
        'name',
        'address_line1',
        'address_line2',
        'address_line3',
        'country_id',
        'state_id',
        'city_id',
        'pin_code',
        'phone',
        'email',
    ];
}
