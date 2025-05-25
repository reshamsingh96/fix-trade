<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaborImage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'labour_images';
    protected $fillable = [
        'image_url',
        'labour_id',
        'image_public_id'
    ];
}
