<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;
use App\Enums\AthleteCategoryGroup;
use Artesaos\SEOTools\Facades\SEOMeta;

class EventController extends Controller
{
    /**
     * Show the page for a given event.
     */
    public function show(string $event): View
    {
        $event = Event::findOrFail($event);

        SEOMeta::setTitle($event->name);

        return view('events.show', [
            'event' => $event,
        ]);
    }

    /**
     * Show the page for all events.
     */
    public function index(): View
    {
        $acg = request()->input('acg');

        if ($acg) {
            $events = Event::whereJsonContains('athlete_category_groups', $acg)->get();
        } else {
            $events = Event::all();
        }

        SEOMeta::setTitle('Calendrier');

        return view('events.index', [
            'events' => $events,
        ]);
    }
}
