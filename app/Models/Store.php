<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'stores';
    protected $fillable = [
        'user_id',
        'store_url',
        'type',
        'store_name',
        'store_phone',
        'full_address',
        'store_public_id',
        'gst_number',
        'status',
        'description'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'user_id');
    }
}
