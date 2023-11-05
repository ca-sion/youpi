<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Trainer;
use Illuminate\View\View;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;

class TrainersController extends Controller
{
    /**
     * Show the welcome page.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function presences(): View
    {
        $events = Event::whereDate('starts_at', '>', now()->subDays(1)->startOfDay())
            ->with('trainersPresences')
            ->orderBy('starts_at')
            ->get();

        $trainers = Trainer::orderBy('name')->get();

        SEOMeta::setTitle('Présences des entraîneurs');
        OpenGraph::setTitle('Présences des entraîneurs');

        return view('trainers.presences', [
            'events' => $events,
            'trainers' => $trainers,
        ]);
    }
}
