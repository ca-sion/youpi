<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;

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

        return view('welcome');
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
        $period_plans = Resource::whereIn('type', ['meso_plan', 'micro_plan'])
            ->where('date', '<=', $today)
            ->where('date_end', '>=', $today)
            ->with('athleteGroup')
            ->orderBy('date', 'asc')
            ->get();
        $sessions_exercises = Resource::whereIn('type', ['sessions', 'exercises'])
            ->with('athleteGroup')
            ->get();
        $year_plans = Resource::whereIn('type', ['year_plan', 'macro_plan'])
            ->where('date', '<=', $today)
            ->where('date_end', '>=', $today)
            ->with('athleteGroup')
            ->orderBy('date', 'asc')
            ->get();

        $week_resources = $week_resources->merge($period_plans);

        $allForModal = $today_resources
            ->merge($all_week_resources)
            ->merge($week_resources)
            ->merge($sessions_exercises)
            ->merge($period_plans)
            ->merge($year_plans)
            ->filter(function ($resource) {
                return $resource->text && $resource->isAccessible;
            });

        SEOMeta::setTitle('Programme');
        OpenGraph::setTitle('Programme');

        return view('program', compact('today_resources', 'week_resources', 'all_week_resources', 'sessions_exercises', 'period_plans', 'year_plans', 'allForModal'));
    }
}
