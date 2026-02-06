<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventLogistic extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'inscriptions_data' => 'array',
        'schedule_raw'      => 'array',
        'participants_data' => 'array',
        'transport_plan'    => 'array',
        'stay_plan'         => 'array',
        'settings'          => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
