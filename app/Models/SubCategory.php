<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'sub_categories';
    protected $fillable = [
        'category_id',
        'name',
        'sub_category_type',
        'sub_category_url',
        'description'
    ];

    public function product()
    {
        return $this->hasMany(Product::class, 'sub_category_id', 'id');
    }

    public function category()
    {
        return $this->hasMany(Category::class, 'id', 'category_id');
    }
}
