<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'products';
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'store_id',
        'discount_type',
        'discount',
        'category_id',
        'sub_category_id',
        'type',
        'product_search',
        'tax_type',
        'tax_id',
        'status',
        'comment',
        'quantity',
        'unit_price',
        'unit_id',
        'duration',
        'discount_unit_price',
        'latitude',
        'longitude',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'user_id');
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
    public function sub_category()
    {
        return $this->hasOne(SubCategory::class, 'id', 'sub_category_id');
    }

    public function image()
    {
        return $this->hasOne(ProductImage::class, 'product_id', 'id');
    }

    public function product_images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    public function bookmark()
    {
        return $this->hasMany(ProductBookmark::class, 'product_id', 'id');
    }

    public function bookmark_info()
    {
        return $this->hasOne(ProductBookmark::class, 'product_id', 'id');
    }

    public function store()
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }

    public function rating()
    {
        return $this->hasMany(Review::class, 'product_id', 'id')->whereNotNull('product_id')->select('id','product_id','user_id','rating');
    }

    public function user_rating()
    {
        return $this->hasOne(Review::class, 'product_id', 'id')->whereNotNull('product_id')->select('id','product_id','user_id','rating');
    }

    public function review()
    {
        return $this->hasMany(Review::class, 'product_id', 'id')->whereNotNull('product_id')->select('id','product_id','user_id','review');
    }
    public function tax()
    {
        return $this->hasOne(Tax::class, 'id', 'tax_id');
    }
}
