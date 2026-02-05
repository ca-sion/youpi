<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventLogistic;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;

class LogisticsController extends Controller
{
    public function show($id)
    {
        $event = EventLogistic::findOrFail($id);
        $settings = $event->settings ?? [];
        
        $days = [];
        $startDateStr = $settings['start_date'] ?? null;
        $daysCount = (int)($settings['days_count'] ?? 2);

        if ($startDateStr) {
            $startDate = \Carbon\Carbon::parse($startDateStr);
            for ($i = 0; $i < $daysCount; $i++) {
                $date = $startDate->copy()->addDays($i);
                $days[] = [
                    'date' => $date->toDateString(),
                    'label' => $date->translatedFormat('D d M'),
                ];
            }
        }

        SEOMeta::setTitle($event->event_name.' - Résumé logistique');
        OpenGraph::setTitle($event->event_name.' - Résumé logistique');

        return view('logistics.summary', [
            'event' => $event,
            'settings' => $settings,
            'days' => $days,
            'transportPlan' => $event->transport_plan ?? [],
            'stayPlan' => $event->stay_plan ?? [],
            'participants' => collect($event->participants_data ?? [])->keyBy('id'),
        ]);
    }
}
