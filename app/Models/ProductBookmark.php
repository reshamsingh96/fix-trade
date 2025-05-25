<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBookmark extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'product_bookmarks';
    protected $fillable = [
        'is_bookmark',
        'user_id',
        'product_id','guest_user_id'
    ];
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id')->with('image');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'user_id');
    }
}
