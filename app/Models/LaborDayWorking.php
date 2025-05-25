<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaborDayWorking extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'labour_day_workings';
    protected $fillable = [
        'labour_id',
        'day_number',
        'day_name',
        'start_time',
        'end_time',
        'break_minute',
        'working_hour',
        'per_hour_amount',
        'day_amount',
    ];
}
