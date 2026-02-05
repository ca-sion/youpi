<?php

namespace App\Filament\Resources\EventLogisticResource\Pages;

use App\Filament\Resources\EventLogisticResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ManageTransport extends Page
{
    use InteractsWithRecord;

    protected static string $resource = EventLogisticResource::class;

    protected static string $view = 'filament.resources.event-logistic-resource.pages.manage-transport';

    public $transportPlans = []; // ['YYYY-MM-DD' => [vehicles...]]
    public $stayPlans = [];      // ['YYYY-MM-DD' => [rooms...]]
    public $unassignedTransport = []; // Aller
    public $unassignedTransportRetour = [];
    public $unassignedStay = [];
    public $participantsMap = [];
    public $alerts = []; // [vehicleIndex => ['type' => 'msg']]
    public $globalAlerts = []; // ['msg' => '...']
    public $hotelNeededIds = [];
    public $days = [];
    public $selectedDay = null;
    public $independentAller = [];
    public $independentRetour = [];
    public $autoHotelIds = [];
    public $hotelOverrideIds = [];

    public function getTitle(): string
    {
        return 'Gestion logistique';
    }

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
        $threshold = $settings['home_departure_threshold'] ?? '07:00';

        $this->days = [];
        $participants = collect($this->record->participants_data ?? [])->map(function($p) {
            if (isset($p['id'])) $p['id'] = (string)$p['id'];
            return $p;
        });
        $this->participantsMap = $participants->keyBy('id')->toArray();

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
                $day = ($v['departure_datetime'] ?? null) ? substr($v['departure_datetime'], 0, 10) : ($this->selectedDay ?? date('Y-m-d'));
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
        $this->alerts = [];
        $this->globalAlerts = [];

        $this->hotelOverrideIds = $participants->filter(fn($p) => $p['hotel_override'] ?? false)->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $this->autoHotelIds = [];

        // Auto suggestion logic
        foreach ($participants as $p) {
            $neededAuto = false;
            
            // A. Consecutive days
            $pDays = [];
            foreach ($this->days as $d) {
                if (isset($p['survey_response']['responses'][$d['date']])) {
                    $pDays[] = $d['date'];
                }
            }
            
            $currentDayIdx = array_search($this->selectedDay, array_column($this->days, 'date'));
            if ($currentDayIdx !== false && $currentDayIdx < count($this->days) - 1) {
                $nextDay = $this->days[$currentDayIdx + 1]['date'];
                if (in_array($this->selectedDay, $pDays) && in_array($nextDay, $pDays)) {
                    $neededAuto = true;
                }
            }

            // B. Early start
            if (!$neededAuto && isset($p['first_competition_datetime'])) {
                $firstComp = Carbon::parse($p['first_competition_datetime']);
                if ($firstComp->toDateString() === $this->selectedDay) {
                    $prep = (int)($settings['duration_prep_min'] ?? 90);
                    $dist = (float)($settings['distance_km'] ?? 0);
                    $carSpeed = (float)($settings['car_speed'] ?? 100);
                    $travelTime = ($carSpeed > 0) ? ($dist / $carSpeed * 60) : 0;
                    
                    $departureFromHome = $firstComp->copy()->subMinutes($prep)->subMinutes($travelTime);
                    if ($departureFromHome->format('H:i') < $threshold) {
                        $neededAuto = true;
                    }
                }
            }

            if ($neededAuto) {
                $this->autoHotelIds[] = (string)$p['id'];
            }
        }

        $surveyHotelIds = $participants->filter(fn($p) => $p['survey_response']['hotel_needed'] ?? false)->pluck('id')->map(fn($id) => (string)$id)->toArray();
        
        $this->hotelNeededIds = array_values(array_unique(array_merge(
            $this->hotelOverrideIds,
            $this->autoHotelIds,
            $surveyHotelIds
        )));

        // Ensure default settings
        $settings = $this->record->settings ?? [];
        if (!isset($settings['bus_capacity'])) {
            $settings['bus_capacity'] = 50;
            $this->record->update(['settings' => $settings]);
        }

        // Out-of-sync logic
        $surveyUpdatedAt = $settings['survey_updated_at'] ?? null;
        $lastAutoDispatchAt = $settings['last_auto_dispatch_at'] ?? null;
        if ($surveyUpdatedAt && (!$lastAutoDispatchAt || $surveyUpdatedAt > $lastAutoDispatchAt)) {
            $this->globalAlerts[] = [
                'type' => 'warning', 
                'msg' => 'Les données du sondage ont été modifiées depuis le dernier calcul. Les plans affichés peuvent être obsolètes. Relancez le "Calcul Auto" pour synchroniser.'
            ];
        }

        // Calculate unassigned & Alerts
        $assignedIds = [];
        
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
                    $dist = $settings['distance_km'] ?? 0;
                    $speed = ($vehicle['type'] === 'bus') ? ($settings['bus_speed'] ?? 100) : ($settings['car_speed'] ?? 120);
                    $travelMin = ($speed > 0) ? ($dist / $speed * 60) : 0;
                    
                    $arrivalEst = $depTime->copy()->addMinutes($travelMin);
                    $flow = $vehicle['flow'] ?? 'aller';
                    $prep = $settings['duration_prep_min'] ?? 90;

                    foreach ($vPassengers as $pid) {
                        $p = $this->participantsMap[$pid] ?? null;
                        if (!$p) continue;

                        if ($flow === 'retour') {
                            if (isset($p['last_competition_datetime'])) {
                                $lastEvent = Carbon::parse($p['last_competition_datetime']);
                                if ($depTime->lt($lastEvent)) {
                                    $this->alerts[$index][] = ['type' => 'danger', 'msg' => "Départ anticipé: {$p['name']} (Epreuve finit à {$lastEvent->format('H:i')})"];
                                }
                            }
                        } else {
                            if (isset($p['first_competition_datetime'])) {
                                $firstEvent = Carbon::parse($p['first_competition_datetime']);
                                $neededArrival = $firstEvent->copy()->subMinutes($prep);
                                
                                if ($arrivalEst->gt($neededArrival)) {
                                     $lateMin = $arrivalEst->diffInMinutes($neededArrival);
                                     $this->alerts[$index][] = ['type' => 'warning', 'msg' => "Retard échauffement: {$p['name']} (+{$lateMin}m)"];
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Fail silently or log
                }
            }
        }

        // Transport Unassigned vs Independent
        $assignedAllerIds = [];
        $assignedRetourIds = [];
        
        foreach ($this->transportPlans as $day => $vList) {
            if ($day === $this->selectedDay) {
                foreach ($vList as $v) {
                    $flow = $v['flow'] ?? 'aller';
                    if ($flow === 'retour') {
                        $assignedRetourIds = array_merge($assignedRetourIds, $v['passengers'] ?? []);
                    } else {
                        $assignedAllerIds = array_merge($assignedAllerIds, $v['passengers'] ?? []);
                    }
                }
            }
        }

        $allDayParticipants = $participants->filter(function($p) {
            return isset($p['survey_response']['responses'][$this->selectedDay]);
        });

        $this->unassignedTransport = $allDayParticipants
            ->filter(function($p) {
                $mode = $p['survey_response']['responses'][$this->selectedDay]['aller']['mode'] ?? '';
                return $mode === 'bus';
            })
            ->reject(fn($p) => in_array($p['id'], $assignedAllerIds))
            ->values()
            ->toArray();

        $this->independentAller = $allDayParticipants
            ->filter(function($p) {
                $mode = $p['survey_response']['responses'][$this->selectedDay]['aller']['mode'] ?? '';
                return in_array($mode, ['train', 'car', 'on_site']);
            })
            ->reject(fn($p) => in_array($p['id'], $assignedAllerIds))
            ->values()
            ->toArray();

        $this->unassignedTransportRetour = $allDayParticipants
            ->filter(function($p) {
                $mode = $p['survey_response']['responses'][$this->selectedDay]['retour']['mode'] ?? '';
                return $mode === 'bus';
            })
            ->reject(fn($p) => in_array($p['id'], $assignedRetourIds))
            ->values()
            ->toArray();

        $this->independentRetour = $allDayParticipants
            ->filter(function($p) {
                $mode = $p['survey_response']['responses'][$this->selectedDay]['retour']['mode'] ?? '';
                return in_array($mode, ['train', 'car', 'on_site']);
            })
            ->reject(fn($p) => in_array($p['id'], $assignedRetourIds))
            ->values()
            ->toArray();

        // Stay Unassigned
        $isLastDay = !empty($this->days) && $this->selectedDay === end($this->days)['date'];

        if ($isLastDay) {
            $this->unassignedStay = [];
        } else {
            $assignedStayIds = [];
            foreach ($this->stayPlans as $day => $rList) {
                if ($day === $this->selectedDay) {
                    foreach ($rList as $r) {
                        $assignedStayIds = array_merge($assignedStayIds, $r['occupant_ids'] ?? []);
                    }
                }
            }

            $this->unassignedStay = collect($participants)
                ->filter(fn($p) => in_array($p['id'], $this->hotelNeededIds))
                ->reject(fn($p) => in_array($p['id'], $assignedStayIds))
                ->values()
                ->toArray();

            foreach ($participants as $p) {
                // 3. Hotel Alert
                $needsHotel = in_array($p['id'], $this->hotelNeededIds);
                $inHotel = in_array($p['id'], $assignedStayIds);

                if ($needsHotel && !$inHotel) {
                     $this->globalAlerts[] = ['type' => 'danger', 'msg' => "Nuit manquante: {$p['name']}"];
                }
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

    public function addVehicle($type = 'car', $flow = 'aller')
    {
        $settings = $this->record->settings ?? [];
        $defaultBusCapacity = $settings['bus_capacity'] ?? 50;

        if (!isset($this->transportPlans[$this->selectedDay])) {
            $this->transportPlans[$this->selectedDay] = [];
        }

        $flowLabel = ($flow === 'retour' ? 'Retour' : 'Aller');

        $this->transportPlans[$this->selectedDay][] = [
            'id' => 'manual_' . uniqid(),
            'type' => $type,
            'flow' => $flow,
            'name' => ($type === 'bus' ? "Nouveau Bus $flowLabel" : "Nouvelle Voiture $flowLabel"),
            'capacity' => ($type === 'bus' ? $defaultBusCapacity : 4),
            'passengers' => [],
            'driver' => 'À définir',
            'departure_datetime' => $this->selectedDay . ($flow === 'retour' ? ' 17:00:00' : ' 08:00:00'),
            'departure_location' => ($flow === 'retour' ? 'Lieu compétition' : 'Sion, piscine'),
            'note' => '',
        ];
        $this->record->update(['transport_plan' => $this->transportPlans]);
        $this->loadData();
        Notification::make()->title('Véhicule ajouté')->success()->send();
    }
    
    public function autoDispatch()
    {
        // 1. Get all candidates for the selected day
        $participants = $this->record->participants_data ?? [];
        $candidatesAller = [];
        $candidatesRetour = [];
        
        foreach ($participants as $p) {
            $dayResp = $p['survey_response']['responses'][$this->selectedDay] ?? null;
            if (!$dayResp) continue;

            if (($dayResp['aller']['mode'] ?? '') === 'bus') {
                $candidatesAller[] = $p;
            }
            if (($dayResp['retour']['mode'] ?? '') === 'bus') {
                $candidatesRetour[] = $p;
            }
        }

        // 2. Identify Vehicles
        $settings = $this->record->settings ?? [];
        $defaultBusCapacity = $settings['bus_capacity'] ?? 50;
        
        $vehicles = [];
        
        // Add Buses by default if needed
        if (!empty($candidatesAller)) {
            $vehicles[] = [
                'id' => 'bus_aller_' . $this->selectedDay,
                'type' => 'bus',
                'flow' => 'aller',
                'name' => Carbon::parse($this->selectedDay)->translatedFormat('D') . ' - Bus Aller',
                'capacity' => $defaultBusCapacity,
                'passengers' => [],
                'driver' => 'Chauffeur Bus',
                'departure_datetime' => $this->selectedDay . ' 07:30:00',
                'departure_location' => 'Sion, piscine'
            ];
        }
        if (!empty($candidatesRetour)) {
            $vehicles[] = [
                'id' => 'bus_retour_' . $this->selectedDay,
                'type' => 'bus',
                'flow' => 'retour',
                'name' => Carbon::parse($this->selectedDay)->translatedFormat('D') . ' - Bus Retour',
                'capacity' => $defaultBusCapacity,
                'passengers' => [],
                'driver' => 'Chauffeur Bus',
                'departure_datetime' => $this->selectedDay . ' 17:30:00',
                'departure_location' => 'Lieu compétition'
            ];
        }

        // Parent Cars from Survey
        foreach ($participants as $p) {
            $dayResp = $p['survey_response']['responses'][$this->selectedDay] ?? null;
            if (!$dayResp) continue;

            $allerSeats = (int)($dayResp['aller']['seats'] ?? 0);
            $retourSeats = (int)($dayResp['retour']['seats'] ?? 0);

            if ($allerSeats > 0) {
                 $vehicles[] = [
                     'id' => 'car_aller_' . $p['id'] . '_' . $this->selectedDay,
                     'type' => 'car',
                     'flow' => 'aller',
                     'name' => 'Voiture ' . $p['name'] . ' (Aller)',
                     'capacity' => $allerSeats,
                     'passengers' => [$p['id']], 
                     'driver' => 'Parent ' . $p['name'],
                     'departure_datetime' => $this->selectedDay . ' 07:30:00',
                     'departure_location' => 'Sion, piscine'
                 ];
                 $candidatesAller = array_filter($candidatesAller, fn($c) => $c['id'] !== $p['id']);
            }
            
            if ($retourSeats > 0) {
                 $vehicles[] = [
                     'id' => 'car_retour_' . $p['id'] . '_' . $this->selectedDay,
                     'type' => 'car',
                     'flow' => 'retour',
                     'name' => 'Voiture ' . $p['name'] . ' (Retour)',
                     'capacity' => $retourSeats,
                     'passengers' => [$p['id']], 
                     'driver' => 'Parent ' . $p['name'],
                     'departure_datetime' => $this->selectedDay . ' 17:30:00',
                     'departure_location' => 'Lieu compétition'
                 ];
                 $candidatesRetour = array_filter($candidatesRetour, fn($c) => $c['id'] !== $p['id']);
            }
        }

        // 3. Fill Vehicles
        foreach ($vehicles as &$v) {
            $isRetour = ($v['flow'] === 'retour');
            $targetCandidates = $isRetour ? $candidatesRetour : $candidatesAller;
            if (empty($targetCandidates)) continue;

            $slots = $v['capacity'] - count($v['passengers']);
            while ($slots > 0 && !empty($targetCandidates)) {
                $p = array_shift($targetCandidates);
                $v['passengers'][] = $p['id'];
                $slots--;
            }
            
            // Sync back the candidates lists
            if ($isRetour) {
                $candidatesRetour = $targetCandidates;
            } else {
                $candidatesAller = $targetCandidates;
            }
        }
        unset($v);

        // 4. Calculate Departure Times
        $dist = $settings['distance_km'] ?? 0;
        $prep = $settings['duration_prep_min'] ?? 90;
        
        foreach ($vehicles as &$v) {
            $isRetour = ($v['flow'] === 'retour');
            
            if ($isRetour) {
                // Return: Departure = Last competition + some buffer (e.g. 30min)
                $lastTime = null;
                foreach ($v['passengers'] as $pid) {
                    $p = collect($participants)->firstWhere('id', $pid);
                    if ($p && isset($p['last_competition_datetime'])) {
                        $dt = Carbon::parse($p['last_competition_datetime']);
                        if ($dt->toDateString() === $this->selectedDay) {
                            if (!$lastTime || $dt->gt($lastTime)) {
                                $lastTime = $dt;
                            }
                        }
                    }
                }
                if ($lastTime) {
                    $v['departure_datetime'] = $lastTime->copy()->addMinutes(30)->toDateTimeString();
                }
            } else {
                // Aller: Departure = First competition - (dist/speed + prep)
                $speed = ($v['type'] === 'bus') ? ($settings['bus_speed'] ?? 100) : ($settings['car_speed'] ?? 120);
                $travelTimeMin = ($speed > 0) ? ($dist / $speed * 60) : 0;
                $totalOffset = $prep + $travelTimeMin;
                
                $firstTime = null;
                foreach ($v['passengers'] as $pid) {
                    $p = collect($participants)->firstWhere('id', $pid);
                    if ($p && isset($p['first_competition_datetime'])) {
                        $dt = Carbon::parse($p['first_competition_datetime']);
                        if ($dt->toDateString() === $this->selectedDay) {
                            if (!$firstTime || $dt->lt($firstTime)) {
                                $firstTime = $dt;
                            }
                        }
                    }
                }
                if ($firstTime) {
                    $v['departure_datetime'] = $firstTime->copy()->subMinutes($totalOffset)->toDateTimeString();
                }
            }
        }
        unset($v);

        $this->transportPlans[$this->selectedDay] = $vehicles;
        
        $settings = $this->record->settings ?? [];
        $settings['last_auto_dispatch_at'] = now()->toDateTimeString();
        $this->record->update([
            'transport_plan' => $this->transportPlans,
            'settings' => $settings,
        ]);
        
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
            Action::make('edit')
                ->label(fn() => "Editer")
                ->url(fn(Model $record) => EventLogisticResource::getUrl('edit', ['record' => $record]))
                ->color('gray')
                ->icon('heroicon-o-pencil'),
        ];
    }

    public function getParticipantStartTime($pId)
    {
        $p = $this->participantsMap[$pId] ?? null;
        if (!$p) return null;

        $start = $p['first_competition_datetime'] ?? null;
        if (!$start) return null;

        $startDt = Carbon::parse($start);
        if ($startDt->toDateString() !== $this->selectedDay) return null;

        return $startDt->format('H:i');
    }

    public function getParticipantEndTime($pId)
    {
        $p = $this->participantsMap[$pId] ?? null;
        if (!$p) return null;

        $end = $p['last_competition_datetime'] ?? null;
        if (!$end) return null;

        $endDt = Carbon::parse($end);
        if ($endDt->toDateString() !== $this->selectedDay) return null;

        return $endDt->format('H:i');
    }

    public function getParticipantTimes($pId)
    {
        $p = $this->participantsMap[$pId] ?? null;
        if (!$p) return null;

        $start = $this->getParticipantStartTime($pId);
        $end = $this->getParticipantEndTime($pId) ?? '...';

        if (!$start) return null;

        return "{$start} - {$end}";
    }
}
