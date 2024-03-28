<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use App\Mail\AdminEventReminder;
use Illuminate\Support\Facades\Mail;

class SendAdminEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-admin-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inTwoWeeks = now()->startOfDay()->addDays(14);

        $events = Event::where('starts_at', $inTwoWeeks)->get();

        foreach ($events as $event) {
            Mail::to('technique@casion.ch')->send(new AdminEventReminder($event));
        }

        $inOneWeek = now()->startOfDay()->addDays(7);

        $events = Event::where('deadline_at', $inOneWeek)->get();

        foreach ($events as $event) {
            Mail::to('technique@casion.ch')->send(new AdminEventReminder($event));
        }
    }
}
