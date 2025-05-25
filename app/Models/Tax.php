<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'taxes';
    protected $fillable = [
        'country_id',
        'tax_name',
        'tax_percentage',
        'description'
    ];
}
