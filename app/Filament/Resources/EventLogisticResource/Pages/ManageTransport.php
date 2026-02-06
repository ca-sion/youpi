<?php

namespace App\Filament\Resources\EventLogisticResource\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Filament\Resources\EventLogisticResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

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

    public $autoHotelIds = [];

    public $hotelOverrideIds = [];

    public $hotelBlockedIds = [];

    public $hotelNeededList = [];

    public $days = [];

    public $selectedDay = null;

    public $independentAller = [];

    public $independentRetour = [];

    public $planningMode = 'survey'; // survey, schedule, all

    public function getTitle(): string
    {
        return 'Gestion logistique';
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->planningMode = $this->record->settings['planning_mode'] ?? 'survey';
        $this->loadData(true);
    }

    public function loadData($forceFromDisk = false)
    {
        $settings = $this->record->settings ?? [];
        $startDateStr = $settings['start_date'] ?? null;
        $daysCount = (int) ($settings['days_count'] ?? 2);
        $threshold = $settings['home_departure_threshold'] ?? '07:00';

        $this->days = [];
        $participants = collect($this->record->participants_data ?? [])->map(function ($p) {
            if (isset($p['id'])) {
                $p['id'] = (string) $p['id'];
            }

            return $p;
        });
        $this->participantsMap = $participants->keyBy('id')->toArray();

        if ($startDateStr) {
            $startDate = Carbon::parse($startDateStr);
            for ($i = 0; $i < $daysCount; $i++) {
                $date = $startDate->copy()->addDays($i);
                $this->days[] = [
                    'date'  => $date->toDateString(),
                    'label' => $date->translatedFormat('D d M'),
                ];
            }
        }

        if (! $this->selectedDay && ! empty($this->days)) {
            $this->selectedDay = $this->days[0]['date'];
        }

        // Load and Normalize Transport Plans (only if forced)
        if ($forceFromDisk || empty($this->transportPlans)) {
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
        }

        // Load and Normalize Stay Plans (only if forced)
        if ($forceFromDisk || empty($this->stayPlans)) {
            $rawStay = $this->record->stay_plan ?? [];
            if (isset($rawStay[0])) { // Migration from flat array to first day
                $rawStay = [$this->selectedDay => $rawStay];
            }
            $this->stayPlans = $rawStay;
        }
        $this->alerts = [];
        $this->globalAlerts = [];

        $this->hotelOverrideIds = $participants->filter(fn ($p) => ($p['hotel_override'] ?? false) || ($p['role'] ?? '') === 'manual')->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->autoHotelIds = [];

        $this->hotelOverrideIds = []; // Forcé manuellement (true)
        $this->hotelBlockedIds = [];  // Bloqué manuellement (false)
        $this->autoHotelIds = [];     // Suggéré par l'algorithme

        foreach ($participants as $p) {
            $pId = (string) $p['id'];
            $override = $p['hotel_override'] ?? null;

            if ($override === true) {
                $this->hotelOverrideIds[] = $pId;
            } elseif ($override === false) {
                $this->hotelBlockedIds[] = $pId;
            } else {
                // Logique de suggestion automatique
                $neededAuto = false;

                // A. Jours consécutifs
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

                // B. Départ matinal
                $firstCompStr = null;
                if (isset($p['competition_days'][$this->selectedDay]['first'])) {
                    $firstCompStr = $p['competition_days'][$this->selectedDay]['first'];
                } elseif (isset($p['first_competition_datetime'])) {
                    $firstCompStr = $p['first_competition_datetime'];
                }

                if (! $neededAuto && $firstCompStr) {
                    $firstComp = Carbon::parse($firstCompStr);
                    if ($firstComp->toDateString() === $this->selectedDay) {
                        $prep = (int) ($settings['duration_prep_min'] ?? 90);
                        $dist = (float) ($settings['distance_km'] ?? 0);
                        $carSpeed = (float) ($settings['car_speed'] ?? 100);
                        $travelTime = ($carSpeed > 0) ? ($dist / $carSpeed * 60) : 0;

                        $departureFromHome = $firstComp->copy()->subMinutes($prep)->subMinutes($travelTime);
                        if ($departureFromHome->format('H:i') < $threshold) {
                            $neededAuto = true;
                        }
                    }
                }

                // C. Sondage direct
                if (! $neededAuto && ($p['survey_response']['hotel_needed'] ?? false)) {
                    $neededAuto = true;
                }

                if ($neededAuto) {
                    $this->autoHotelIds[] = $pId;
                }
            }
        }

        $this->hotelNeededIds = array_values(array_unique(array_merge(
            $this->hotelOverrideIds,
            $this->autoHotelIds
        )));

        // On retire ceux qui sont explicitement bloqués (sécurite supplémentaire)
        $this->hotelNeededIds = array_values(array_diff($this->hotelNeededIds, $this->hotelBlockedIds));

        // Ensure default settings
        $settings = $this->record->settings ?? [];
        if (! isset($settings['bus_capacity'])) {
            $settings['bus_capacity'] = 50;
            $this->record->update(['settings' => $settings]);
        }

        // Out-of-sync logic
        $surveyUpdatedAt = $settings['survey_updated_at'] ?? null;
        $lastAutoDispatchAt = $settings['last_auto_dispatch_at'] ?? null;
        if ($surveyUpdatedAt && (! $lastAutoDispatchAt || $surveyUpdatedAt > $lastAutoDispatchAt)) {
            $this->globalAlerts[] = [
                'type' => 'warning',
                'msg'  => 'Les données du sondage ont été modifiées depuis le dernier calcul. Les plans affichés peuvent être obsolètes. Relancez le "Calcul auto" pour synchroniser.',
            ];
        }

        // Calculate unassigned & Alerts
        $assignedIds = [];

        $currentDayTransport = $this->transportPlans[$this->selectedDay] ?? [];
        foreach ($currentDayTransport as $index => $vehicle) {
            $vPassengers = $vehicle['passengers'] ?? [];
            if (empty($vPassengers)) {
                continue;
            }

            foreach ($vPassengers as $pId) {
                $assignedIds[] = $pId;
            }

            // 1. Capacity Alert
            if (count($vPassengers) > ($vehicle['capacity'] ?? 0)) {
                $this->alerts[$index][] = ['type' => 'danger', 'msg' => 'Surcharge: '.count($vPassengers).'/'.$vehicle['capacity']];
            }

            // 2. Timing Alert
            if (! empty($vehicle['departure_datetime'])) {
                try {
                    $depTime = Carbon::parse($vehicle['departure_datetime']);
                    $dist = (float) ($settings['distance_km'] ?? 0);
                    $speed = (float) (($vehicle['type'] === 'bus') ? ($settings['bus_speed'] ?? 80) : ($settings['car_speed'] ?? 100));
                    $travelMin = ($speed > 0) ? ($dist / $speed * 60) : 0;

                    $arrivalEst = $depTime->copy()->addMinutes($travelMin);
                    $flow = $vehicle['flow'] ?? 'aller';
                    $prep = $settings['duration_prep_min'] ?? 90;

                    foreach ($vPassengers as $pid) {
                        $p = $this->participantsMap[$pid] ?? null;
                        if (! $p) {
                            continue;
                        }

                        if ($flow === 'retour') {
                            $lastCompStr = null;
                            if (isset($p['competition_days'][$this->selectedDay]['last'])) {
                                $lastCompStr = $p['competition_days'][$this->selectedDay]['last'];
                            } elseif (isset($p['last_competition_datetime'])) {
                                if (str_starts_with($p['last_competition_datetime'], $this->selectedDay)) {
                                    $lastCompStr = $p['last_competition_datetime'];
                                }
                            }

                            if ($lastCompStr) {
                                $lastEvent = Carbon::parse($lastCompStr);
                                $recup = (int) ($settings['duration_recup_min'] ?? 60);
                                if ($depTime->lt($lastEvent)) {
                                    $this->alerts[$index][] = ['type' => 'danger', 'msg' => "DÉPART IMPOSSIBLE: {$p['name']} finit à {$lastEvent->format('H:i')}"];
                                } elseif ($depTime->lt($lastEvent->copy()->addMinutes($recup))) {
                                    $this->alerts[$index][] = ['type' => 'warning', 'msg' => "Récup. courte: {$p['name']} (finit à {$lastEvent->format('H:i')} + {$recup}m > {$depTime->format('H:i')})"];
                                }
                            }
                        } else {
                            $firstCompStr = null;
                            if (isset($p['competition_days'][$this->selectedDay]['first'])) {
                                $firstCompStr = $p['competition_days'][$this->selectedDay]['first'];
                            } elseif (isset($p['first_competition_datetime'])) {
                                if (str_starts_with($p['first_competition_datetime'], $this->selectedDay)) {
                                    $firstCompStr = $p['first_competition_datetime'];
                                }
                            }

                            if ($firstCompStr) {
                                $firstEvent = Carbon::parse($firstCompStr);
                                $prep = (int) ($settings['duration_prep_min'] ?? 90);
                                if ($arrivalEst->gt($firstEvent)) {
                                    $this->alerts[$index][] = ['type' => 'danger', 'msg' => "RETARD: {$p['name']} arrive à {$arrivalEst->format('H:i')} alors que l'épreuve commence à {$firstEvent->format('H:i')}"];
                                } elseif ($arrivalEst->copy()->addMinutes($prep)->gt($firstEvent)) {
                                    $this->alerts[$index][] = ['type' => 'warning', 'msg' => "Retard prépa: {$p['name']} (arrivée est. {$arrivalEst->format('H:i')} + {$prep}m > {$firstEvent->format('H:i')})"];
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

        $allDayParticipants = $participants->filter(function ($p) {
            $hasSurvey = isset($p['survey_response']['responses'][$this->selectedDay]);
            $isManual = ($p['is_manual'] ?? false);
            $hasSchedule = isset($p['competition_days'][$this->selectedDay]);

            if ($this->planningMode === 'schedule') {
                return $hasSurvey || $hasSchedule || $isManual;
            }

            if ($this->planningMode === 'all') {
                return true;
            }

            // Default 'survey' mode
            return $hasSurvey || $isManual;
        });

        $this->unassignedTransport = $allDayParticipants
            ->filter(function ($p) {
                $role = $p['role'] ?? 'athlete';
                $mode = $p['survey_response']['responses'][$this->selectedDay]['aller']['mode'] ?? '';

                // En mode survey, on ne prend que ceux qui ont explicitement demandé le bus ou les manuels
                if ($this->planningMode === 'survey') {
                    return $mode === 'bus' || ($p['is_manual'] ?? false);
                }

                // En mode schedule ou all, on prend tout le monde sauf si ils ont explicitement dit "car/train/on_site"
                return ! in_array($mode, ['train', 'car', 'on_site']);
            })
            ->reject(fn ($p) => in_array($p['id'], $assignedAllerIds))
            ->values()
            ->toArray();

        $this->independentAller = $allDayParticipants
            ->filter(function ($p) {
                $mode = $p['survey_response']['responses'][$this->selectedDay]['aller']['mode'] ?? '';

                return in_array($mode, ['train', 'car', 'on_site']);
            })
            ->reject(fn ($p) => in_array($p['id'], $assignedAllerIds))
            ->values()
            ->toArray();

        $this->unassignedTransportRetour = $allDayParticipants
            ->filter(function ($p) {
                $role = $p['role'] ?? 'athlete';
                $mode = $p['survey_response']['responses'][$this->selectedDay]['retour']['mode'] ?? '';

                if ($this->planningMode === 'survey') {
                    return $mode === 'bus' || ($p['is_manual'] ?? false);
                }

                return ! in_array($mode, ['train', 'car', 'on_site']);
            })
            ->reject(fn ($p) => in_array($p['id'], $assignedRetourIds))
            ->values()
            ->toArray();

        $this->independentRetour = $allDayParticipants
            ->filter(function ($p) {
                $mode = $p['survey_response']['responses'][$this->selectedDay]['retour']['mode'] ?? '';

                return in_array($mode, ['train', 'car', 'on_site']);
            })
            ->reject(fn ($p) => in_array($p['id'], $assignedRetourIds))
            ->values()
            ->toArray();

        // Stay Unassigned
        $isLastDay = ! empty($this->days) && $this->selectedDay === end($this->days)['date'];

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
                ->filter(fn ($p) => in_array($p['id'], $this->hotelNeededIds))
                ->reject(fn ($p) => in_array($p['id'], $assignedStayIds))
                ->values()
                ->toArray();

            foreach ($participants as $p) {
                // 3. Hotel Alert
                $needsHotel = in_array($p['id'], $this->hotelNeededIds);
                $inHotel = in_array($p['id'], $assignedStayIds);

                if ($needsHotel && ! $inHotel) {
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
            'stay_plan'      => $this->stayPlans,
        ]);
        $this->loadData();
    }

    public function updatedPlanningMode()
    {
        $this->loadData();
    }

    public function updatedTransportPlans()
    {
        $this->loadData();
    }

    public function updatedStayPlans()
    {
        $this->loadData();
    }

    public function saveAllPlans($transportPlans, $stayPlans)
    {
        $this->transportPlans = $transportPlans;
        $this->stayPlans = $stayPlans;

        $this->record->update([
            'transport_plan' => $this->transportPlans,
            'stay_plan'      => $this->stayPlans,
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
        if (! isset($this->stayPlans[$this->selectedDay])) {
            $this->stayPlans[$this->selectedDay] = [];
        }

        $this->stayPlans[$this->selectedDay][] = [
            'id'           => 'room_'.uniqid(),
            'name'         => 'Chambre '.(count($this->stayPlans[$this->selectedDay]) + 1),
            'occupant_ids' => [],
            'note'         => '',
        ];
        $this->record->update(['stay_plan' => $this->stayPlans]);
        $this->loadData();
    }

    public function addVehicle($type = 'car', $flow = 'aller')
    {
        $settings = $this->record->settings ?? [];
        $defaultBusCapacity = $settings['bus_capacity'] ?? 50;

        if (! isset($this->transportPlans[$this->selectedDay])) {
            $this->transportPlans[$this->selectedDay] = [];
        }

        $flowLabel = ($flow === 'retour' ? 'Retour' : 'Aller');

        $this->transportPlans[$this->selectedDay][] = [
            'id'                 => 'manual_'.uniqid(),
            'type'               => $type,
            'flow'               => $flow,
            'name'               => ($type === 'bus' ? "Nouveau Bus $flowLabel" : "Nouvelle Voiture $flowLabel"),
            'capacity'           => ($type === 'bus' ? $defaultBusCapacity : 4),
            'passengers'         => [],
            'driver'             => 'À définir',
            'departure_datetime' => $this->selectedDay.($flow === 'retour' ? ' 17:00:00' : ' 08:00:00'),
            'departure_location' => ($flow === 'retour' ? 'Lieu compétition' : 'Sion, piscine'),
            'note'               => '',
        ];
        $this->record->update(['transport_plan' => $this->transportPlans]);
        $this->loadData();
        Notification::make()->title('Véhicule ajouté')->success()->send();
    }

    public function toggleLock($type, $index)
    {
        if ($type === 'vehicle') {
            $current = $this->transportPlans[$this->selectedDay][$index]['locked'] ?? false;
            $this->transportPlans[$this->selectedDay][$index]['locked'] = ! $current;
            $this->record->update(['transport_plan' => $this->transportPlans]);
        } elseif ($type === 'room') {
            $current = $this->stayPlans[$this->selectedDay][$index]['locked'] ?? false;
            $this->stayPlans[$this->selectedDay][$index]['locked'] = ! $current;
            $this->record->update(['stay_plan' => $this->stayPlans]);
        }
        $this->loadData();
    }

    public function updatePlanningMode($mode)
    {
        $this->planningMode = $mode;
        $settings = $this->record->settings ?? [];
        $settings['planning_mode'] = $mode;
        $this->record->update(['settings' => $settings]);
        $this->loadData();
        Notification::make()->title('Mode de planification mis à jour : '.$mode)->success()->send();
    }

    public function addManualParticipant($name, $role = 'athlete')
    {
        $participants = $this->record->participants_data ?? [];
        $id = 'manual_'.uniqid();
        $participants[] = [
            'id'                         => $id,
            'name'                       => $name,
            'role'                       => $role,
            'is_manual'                  => true,
            'hotel_override'             => true, // Par défaut forcé pour les manuels
            'first_competition_datetime' => null,
            'last_competition_datetime'  => null,
            'competition_days'           => [],
            'survey_response'            => [
                'responses' => [],
            ],
        ];
        $this->record->update(['participants_data' => $participants]);
        $this->loadData();
        Notification::make()->title('Participant ajouté')->success()->send();
    }

    public function toggleHotel($pId)
    {
        $participants = $this->record->participants_data ?? [];
        foreach ($participants as &$p) {
            if ((string) $p['id'] === (string) $pId) {
                $current = $p['hotel_override'] ?? null;
                if ($current === null) {
                    $p['hotel_override'] = true; // Forcer
                } elseif ($current === true) {
                    $p['hotel_override'] = false; // Bloquer
                } else {
                    $p['hotel_override'] = null; // Retour Auto
                }
                break;
            }
        }
        $this->record->update(['participants_data' => $participants]);
        $this->loadData();
    }

    public function autoDispatch()
    {
        // 1. Get all candidates for the selected day
        $participants = $this->record->participants_data ?? [];
        $candidatesAller = [];
        $candidatesRetour = [];

        foreach ($participants as $p) {
            $role = $p['role'] ?? 'athlete';
            $dayResp = $p['survey_response']['responses'][$this->selectedDay] ?? null;
            $hasSchedule = isset($p['competition_days'][$this->selectedDay]);

            $isIncluded = false;
            if ($this->planningMode === 'survey') {
                $isIncluded = ($dayResp['aller']['mode'] ?? '') === 'bus' || ($p['is_manual'] ?? false);
            } elseif ($this->planningMode === 'schedule') {
                $isIncluded = ($dayResp['aller']['mode'] ?? '') === 'bus' || $hasSchedule || ($p['is_manual'] ?? false);
            } else {
                $isIncluded = true;
            }

            if ($isIncluded && ($dayResp['aller']['mode'] ?? 'bus') === 'bus') {
                $candidatesAller[] = $p;
            }

            $isIncludedRetour = false;
            if ($this->planningMode === 'survey') {
                $isIncludedRetour = ($dayResp['retour']['mode'] ?? '') === 'bus' || ($p['is_manual'] ?? false);
            } elseif ($this->planningMode === 'schedule') {
                $isIncludedRetour = ($dayResp['retour']['mode'] ?? '') === 'bus' || $hasSchedule || ($p['is_manual'] ?? false);
            } else {
                $isIncludedRetour = true;
            }

            if ($isIncludedRetour && ($dayResp['retour']['mode'] ?? 'bus') === 'bus') {
                $candidatesRetour[] = $p;
            }
        }

        // 2. Identify Locked vs New Vehicles
        $transportPlan = $this->transportPlans[$this->selectedDay] ?? [];
        $vehicles = [];
        $assignedIds = [];

        foreach ($transportPlan as $v) {
            if ($v['locked'] ?? false) {
                $vehicles[] = $v;
                $assignedIds = array_merge($assignedIds, $v['passengers'] ?? []);
            }
        }

        // Filter candidates who are already assigned in locked vehicles
        $candidatesAller = array_filter($candidatesAller, fn ($c) => ! in_array($c['id'], $assignedIds));
        $candidatesRetour = array_filter($candidatesRetour, fn ($c) => ! in_array($c['id'], $assignedIds));

        // 3. Identification of Parent Cars from Survey (only if not already in locked vehicles)
        foreach ($participants as $p) {
            if (in_array($p['id'], $assignedIds)) {
                continue;
            }

            $dayResp = $p['survey_response']['responses'][$this->selectedDay] ?? null;
            if (! $dayResp) {
                continue;
            }

            $allerSeats = (int) ($dayResp['aller']['seats'] ?? 0);
            $retourSeats = (int) ($dayResp['retour']['seats'] ?? 0);

            if ($allerSeats > 0) {
                $vehicles[] = [
                    'id'                 => 'car_aller_'.$p['id'].'_'.$this->selectedDay,
                    'type'               => 'car',
                    'flow'               => 'aller',
                    'name'               => 'Voiture '.$p['name'].' (Aller)',
                    'capacity'           => $allerSeats,
                    'passengers'         => [$p['id']],
                    'driver'             => 'Parent '.$p['name'],
                    'departure_datetime' => $this->selectedDay.' 07:30:00',
                    'departure_location' => 'Sion, piscine',
                ];
                $candidatesAller = array_filter($candidatesAller, fn ($c) => $c['id'] !== $p['id']);
            }

            if ($retourSeats > 0) {
                $vehicles[] = [
                    'id'                 => 'car_retour_'.$p['id'].'_'.$this->selectedDay,
                    'type'               => 'car',
                    'flow'               => 'retour',
                    'name'               => 'Voiture '.$p['name'].' (Retour)',
                    'capacity'           => $retourSeats,
                    'passengers'         => [$p['id']],
                    'driver'             => 'Parent '.$p['name'],
                    'departure_datetime' => $this->selectedDay.' 17:30:00',
                    'departure_location' => 'Lieu compétition',
                ];
                $candidatesRetour = array_filter($candidatesRetour, fn ($c) => $c['id'] !== $p['id']);
            }
        }

        // 4. Add Buses if needed for remaining candidates
        $settings = $this->record->settings ?? [];
        $defaultBusCapacity = $settings['bus_capacity'] ?? 50;

        if (! empty($candidatesAller)) {
            $vehicles[] = [
                'id'                 => 'bus_aller_'.$this->selectedDay.'_'.uniqid(),
                'type'               => 'bus',
                'flow'               => 'aller',
                'name'               => Carbon::parse($this->selectedDay)->translatedFormat('D').' - Bus Aller',
                'capacity'           => $defaultBusCapacity,
                'passengers'         => [],
                'driver'             => 'Chauffeur Bus',
                'departure_datetime' => $this->selectedDay.' 07:30:00',
                'departure_location' => 'Sion, piscine',
            ];
        }
        if (! empty($candidatesRetour)) {
            $vehicles[] = [
                'id'                 => 'bus_retour_'.$this->selectedDay.'_'.uniqid(),
                'type'               => 'bus',
                'flow'               => 'retour',
                'name'               => Carbon::parse($this->selectedDay)->translatedFormat('D').' - Bus Retour',
                'capacity'           => $defaultBusCapacity,
                'passengers'         => [],
                'driver'             => 'Chauffeur Bus',
                'departure_datetime' => $this->selectedDay.' 17:30:00',
                'departure_location' => 'Lieu compétition',
            ];
        }

        // 5. Fill Vehicles
        foreach ($vehicles as &$v) {
            if ($v['locked'] ?? false) {
                continue;
            }

            $isRetour = ($v['flow'] === 'retour');
            $targetCandidates = $isRetour ? $candidatesRetour : $candidatesAller;
            if (empty($targetCandidates)) {
                continue;
            }

            $slots = $v['capacity'] - count($v['passengers']);
            while ($slots > 0 && ! empty($targetCandidates)) {
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

        // 6. Calculate Departure Times (only for non-locked)
        $dist = (float) ($settings['distance_km'] ?? 0);
        $prep = (int) ($settings['duration_prep_min'] ?? 90);
        $recup = (int) ($settings['duration_recup_min'] ?? 60);

        foreach ($vehicles as &$v) {
            if ($v['locked'] ?? false) {
                continue;
            }
            $isRetour = ($v['flow'] === 'retour');

            if ($isRetour) {
                // Return: Departure = Last competition + buffer (recup)
                $lastTime = null;
                foreach ($v['passengers'] as $pid) {
                    $p = $this->participantsMap[$pid] ?? null;
                    $pLastStr = null;
                    if ($p) {
                        if (isset($p['competition_days'][$this->selectedDay]['last'])) {
                            $pLastStr = $p['competition_days'][$this->selectedDay]['last'];
                        } elseif (isset($p['last_competition_datetime'])) {
                            // Fallback if day-specific not found but global matches today
                            if (str_starts_with($p['last_competition_datetime'], $this->selectedDay)) {
                                $pLastStr = $p['last_competition_datetime'];
                            }
                        }
                    }

                    if ($pLastStr) {
                        $dt = Carbon::parse($pLastStr);
                        if (! $lastTime || $dt->gt($lastTime)) {
                            $lastTime = $dt;
                        }
                    }
                }
                if ($lastTime) {
                    $v['departure_datetime'] = $lastTime->copy()->addMinutes($recup)->toDateTimeString();
                }
            } else {
                // Aller: Departure = First competition - (dist/speed + prep)
                $speed = (float) (($v['type'] === 'bus') ? ($settings['bus_speed'] ?? 80) : ($settings['car_speed'] ?? 100));
                $travelTimeMin = ($speed > 0) ? ($dist / $speed * 60) : 0;
                $totalOffset = $prep + $travelTimeMin;

                $firstTime = null;
                foreach ($v['passengers'] as $pid) {
                    $p = $this->participantsMap[$pid] ?? null;
                    $pFirstStr = null;
                    if ($p) {
                        if (isset($p['competition_days'][$this->selectedDay]['first'])) {
                            $pFirstStr = $p['competition_days'][$this->selectedDay]['first'];
                        } elseif (isset($p['first_competition_datetime'])) {
                            if (str_starts_with($p['first_competition_datetime'], $this->selectedDay)) {
                                $pFirstStr = $p['first_competition_datetime'];
                            }
                        }
                    }

                    if ($pFirstStr) {
                        $dt = Carbon::parse($pFirstStr);
                        if (! $firstTime || $dt->lt($firstTime)) {
                            $firstTime = $dt;
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
            'settings'       => $settings,
        ]);

        $this->loadData();
        Notification::make()->title('Calcul automatique terminé (Jour : '.$this->selectedDay.')')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('planning_mode')
                ->label(fn () => match ($this->planningMode) {
                    'survey'   => 'Mode : Sondage',
                    'schedule' => 'Mode : Horaire',
                    'all'      => 'Mode : Complet',
                    default    => 'Mode : Sondage',
                })
                ->icon('heroicon-o-funnel')
                ->color('info')
                ->action(fn (array $data) => $this->updatePlanningMode($data['planning_mode']))
                ->form([
                    \Filament\Forms\Components\Select::make('planning_mode')
                        ->label('Source de données')
                        ->options([
                            'survey'   => 'Sondage uniquement (Réponses explicites)',
                            'schedule' => 'Sondage + Horaires (Basé sur les épreuves)',
                            'all'      => 'Tous les participants (Toute la liste)',
                        ])
                        ->default($this->planningMode),
                ]),
            Action::make('add_manual')
                ->label('Ajouter Manuel')
                ->icon('heroicon-o-user-plus')
                ->color('gray')
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->label('Nom')
                        ->required()
                        ->placeholder('Ex: Pierre'),
                    \Filament\Forms\Components\Select::make('role')
                        ->label('Rôle')
                        ->required()
                        ->options([
                            'athlete' => 'Athlète',
                            'coach'   => 'Coach',
                        ])
                        ->default('athlete'),
                ])
                ->action(fn (array $data) => $this->addManualParticipant($data['name'], $data['role'])),
            Action::make('auto_dispatch')
                ->label(fn () => 'Calcul auto '.($this->selectedDay ? "($this->selectedDay)" : ''))
                ->tooltip('Génère un plan pour le jour sélectionné (écrase le plan actuel non verrouillé de ce jour)')
                ->requiresConfirmation()
                ->modalHeading('Réinitialiser le plan pour ce jour ?')
                ->modalDescription('Cela va supprimer les attributions actuelles des véhicules (SAUF ceux qui sont verrouillés) pour générer un nouveau plan basé sur le mode : '.($this->planningMode === 'survey' ? 'Sondage' : ($this->planningMode === 'schedule' ? 'Horaire' : 'Complet')).'.')
                ->action(fn () => $this->autoDispatch())
                ->color('danger')
                ->icon('heroicon-o-arrow-path'),
            Action::make('edit')
                ->label(fn () => 'Editer')
                ->url(fn (Model $record) => EventLogisticResource::getUrl('edit', ['record' => $record]))
                ->color('gray')
                ->icon('heroicon-o-pencil'),
        ];
    }

    public function getParticipantStartTime($pId)
    {
        $p = $this->participantsMap[$pId] ?? null;
        if (! $p) {
            return null;
        }

        $start = null;
        if (isset($p['competition_days'][$this->selectedDay]['first'])) {
            $start = $p['competition_days'][$this->selectedDay]['first'];
        } elseif (isset($p['first_competition_datetime'])) {
            $start = $p['first_competition_datetime'];
        }

        if (! $start) {
            return null;
        }

        $startDt = Carbon::parse($start);
        if ($startDt->toDateString() !== $this->selectedDay) {
            return null;
        }

        return $startDt->format('H:i');
    }

    public function getParticipantEndTime($pId)
    {
        $p = $this->participantsMap[$pId] ?? null;
        if (! $p) {
            return null;
        }

        $end = null;
        if (isset($p['competition_days'][$this->selectedDay]['last'])) {
            $end = $p['competition_days'][$this->selectedDay]['last'];
        } elseif (isset($p['last_competition_datetime'])) {
            $end = $p['last_competition_datetime'];
        }

        if (! $end) {
            return null;
        }

        $endDt = Carbon::parse($end);
        if ($endDt->toDateString() !== $this->selectedDay) {
            return null;
        }

        return $endDt->format('H:i');
    }

    public function getParticipantTimes($pId)
    {
        $p = $this->participantsMap[$pId] ?? null;
        if (! $p) {
            return null;
        }

        $start = $this->getParticipantStartTime($pId);
        $end = $this->getParticipantEndTime($pId) ?? '...';

        if (! $start) {
            return null;
        }

        return "{$start} - {$end}";
    }
}
