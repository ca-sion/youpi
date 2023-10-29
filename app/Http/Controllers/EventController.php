<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;
use App\Enums\AthleteCategoryGroup;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;

class EventController extends Controller
{
    /**
     * Show the page for a given event.
     */
    public function show(string $event): View
    {
        $event = Event::findOrFail($event);

        SEOMeta::setTitle($event->name);
        OpenGraph::setTitle($event->name);

        return view('events.show', [
            'event' => $event,
        ]);
    }
    /**
     * Show the page for a given event.
     */
    public function text(string $event): View
    {
        $event = Event::findOrFail($event);

        SEOMeta::setTitle($event->name);
        OpenGraph::setTitle($event->name);

        return view('events.text', [
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
            $events = Event::whereJsonContains('athlete_category_groups', $acg)
            ->whereDate('starts_at', '>', now()->subDays(10)->startOfDay())
            ->orderBy('starts_at')
            ->get();
        } else {
            $events = Event::whereDate('starts_at', '>', now()->subDays(10)->startOfDay())
            ->orderBy('starts_at')
            ->get();
        }

        SEOMeta::setTitle('Calendrier');

        return view('events.index', [
            'events' => $events,
        ]);
    }
}
