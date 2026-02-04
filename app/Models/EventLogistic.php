<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLogistic extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'inscriptions_data' => 'array',
        'raw_schedule' => 'array',
        'participants_data' => 'array',
        'transport_plan' => 'array',
        'stay_plan' => 'array',
        'settings' => 'array',
    ];
}
