<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'order_items';
    protected $fillable = [
     'product_id',
     'order_id',
     'store_id',
     'product_name',
     'order_type',
     'quantity',
     'unit_price',
     'discount_amount',
     'duration',
     'tax_type',
     'tax_id',
     'gst_rate',
     'sale_price',
     'gst_amount',
     'total_price',
    ];

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
    
    public function tax()
    {
        return $this->hasOne(Tax::class, 'id', 'tax_id');
    }
    public function store()
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }
}
