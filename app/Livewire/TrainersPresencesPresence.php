<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Trainer;
use Livewire\Component;
use App\Models\TrainerPresence;
use Filament\Notifications\Notification;

class TrainersPresencesPresence extends Component
{
    public $trainer;
    public $event;
    public $trainerPresence;
    public $trainerPresenceValue;

    public function mount(Trainer $trainer, Event $event)
    {
        $this->trainer = $trainer;
        $this->event = $event;

        $this->trainerPresence = TrainerPresence::where('event_id', $event->id)->where('trainer_id', $trainer->id)->first();
        $this->trainerPresenceValue = $this->trainerPresence ? $this->trainerPresence->presence : null;
    }

    public function updatePresence($value)
    {
        $this->trainerPresence = TrainerPresence::where('event_id', $this->event->id)->where('trainer_id', $this->trainer->id)->first();
        if (! $this->trainerPresence) {
            $this->trainerPresence = TrainerPresence::create([
                'event_id' => $this->event->id,
                'trainer_id' => $this->trainer->id,
            ]);
        }

        $this->trainerPresence->presence = $value;
        $this->trainerPresence->save();

        Notification::make()
            ->title('Enregistré')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.trainers-presences-presence');
    }
}
