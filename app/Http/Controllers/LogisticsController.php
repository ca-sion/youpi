<?php

namespace App\Http\Controllers;

use App\Models\EventLogistic;
use Illuminate\Http\Request;

class LogisticsController extends Controller
{
    public function show($id)
    {
        $event = EventLogistic::findOrFail($id);
        
        return view('logistics.summary', [
            'event' => $event,
            'transportPlan' => $event->transport_plan ?? [],
            'stayPlan' => $event->stay_plan ?? [],
            'participants' => collect($event->participants_data ?? [])->keyBy('id'),
        ]);
    }
}
