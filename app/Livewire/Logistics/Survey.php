<?php

namespace App\Livewire\Logistics;

use App\Models\EventLogistic;
use Livewire\Component;

class Survey extends Component
{
    public EventLogistic $event_logistic;
    public $participantId = '';
    
    // Form data
    public $presence_aller = [];
    public $presence_retour = [];
    public $transport_mode;
    public $voiture_seats;
    public $hotel_needed = false;
    public $remarks;

    public function mount(EventLogistic $event_logistic)
    {
        $this->event_logistic = $event_logistic;
    }
    
    public function updatedParticipantId($value)
    {
        $this->reset(['presence_aller', 'presence_retour', 'transport_mode', 'voiture_seats', 'hotel_needed', 'remarks']);

        $participants = $this->event_logistic->participants_data ?? [];
        $p = collect($participants)->firstWhere('id', (string)$value);
        
        if ($p && isset($p['survey_response'])) {
            $r = $p['survey_response'];
            // Normalize data
            $this->presence_aller = (array)($r['presence_aller'] ?? []);
            $this->presence_retour = (array)($r['presence_retour'] ?? []);
            $this->transport_mode = $r['transport_mode'] ?? null;
            $this->voiture_seats = $r['voiture_seats'] ?? null;
            $this->hotel_needed = $r['hotel_needed'] ?? false;
            $this->remarks = $r['remarks'] ?? null;
        }
    }

    public function submit()
    {
        $this->validate([
            'participantId' => 'required',
            'transport_mode' => 'required',
            'voiture_seats' => 'nullable|integer|min:0',
        ]);
        
        $participants = $this->event_logistic->participants_data ?? [];
        $updated = false;
        
        foreach ($participants as &$p) {
            if (isset($p['id']) && (string)$p['id'] === (string)$this->participantId) {
                $p['survey_response'] = [
                    'presence_aller' => $this->presence_aller,
                    'presence_retour' => $this->presence_retour,
                    'transport_mode' => $this->transport_mode,
                    'voiture_seats' => $this->voiture_seats,
                    'hotel_needed' => $this->hotel_needed,
                    'remarks' => $this->remarks,
                    'filled_at' => now()->toDateTimeString(),
                ];
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            $this->event_logistic->update(['participants_data' => $participants]);
            session()->flash('message', 'Merci ! Votre réponse a été enregistrée.');
        } else {
            session()->flash('error', 'Participant non trouvé.');
        }
    }

    public function render()
    {
        // Get sorted participants for the select
        $participants = collect($this->event_logistic->participants_data ?? [])
            ->sortBy('name')
            ->values();

        return view('livewire.logistics.survey', [
            'participants' => $participants
        ])->layout('components.layouts.app'); // Trying standard layout first
    }
}
