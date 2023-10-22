<?php

namespace App\View\Components;

use App\Models\Event;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EventTextTrainersMessage extends Component
{

    /**
     * Create a new component instance.
     */
    public function __construct(public Event $event)
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.event-text-trainers-message');
    }
}
