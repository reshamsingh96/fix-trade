<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUseUser extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'coupon_use_users';
    protected $fillable = [
        'coupon_id',
        'user_id',
        'use_count'
    ];
}
