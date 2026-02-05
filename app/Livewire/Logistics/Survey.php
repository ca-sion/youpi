<?php

namespace App\Livewire\Logistics;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\EventLogistic;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;

class Survey extends Component
{
    public EventLogistic $event_logistic;
    public $participantId = '';
    public $newName = '';
    public $isCoach = false;
    
    // Form data
    public $responses = []; // Per-day transport data
    public $hotel_needed = false;
    public $remarks;

    public function mount(EventLogistic $event_logistic)
    {
        $this->event_logistic = $event_logistic;

        SEOMeta::setTitle($event_logistic->event_name.' - Sondage logistique');
        OpenGraph::setTitle($event_logistic->event_name.' - Sondage logistique');
    }

    public function getDaysProperty()
    {
        $settings = $this->event_logistic->settings ?? [];
        $startDateStr = $settings['start_date'] ?? null;
        $daysCount = $settings['days_count'] ?? 3;

        if (!$startDateStr) return [];

        $days = [];
        $current = \Carbon\Carbon::parse($startDateStr);
        for ($i = 0; $i < $daysCount; $i++) {
            $date = $current->copy()->addDays($i);
            $days[] = [
                'date' => $date->toDateString(),
                'label' => ucfirst($date->translatedFormat('l d F')),
            ];
        }
        return $days;
    }

    public function getSelectedParticipantProperty()
    {
        if (!$this->participantId || $this->participantId === 'new') return null;
        return collect($this->event_logistic->participants_data ?? [])->firstWhere('id', (string)$this->participantId);
    }

    public function getCanRequestHotelProperty()
    {
        if ($this->participantId === 'new') {
            return $this->isCoach;
        }
        $p = $this->selected_participant;
        return $p && (($p['role'] ?? '') === 'coach' || str_contains($p['name'] ?? '', '[E]'));
    }

    public function getStatsProperty()
    {
        $participants = collect($this->event_logistic->participants_data ?? []);
        
        $responded = $participants->filter(fn($p) => isset($p['survey_response']))->sortBy('name');
        $notResponded = $participants->filter(fn($p) => !isset($p['survey_response']))->sortBy('name');

        return [
            'total' => $participants->count(),
            'responded_count' => $responded->count(),
            'not_responded_count' => $notResponded->count(),
            'responded' => $responded->pluck('name')->toArray(),
            'responded_full' => $responded->values()->toArray(),
            'not_responded' => $notResponded->pluck('name')->toArray(),
        ];
    }
    
    public function updatedParticipantId($value)
    {
        $this->reset(['responses', 'hotel_needed', 'remarks', 'newName', 'isCoach']);

        if ($value === 'new' || !$value) {
            return;
        }

        $p = $this->selected_participant;
        
        if ($p && isset($p['survey_response'])) {
            $r = $p['survey_response'];
            $this->responses = (array)($r['responses'] ?? []);
            $this->hotel_needed = $r['hotel_needed'] ?? false;
            $this->remarks = $r['remarks'] ?? null;
        }
    }

    public function submit()
    {
        $this->validate([
            'participantId' => 'required',
            'newName' => 'required_if:participantId,new',
        ]);
        
        $participants = $this->event_logistic->participants_data ?? [];
        $updated = false;
        
        $surveyResponse = [
            'responses' => $this->responses,
            'hotel_needed' => $this->can_request_hotel ? $this->hotel_needed : false,
            'remarks' => $this->remarks,
            'filled_at' => now()->toDateTimeString(),
        ];

        if ($this->participantId === 'new') {
            $name = $this->newName;
            if ($this->isCoach) {
                $name = '[E] ' . $name;
            }

            $participants[] = [
                'id' => (string) Str::uuid(),
                'name' => $name,
                'role' => $this->isCoach ? 'coach' : 'athlete',
                'survey_response' => $surveyResponse,
            ];
            $updated = true;
        } else {
            foreach ($participants as &$p) {
                if (isset($p['id']) && (string)$p['id'] === (string)$this->participantId) {
                    $p['survey_response'] = $surveyResponse;
                    $updated = true;
                    break;
                }
            }
        }
        
        if ($updated) {
            $this->event_logistic->update(['participants_data' => $participants]);
            session()->flash('message', 'Merci ! Votre réponse a été enregistrée.');
            if ($this->participantId === 'new') {
                $this->reset(['participantId', 'newName', 'isCoach', 'responses', 'hotel_needed', 'remarks']);
            }
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
            'participants' => $participants,
            'days' => $this->days
        ])->layout('components.layouts.app'); 
    }
}
