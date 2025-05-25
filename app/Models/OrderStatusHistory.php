<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'order_status_histories';
    protected $fillable = [
        'order_id',
        'user_id',
        'old_status',
        'status',
        'comment',
    ];
}
