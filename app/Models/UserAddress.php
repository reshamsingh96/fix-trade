<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'user_address';
    protected $fillable = [
        'user_id',
        'country_id',
        'state_id',
        'city_id',
        'pin_code',
        'home_no',
        'full_address',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'user_id');
    }
    
    public function country()
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

    public function state()
    {
        return $this->hasOne(State::class, 'id', 'state_id');
    }

    public function city()
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }
}
