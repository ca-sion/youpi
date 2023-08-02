<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;

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

        $today_resources = Resource::where('date', $today)->with('athleteGroup')->get();
        $all_week_resources = Resource::where('date', '>=', $week_start)
        ->where('date', '<=', $week_end)
        ->where('type', '=', 'session')
        ->with('athleteGroup')
        ->get();
        $week_resources = Resource::where('date', '>=', $week_start)
            ->where('date', '<=', $week_end)
            ->where('type', '<>', 'session')
            ->with('athleteGroup')
            ->get();

        return view('welcome', compact('today_resources', 'week_resources', 'all_week_resources'));
    }
}
