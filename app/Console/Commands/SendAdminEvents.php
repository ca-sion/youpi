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
        $inTenDays = now()->startOfDay()->addDays(10);

        $events = Event::where('starts_at', $inTenDays)->get();

        foreach ($events as $event) {
            Mail::to('technique@casion.ch')->send(new AdminEventReminder($event));
        }
    }
}
