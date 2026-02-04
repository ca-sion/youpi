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

    public $transportPlan = [];
    public $stayPlan = [];
    public $unassignedTransport = [];
    public $unassignedStay = [];
    public $participantsMap = [];
    public $alerts = []; // [vehicleIndex => ['type' => 'msg']]
    public $globalAlerts = []; // ['msg' => '...']
    public $hotelNeededIds = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->loadData();
    }

    public function loadData()
    {
        $this->transportPlan = $this->record->transport_plan ?? [];
        $this->stayPlan = $this->record->stay_plan ?? [];
        $participants = $this->record->participants_data ?? [];
        $this->participantsMap = collect($participants)->keyBy('id')->toArray();
        $this->alerts = [];
        $this->globalAlerts = [];
        $this->hotelNeededIds = collect($participants)->filter(fn($p) => $p['survey_response']['hotel_needed'] ?? false)->pluck('id')->toArray();

        // Ensure default settings
        $settings = $this->record->settings ?? [];
        if (!isset($settings['default_bus_capacity'])) {
            $settings['default_bus_capacity'] = 50;
            $this->record->update(['settings' => $settings]);
        }
        
        // Ensure at least a Bus if empty?
        if (empty($this->transportPlan)) {
            // Check settings if bus exists?
        }

        // Calculate unassigned & Alerts
        $assignedIds = [];
        
        $settings = $this->record->settings ?? [];
        $prep = $settings['temps_prep_min'] ?? 90;
        $dist = $settings['distance_km'] ?? 0;

        foreach ($this->transportPlan as $index => $vehicle) {
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
                $depTime = Carbon::parse($vehicle['departure_datetime']);
                $speed = ($vehicle['type'] === 'bus') ? ($settings['vitesse_bus'] ?? 100) : ($settings['vitesse_voiture'] ?? 120);
                $travelMin = ($speed > 0) ? ($dist / $speed * 60) : 0;
                
                $arrivalEst = $depTime->copy()->addMinutes($travelMin); // Arrival at destination
                
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
            }
        }

        // Transport Unassigned
        $assignedTransportIds = [];
        foreach ($this->transportPlan as $v) {
            $assignedTransportIds = array_merge($assignedTransportIds, $v['passengers'] ?? []);
        }
        $this->unassignedTransport = collect($participants)->reject(fn($p) => in_array($p['id'], $assignedTransportIds))->toArray();

        // Stay Unassigned
        $assignedStayIds = [];
        foreach ($this->stayPlan as $r) {
            $assignedStayIds = array_merge($assignedStayIds, $r['occupant_ids'] ?? []);
        }
        $this->unassignedStay = collect($participants)
            ->filter(fn($p) => $p['survey_response']['hotel_needed'] ?? false) // Only those who need hotel?
            ->reject(fn($p) => in_array($p['id'], $assignedStayIds))
            ->toArray();

        foreach ($participants as $p) {
            // 3. Hotel Alert
            $surveyHotel = $p['survey_response']['hotel_needed'] ?? false;
            $inHotel = false;
            $stayPlan = $this->record->stay_plan ?? [];
            foreach ($stayPlan as $room) {
                if (in_array($p['id'], $room['occupant_ids'] ?? [])) {
                    $inHotel = true; 
                    break;
                }
            }

            if ($surveyHotel && !$inHotel) {
                 $this->globalAlerts[] = ['type' => 'danger', 'msg' => "Dodo manquant: {$p['name']}"];
            }
        }
    }

    public function saveAllPlans($transport, $stay)
    {
        $this->transportPlan = $transport;
        $this->stayPlan = $stay;
        
        $this->record->update([
            'transport_plan' => $this->transportPlan,
            'stay_plan' => $this->stayPlan,
        ]);
        
        Notification::make()->title('Logistique enregistrée avec succès')->success()->send();
        $this->loadData();
    }
    
    public function removeVehicle($index)
    {
        if (isset($this->transportPlan[$index])) {
            array_splice($this->transportPlan, $index, 1);
            $this->record->update(['transport_plan' => $this->transportPlan]);
            $this->loadData();
            Notification::make()->title('Véhicule supprimé')->success()->send();
        }
    }

    public function removeRoom($index)
    {
        if (isset($this->stayPlan[$index])) {
            array_splice($this->stayPlan, $index, 1);
            $this->record->update(['stay_plan' => $this->stayPlan]);
            $this->loadData();
            Notification::make()->title('Chambre supprimée')->success()->send();
        }
    }

    public function addRoom()
    {
        $this->stayPlan[] = [
            'id' => 'room_' . uniqid(),
            'name' => 'Chambre ' . (count($this->stayPlan) + 1),
            'occupant_ids' => [],
            'note' => '',
        ];
        $this->record->update(['stay_plan' => $this->stayPlan]);
        $this->loadData();
    }

    public function addVehicle($type = 'car')
    {
        $settings = $this->record->settings ?? [];
        $defaultBusCapacity = $settings['default_bus_capacity'] ?? 50;

        $this->transportPlan[] = [
            'id' => 'manual_' . uniqid(),
            'type' => $type,
            'name' => ($type === 'bus' ? 'Nouveau Bus' : 'Nouvelle Voiture'),
            'capacity' => ($type === 'bus' ? $defaultBusCapacity : 4),
            'passengers' => [],
            'driver' => 'À définir',
            'departure_datetime' => null,
            'departure_location' => 'Stade de Sion',
            'note' => '',
        ];
        $this->record->update(['transport_plan' => $this->transportPlan]);
        $this->loadData();
        Notification::make()->title('Véhicule ajouté')->success()->send();
    }
    
    public function autoDispatch()
    {
        // 1. Get all candidates (exclude personal transport)
        $participants = $this->record->participants_data ?? [];
        $candidates = [];
        foreach ($participants as $p) {
            $mode = $p['survey_response']['transport_mode'] ?? null;
            if ($mode === 'perso' || $mode === 'train') continue; 
            $candidates[] = $p;
        }

        // 2. Identify Vehicles
        $vehicles = [];
        // Add a Bus Club by default
        $vehicles[] = [
            'id' => 'bus_1',
            'type' => 'bus',
            'name' => 'Bus Club',
            'capacity' => 50,
            'passengers' => [],
            'driver' => 'Chauffeur Bus'
        ];

        // Parent Cars from Survey
        foreach ($participants as $p) {
            $seats = $p['survey_response']['voiture_seats'] ?? 0;
            if ($seats > 0) {
                 $vehicles[] = [
                     'id' => 'car_' . $p['id'],
                     'type' => 'car',
                     'name' => 'Voiture ' . $p['name'],
                     'capacity' => (int)$seats,
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
            $slots = $v['capacity'];
            while ($slots > 0 && !empty($candidates)) {
                $p = array_shift($candidates);
                 $v['passengers'][] = $p['id'];
                 $slots--;
            }
        }
        unset($v);

        // 4. Calculate Departure Times
        $settings = $this->record->settings ?? [];
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
                    if (!$firstTime || $dt->lt($firstTime)) {
                        $firstTime = $dt;
                    }
                }
            }
            
            if ($firstTime) {
                $v['departure_datetime'] = $firstTime->copy()->subMinutes($totalOffset)->toDateTimeString();
                $v['departure_location'] = 'Stade de Sion';
            } else {
                $v['departure_datetime'] = null;
            }
        }

        $this->transportPlan = $vehicles;
        $this->record->update(['transport_plan' => $this->transportPlan]);
        $this->loadData();
        Notification::make()->title('Calcul automatique terminé (Réinitialisation effectuée)')->success()->send();
    }
    
    protected function getHeaderActions(): array
    {
        return [
             Action::make('auto_dispatch')
                ->label('Auto-Dispatch (Reset)')
                ->tooltip('Génère un plan basé sur les sondages (écrase le plan actuel)')
                ->requiresConfirmation()
                ->modalHeading('Réinitialiser le plan ?')
                ->modalDescription('Cela va supprimer le plan actuel pour en générer un nouveau à partir des réponses au sondage.')
                ->action(fn() => $this->autoDispatch())
                ->color('danger')
                ->icon('heroicon-o-arrow-path'),
             
             Action::make('add_car')
                ->label('Ajouter Voiture')
                ->icon('heroicon-o-plus')
                ->action(fn() => $this->addVehicle('car'))
                ->color('gray'),

             Action::make('add_bus')
                ->label('Ajouter Bus')
                ->icon('heroicon-o-plus')
                ->action(fn() => $this->addVehicle('bus'))
                ->color('gray'),

            Action::make('add_room')
                ->label('Ajouter Chambre')
                ->icon('heroicon-o-home')
                ->action(fn() => $this->addRoom())
                ->color('gray'),

            Action::make('editVehicle')
                ->visible(false) // Hide from header but keep available for mountAction
                ->modalWidth('lg')
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')->label('Nom du véhicule')->required(),
                    \Filament\Forms\Components\TextInput::make('driver')->label('Chauffeur'),
                    \Filament\Forms\Components\TextInput::make('capacity')->label('Capacité')->numeric()->required(),
                    \Filament\Forms\Components\DateTimePicker::make('departure_datetime')->label('Date/Heure de départ'),
                    \Filament\Forms\Components\TextInput::make('departure_location')->label('Lieu de départ'),
                    \Filament\Forms\Components\Textarea::make('note')->label('Notes'),
                ])
                ->fillForm(function (array $arguments) {
                    $index = $arguments['index'] ?? null;
                    if ($index !== null && isset($this->transportPlan[$index])) {
                        return $this->transportPlan[$index];
                    }
                    return [];
                })
                ->action(function (array $data, array $arguments) {
                    $index = $arguments['index'];
                    if (isset($this->transportPlan[$index])) {
                        $this->transportPlan[$index] = array_merge($this->transportPlan[$index], $data);
                        $this->record->update(['transport_plan' => $this->transportPlan]);
                        $this->loadData();
                        Notification::make()->title('Véhicule mis à jour')->success()->send();
                    }
                }),

            Action::make('editRoom')
                ->visible(false) // Hide from header
                ->modalWidth('lg')
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')->label('Nom de la chambre')->required(),
                    \Filament\Forms\Components\Textarea::make('note')->label('Notes / Détails'),
                ])
                ->fillForm(function (array $arguments) {
                    $index = $arguments['index'] ?? null;
                    if ($index !== null && isset($this->stayPlan[$index])) {
                        return $this->stayPlan[$index];
                    }
                    return [];
                })
                ->action(function (array $data, array $arguments) {
                    $index = $arguments['index'];
                    if (isset($this->stayPlan[$index])) {
                        $this->stayPlan[$index] = array_merge($this->stayPlan[$index], $data);
                        $this->record->update(['stay_plan' => $this->stayPlan]);
                        $this->loadData();
                        Notification::make()->title('Chambre mise à jour')->success()->send();
                    }
                }),
        ];
    }
}
