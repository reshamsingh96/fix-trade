<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Labour extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'labours';
    protected $fillable = [
        'user_id',
        'work_title',
        'labor_name',
        'phone',
        'status',
        'description',
        'image_url',
        'image_public_id',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'user_id');
    }

    public function working_day()
    {
        return $this->hasMany(LaborDayWorking::class, 'labour_id', 'id');
    }

    public function work_images()
    {
        return $this->hasMany(LaborImage::class, 'labour_id', 'id');
    }

    public function rating()
    {
        return $this->hasMany(Review::class, 'labour_id', 'id')->whereNotNull('labour_id')->select('id','labour_id','user_id','rating');
    }

    public function user_rating()
    {
        return $this->hasOne(Review::class, 'labour_id', 'id')->whereNotNull('labour_id')->select('id','labour_id','user_id','rating');
    }

    public function review()
    {
        return $this->hasMany(Review::class, 'labour_id', 'id')->whereNotNull('labour_id')->select('id','labour_id','user_id','review');
    }
}
