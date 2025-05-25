<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'reviews';
    protected $fillable = [
        'user_id',
        'rating',
        'review',
        'guest_user_id',
        'product_id',
        'labour_id',
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id')->with('image');
    }

    public function labour()
    {
        return $this->hasOne(Labour::class, 'id', 'labour_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'user_id');
    }
}
