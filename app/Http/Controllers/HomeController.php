<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Artesaos\SEOTools\Facades\SEOMeta;

class HomeController extends Controller
{
    /**
     * Show the welcome page.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function welcome()
    {
        $today = now()->isoFormat('Y-MM-DD');
        $week_start = now()->startOfWeek()->isoFormat('Y-MM-DD');
        $week_end = now()->endOfWeek()->isoFormat('Y-MM-DD');

        $today_resources = Resource::where('date', $today)
            ->with('athleteGroup')
            ->get();
        $all_week_resources = Resource::where('date', '>=', $week_start)
            ->where('date', '<=', $week_end)
            ->where('type', '=', 'session')
            ->with('athleteGroup')
            ->orderBy('date', 'asc')
            ->get();
        $week_resources = Resource::where('date', '>=', $week_start)
            ->where('date', '<=', $week_end)
            ->whereIn('type', ['week_plan'])
            ->with('athleteGroup')
            ->orderBy('date', 'asc')
            ->get();
        $sessions_exercises = Resource::whereIn('type', ['sessions', 'exercises'])
            ->with('athleteGroup')
            ->get();
        $year_plans = Resource::whereIn('type', ['year_plan', 'macro_plan'])
            ->where('date', '<=', $today)
            ->with('athleteGroup')
            ->orderBy('date', 'asc')
            ->get();

        $allForModal = $today_resources
        ->merge($all_week_resources)
        ->merge($week_resources)
        ->merge($sessions_exercises)
        ->merge($year_plans)
        ->filter(function ($resource) {
            return $resource->text && $resource->isAccessible;
        });

        return view('welcome', compact('today_resources', 'week_resources', 'all_week_resources', 'sessions_exercises', 'year_plans', 'allForModal'));
    }

    /**
     * Show the program page.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function program()
    {
        $today = now()->isoFormat('Y-MM-DD');
        $week_start = now()->startOfWeek()->isoFormat('Y-MM-DD');
        $week_end = now()->endOfWeek()->isoFormat('Y-MM-DD');

        $today_resources = Resource::where('date', $today)
            ->with('athleteGroup')
            ->get();
        $all_week_resources = Resource::where('date', '>=', $week_start)
            ->where('date', '<=', $week_end)
            ->where('type', '=', 'session')
            ->with('athleteGroup')
            ->orderBy('date', 'asc')
            ->get();
        $week_resources = Resource::where('date', '>=', $week_start)
            ->where('date', '<=', $week_end)
            ->whereIn('type', ['week_plan'])
            ->with('athleteGroup')
            ->orderBy('date', 'asc')
            ->get();
        $sessions_exercises = Resource::whereIn('type', ['sessions', 'exercises'])
            ->with('athleteGroup')
            ->get();
        $year_plans = Resource::whereIn('type', ['year_plan', 'macro_plan'])
            ->where('date', '<=', $today)
            ->with('athleteGroup')
            ->orderBy('date', 'asc')
            ->get();

        $allForModal = $today_resources
        ->merge($all_week_resources)
        ->merge($week_resources)
        ->merge($sessions_exercises)
        ->merge($year_plans)
        ->filter(function ($resource) {
            return $resource->text && $resource->isAccessible;
        });

        SEOMeta::setTitle('Programme');

        return view('program', compact('today_resources', 'week_resources', 'all_week_resources', 'sessions_exercises', 'year_plans', 'allForModal'));
    }
}
