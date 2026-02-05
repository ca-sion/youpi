<?php

namespace App\Filament\Resources\EventLogisticResource\Pages;

use App\Filament\Resources\EventLogisticResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Carbon\Carbon;

class ManageTransport extends Page
{
    use InteractsWithRecord;

    protected static string $resource = EventLogisticResource::class;

    protected static string $view = 'filament.resources.event-logistic-resource.pages.manage-transport';

    public $transportPlans = []; // ['YYYY-MM-DD' => [vehicles...]]
    public $stayPlans = [];      // ['YYYY-MM-DD' => [rooms...]]
    public $unassignedTransport = [];
    public $unassignedStay = [];
    public $participantsMap = [];
    public $alerts = []; // [vehicleIndex => ['type' => 'msg']]
    public $globalAlerts = []; // ['msg' => '...']
    public $hotelNeededIds = [];
    public $days = [];
    public $selectedDay = null;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->loadData();
    }

    public function loadData()
    {
        $settings = $this->record->settings ?? [];
        $startDateStr = $settings['start_date'] ?? null;
        $daysCount = (int)($settings['days_count'] ?? 2);

        $this->days = [];
        if ($startDateStr) {
            $startDate = Carbon::parse($startDateStr);
            for ($i = 0; $i < $daysCount; $i++) {
                $date = $startDate->copy()->addDays($i);
                $this->days[] = [
                    'date' => $date->toDateString(),
                    'label' => $date->translatedFormat('D d M'),
                ];
            }
        }

        if (!$this->selectedDay && !empty($this->days)) {
            $this->selectedDay = $this->days[0]['date'];
        }

        // Load and Normalize Transport Plans
        $rawTransport = $this->record->transport_plan ?? [];
        if (isset($rawTransport[0])) { // Migration from flat array
            $newTransport = [];
            foreach ($rawTransport as $v) {
                $day = $v['departure_datetime'] ? substr($v['departure_datetime'], 0, 10) : ($this->selectedDay ?? date('Y-m-d'));
                $newTransport[$day][] = $v;
            }
            $rawTransport = $newTransport;
        }
        $this->transportPlans = $rawTransport;

        // Load and Normalize Stay Plans
        $rawStay = $this->record->stay_plan ?? [];
        if (isset($rawStay[0])) { // Migration from flat array to first day
            $rawStay = [$this->selectedDay => $rawStay];
        }
        $this->stayPlans = $rawStay;
        $participants = $this->record->participants_data ?? [];
        $this->participantsMap = collect($participants)->keyBy('id')->toArray();
        $this->alerts = [];
        $this->globalAlerts = [];
        $this->hotelNeededIds = collect($participants)->filter(fn($p) => $p['survey_response']['hotel_needed'] ?? false)->pluck('id')->toArray();

        // Ensure default settings
        $settings = $this->record->settings ?? [];
        if (!isset($settings['bus_capacity'])) {
            $settings['bus_capacity'] = 50;
            $this->record->update(['settings' => $settings]);
        }

        // Calculate unassigned & Alerts
        $assignedIds = [];
        
        $settings = $this->record->settings ?? [];
        $prep = $settings['temps_prep_min'] ?? 90;
        $dist = $settings['distance_km'] ?? 0;

        $currentDayTransport = $this->transportPlans[$this->selectedDay] ?? [];
        foreach ($currentDayTransport as $index => $vehicle) {
            $vPassengers = $vehicle['passengers'] ?? [];
            if (empty($vPassengers)) continue;

            foreach ($vPassengers as $pId) {
                $assignedIds[] = $pId;
            }

            // 1. Capacity Alert
            if (count($vPassengers) > ($vehicle['capacity'] ?? 0)) {
                $this->alerts[$index][] = ['type' => 'danger', 'msg' => 'Surcharge: ' . count($vPassengers) . '/' . $vehicle['capacity']];
            }

            // 2. Timing Alert
            if (!empty($vehicle['departure_datetime'])) {
                try {
                    $depTime = Carbon::parse($vehicle['departure_datetime']);
                    $speed = ($vehicle['type'] === 'bus') ? ($settings['vitesse_bus'] ?? 100) : ($settings['vitesse_voiture'] ?? 120);
                    $travelMin = ($speed > 0) ? ($dist / $speed * 60) : 0;
                    
                    $arrivalEst = $depTime->copy()->addMinutes($travelMin);
                    
                    foreach ($vPassengers as $pid) {
                        $p = $this->participantsMap[$pid] ?? null;
                        if ($p && isset($p['first_competition_datetime'])) {
                            $firstEvent = Carbon::parse($p['first_competition_datetime']);
                            $neededArrival = $firstEvent->copy()->subMinutes($prep);
                            
                            if ($arrivalEst->gt($neededArrival)) {
                                 $lateMin = $arrivalEst->diffInMinutes($neededArrival);
                                 $this->alerts[$index][] = ['type' => 'warning', 'msg' => "Retard échauffement: {$p['name']} (+{$lateMin}m)"];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Fail silently or log
                }
            }
        }

        // Transport Unassigned
        $assignedTransportIds = [];
        foreach ($this->transportPlans as $day => $vList) {
            if ($day === $this->selectedDay) {
                foreach ($vList as $v) {
                    $assignedTransportIds = array_merge($assignedTransportIds, $v['passengers'] ?? []);
                }
            }
        }
        
        $this->unassignedTransport = collect($participants)
            ->filter(function($p) {
                $dayResp = $p['survey_response']['responses'][$this->selectedDay] ?? null;
                if (!$dayResp) return false;
                
                $allerMode = $dayResp['aller']['mode'] ?? '';
                $retourMode = $dayResp['retour']['mode'] ?? '';
                return $allerMode === 'bus' || $retourMode === 'bus' || $allerMode === 'car_seats' || $retourMode === 'car_seats';
            })
            ->reject(fn($p) => in_array($p['id'], $assignedTransportIds))
            ->values()
            ->toArray();

        // Stay Unassigned
        $assignedStayIds = [];
        foreach ($this->stayPlans as $day => $rList) {
            if ($day === $this->selectedDay) {
                foreach ($rList as $r) {
                    $assignedStayIds = array_merge($assignedStayIds, $r['occupant_ids'] ?? []);
                }
            }
        }
        $this->unassignedStay = collect($participants)
            ->filter(fn($p) => $p['survey_response']['hotel_needed'] ?? false)
            ->reject(fn($p) => in_array($p['id'], $assignedStayIds))
            ->values()
            ->toArray();

        foreach ($participants as $p) {
            // 3. Hotel Alert
            $surveyHotel = $p['survey_response']['hotel_needed'] ?? false;
            $inHotel = in_array($p['id'], $assignedStayIds);

            if ($surveyHotel && !$inHotel) {
                 $this->globalAlerts[] = ['type' => 'danger', 'msg' => "Dodo manquant: {$p['name']}"];
            }
        }
        
        $this->dispatch('refresh-sortables');
    }

    public function updatedSelectedDay()
    {
        $this->record->update([
            'transport_plan' => $this->transportPlans,
            'stay_plan' => $this->stayPlans,
        ]);
        $this->loadData();
    }

    public function saveAllPlans($transportPlans, $stayPlans)
    {
        $this->transportPlans = $transportPlans;
        $this->stayPlans = $stayPlans;
        
        $this->record->update([
            'transport_plan' => $this->transportPlans,
            'stay_plan' => $this->stayPlans,
        ]);
        
        Notification::make()->title('Logistique enregistrée avec succès')->success()->send();
        $this->loadData();
    }
    
    public function removeVehicle($index)
    {
        if (isset($this->transportPlans[$this->selectedDay][$index])) {
            array_splice($this->transportPlans[$this->selectedDay], $index, 1);
            $this->record->update(['transport_plan' => $this->transportPlans]);
            $this->loadData();
            Notification::make()->title('Véhicule supprimé')->success()->send();
        }
    }

    public function removeRoom($index)
    {
        if (isset($this->stayPlans[$this->selectedDay][$index])) {
            array_splice($this->stayPlans[$this->selectedDay], $index, 1);
            $this->record->update(['stay_plan' => $this->stayPlans]);
            $this->loadData();
            Notification::make()->title('Chambre supprimée')->success()->send();
        }
    }

    public function addRoom()
    {
        if (!isset($this->stayPlans[$this->selectedDay])) {
            $this->stayPlans[$this->selectedDay] = [];
        }

        $this->stayPlans[$this->selectedDay][] = [
            'id' => 'room_' . uniqid(),
            'name' => 'Chambre ' . (count($this->stayPlans[$this->selectedDay]) + 1),
            'occupant_ids' => [],
            'note' => '',
        ];
        $this->record->update(['stay_plan' => $this->stayPlans]);
        $this->loadData();
    }

    public function addVehicle($type = 'car')
    {
        $settings = $this->record->settings ?? [];
        $defaultBusCapacity = $settings['bus_capacity'] ?? 50;

        if (!isset($this->transportPlans[$this->selectedDay])) {
            $this->transportPlans[$this->selectedDay] = [];
        }

        $this->transportPlans[$this->selectedDay][] = [
            'id' => 'manual_' . uniqid(),
            'type' => $type,
            'name' => ($type === 'bus' ? 'Nouveau Bus' : 'Nouvelle Voiture'),
            'capacity' => ($type === 'bus' ? $defaultBusCapacity : 4),
            'passengers' => [],
            'driver' => 'À définir',
            'departure_datetime' => $this->selectedDay . ' 08:00:00',
            'departure_location' => 'Sion, piscine',
            'note' => '',
        ];
        $this->record->update(['transport_plan' => $this->transportPlans]);
        $this->loadData();
        Notification::make()->title('Véhicule ajouté')->success()->send();
    }
    
    public function autoDispatch()
    {
        // 1. Get all candidates for the selected day (only bus needs)
        $participants = $this->record->participants_data ?? [];
        $candidates = [];
        foreach ($participants as $p) {
            $dayResp = $p['survey_response']['responses'][$this->selectedDay] ?? null;
            if (!$dayResp) continue;

            $allerMode = $dayResp['aller']['mode'] ?? '';
            $retourMode = $dayResp['retour']['mode'] ?? '';

            if ($allerMode === 'bus' || $retourMode === 'bus') {
                $candidates[] = $p;
            }
        }

        // 2. Identify Vehicles
        $settings = $this->record->settings ?? [];
        $defaultBusCapacity = $settings['bus_capacity'] ?? 50;
        
        $vehicles = [];
        // Add a Bus by default
        $vehicles[] = [
            'id' => 'bus_' . $this->selectedDay,
            'type' => 'bus',
            'name' => Carbon::parse($this->selectedDay)->translatedFormat('D') . ' - Bus du club',
            'capacity' => $defaultBusCapacity,
            'passengers' => [],
            'driver' => 'Chauffeur Bus'
        ];

        // Parent Cars from Survey for the selected day
        foreach ($participants as $p) {
            $dayResp = $p['survey_response']['responses'][$this->selectedDay] ?? null;
            if (!$dayResp) continue;

            $allerSeats = (int)($dayResp['aller']['seats'] ?? 0);
            $retourSeats = (int)($dayResp['retour']['seats'] ?? 0);
            $maxSeats = max($allerSeats, $retourSeats);

            if ($maxSeats > 0) {
                 $vehicles[] = [
                     'id' => 'car_' . $p['id'] . '_' . $this->selectedDay,
                     'type' => 'car',
                     'name' => 'Voiture ' . $p['name'],
                     'capacity' => $maxSeats,
                     'passengers' => [$p['id']], 
                     'driver' => 'Parent ' . $p['name']
                 ];
                 $candidates = array_filter($candidates, fn($c) => $c['id'] !== $p['id']);
            }
        }

        // 3. Fill Vehicles
        foreach ($vehicles as &$v) {
            if ($v['type'] !== 'bus') continue;
            while (count($v['passengers']) < $v['capacity'] && !empty($candidates)) {
                $p = array_shift($candidates);
                $v['passengers'][] = $p['id'];
            }
        }
        unset($v);

        foreach ($vehicles as &$v) {
            if ($v['type'] !== 'car') continue;
            $slots = $v['capacity'] - count($v['passengers']);
            while ($slots > 0 && !empty($candidates)) {
                $p = array_shift($candidates);
                 $v['passengers'][] = $p['id'];
                 $slots--;
            }
        }
        unset($v);

        // 4. Calculate Departure Times (Approximate based on first competition)
        $dist = $settings['distance_km'] ?? 0;
        $prep = $settings['temps_prep_min'] ?? 90;
        
        foreach ($vehicles as &$v) {
            $speed = ($v['type'] === 'bus') ? ($settings['vitesse_bus'] ?? 100) : ($settings['vitesse_voiture'] ?? 120);
            $travelTimeHours = ($speed > 0) ? ($dist / $speed) : 0;
            $travelTimeMin = $travelTimeHours * 60;
            $totalOffset = $prep + $travelTimeMin;
            
            $firstTime = null;
            foreach ($v['passengers'] as $pid) {
                $p = collect($participants)->firstWhere('id', $pid);
                if ($p && isset($p['first_competition_datetime'])) {
                    $dt = Carbon::parse($p['first_competition_datetime']);
                    // Only care about events on the selected day
                    if ($dt->toDateString() === $this->selectedDay) {
                        if (!$firstTime || $dt->lt($firstTime)) {
                            $firstTime = $dt;
                        }
                    }
                }
            }
            
            if ($firstTime) {
                $v['departure_datetime'] = $firstTime->copy()->subMinutes($totalOffset)->toDateTimeString();
                $v['departure_location'] = 'Sion, piscine';
            } else {
                $v['departure_datetime'] = null;
            }
        }
        unset($v);

        $this->transportPlans[$this->selectedDay] = $vehicles;
        $this->record->update(['transport_plan' => $this->transportPlans]);
        $this->loadData();
        Notification::make()->title('Calcul automatique terminé (Jour : ' . $this->selectedDay . ')')->success()->send();
    }
    
    protected function getHeaderActions(): array
    {
        return [
             Action::make('auto_dispatch')
                ->label(fn() => "Calcul Auto " . ($this->selectedDay ? "($this->selectedDay)" : ""))
                ->tooltip('Génère un plan pour le jour sélectionné (écrase le plan actuel de ce jour)')
                ->requiresConfirmation()
                ->modalHeading('Réinitialiser le plan pour ce jour ?')
                ->modalDescription('Cela va supprimer les attributions actuelles des véhicules pour générer un nouveau plan basé sur les sondages.')
                ->action(fn() => $this->autoDispatch())
                ->color('danger')
                ->icon('heroicon-o-arrow-path'),
        ];
    }

    public function getParticipantTimes($pId)
    {
        $p = $this->participantsMap[$pId] ?? null;
        if (!$p) return null;

        $start = $p['first_competition_datetime'] ?? null;
        $end = $p['last_competition_datetime'] ?? null;

        if (!$start) return null;

        // Filter for selected day
        $startDt = Carbon::parse($start);
        if ($startDt->toDateString() !== $this->selectedDay) return null;

        $startStr = $startDt->format('H:i');
        $endStr = $end ? Carbon::parse($end)->format('H:i') : '...';

        return "{$startStr} - {$endStr}";
    }
}
