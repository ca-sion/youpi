<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Trainer;
use Livewire\Component;
use App\Models\TrainerPresence;
use Filament\Notifications\Notification;

class TrainersPresencesNote extends Component
{
    public $trainer;
    public $event;
    public $trainerPresence;
    public $note;

    public function mount(Trainer $trainer, Event $event)
    {
        $this->trainer = $trainer;
        $this->event = $event;

        $this->trainerPresence = TrainerPresence::where('event_id', $event->id)->where('trainer_id', $trainer->id)->first();
        $this->note = $this->trainerPresence ? $this->trainerPresence->note : null;
    }

    public function updateNote()
    {
        $this->trainerPresence = TrainerPresence::where('event_id', $this->event->id)->where('trainer_id', $this->trainer->id)->first();
        if (! $this->trainerPresence) {
            $this->trainerPresence = TrainerPresence::create([
                'event_id' => $this->event->id,
                'trainer_id' => $this->trainer->id,
            ]);
        }

        $this->trainerPresence->note = $this->note;
        $this->trainerPresence->save();

        Notification::make()
            ->title('Note enregistré')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.trainers-presences-note');
    }
}
