<?php

namespace App\Filament\Resources\EventLogisticResource\Pages;

use App\Filament\Resources\EventLogisticResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class EditEventLogistic extends EditRecord
{
    protected static string $resource = EventLogisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manage_transport')
                ->label('Gérer le Transport')
                ->icon('heroicon-o-truck')
                ->url(fn ($record) => EventLogisticResource::getUrl('transport', ['record' => $record])),

            Actions\ActionGroup::make([
                Actions\Action::make('public_survey')
                    ->label('Sondage Public')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->url(fn ($record) => route('logistics.survey', $record))
                    ->openUrlInNewTab(),
                Actions\Action::make('public_view')
                    ->label('Vue Résumé')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('logistics.show', $record))
                    ->openUrlInNewTab(),
            ])
            ->label('Liens Publics')
            ->icon('heroicon-m-link')
            ->color('info'),

            Actions\ActionGroup::make([
                Actions\Action::make('parse_inscriptions')
                    ->label('Analyser Inscriptions')
                    ->icon('heroicon-o-cpu-chip')
                    ->action(function () {
                        $record = $this->getRecord();
                        $raw = $record->athletes_inscriptions_raw;
                        if (!$raw) {
                             \Filament\Notifications\Notification::make()->title('Aucune donnée brute trouvée')->warning()->send();
                             return;
                        }
                        $lines = explode("\n", $raw);
                        $parsed = [];
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if (empty($line)) continue;
                            
                            // Format: Name (Category) : Disciplines  OR  Name : Disciplines
                            if (preg_match('/^(.+?)(?:\s*\((.+?)\))?\s*:\s*(.+)$/', $line, $matches)) {
                                $name = trim($matches[1]);
                                $category = !empty($matches[2]) ? trim($matches[2]) : null;
                                $disciplinesStr = $matches[3];
                                $disciplines = array_map('trim', explode(',', $disciplinesStr));
                                $parsed[] = ['name' => $name, 'category' => $category, 'disciplines' => $disciplines];
                            } else {
                                // Fallback
                                $parts = explode(':', $line);
                                if (count($parts) >= 2) {
                                    $name = trim($parts[0]);
                                    $disciplines = array_map('trim', explode(',', $parts[1]));
                                    $parsed[] = ['name' => $name, 'category' => null, 'disciplines' => $disciplines];
                                }
                            }
                        }
                        $record->update(['inscriptions_data' => $parsed]);
                        \Filament\Notifications\Notification::make()->title('Inscriptions analysées avec succès')->success()->send();
                        $this->fillForm();
                    }),
                Actions\Action::make('magic_match')
                    ->label('Générer Planning (Magic)')
                    ->icon('heroicon-o-sparkles')
                    ->action(function () {
                        $record = $this->getRecord();
                        $inscriptions = $record->inscriptions_data ?? [];
                        $schedule = $record->raw_schedule ?? [];
                        $settings = $record->settings ?? [];
                        $startDateStr = $settings['start_date'] ?? null;

                        if (!$startDateStr) {
                            Notification::make()->title('Veuillez définir une date de début dans les paramètres')->danger()->send();
                            return;
                        }
                        $startDate = Carbon::parse($startDateStr);
                        $daysMap = [
                            'lundi' => 1, 'mardi' => 2, 'mercredi' => 3, 'jeudi' => 4,
                            'vendredi' => 5, 'samedi' => 6, 'dimanche' => 7
                        ];

                        $participants = [];
                        foreach ($inscriptions as $athlete) {
                            $athleteName = $athlete['name'] ?? 'Unknown';
                            $athleteCat = $athlete['category'] ?? null;
                            $disciplines = $athlete['disciplines'] ?? [];

                            $details = ['name' => $athleteName, 'id' => Str::uuid()->toString()];
                            $events = [];

                            foreach ($disciplines as $discipline) {
                                foreach ($schedule as $event) {
                                    // Simple substring match for discipline
                                    if (isset($event['epreuve']) && stripos($event['epreuve'], $discipline) !== false) {
                                        // Check Category if available
                                        if ($athleteCat && isset($event['cat'])) {
                                            if (stripos($event['cat'], $athleteCat) === false) continue;
                                        }

                                        try {
                                            if (!isset($event['heure']) || !isset($event['jour'])) continue;
                                            
                                            $time = Carbon::parse($event['heure']);
                                            $startDayIndex = $startDate->dayOfWeekIso;
                                            $eventDayName = strtolower($event['jour']);
                                            $eventDayIndex = $daysMap[$eventDayName] ?? null;

                                            if ($eventDayIndex) {
                                                $diff = $eventDayIndex - $startDayIndex;
                                                if ($diff < 0) $diff += 7; 
                                                $eventDate = $startDate->copy()->addDays($diff);
                                            } else {
                                                $eventDate = $startDate->copy();
                                            }
                                            
                                            $eventDt = $eventDate->setTime($time->hour, $time->minute);
                                            $events[] = $eventDt;
                                        } catch (\Exception $e) {}
                                    }
                                }
                            }

                            if (count($events) > 0) {
                                sort($events);
                                $details['first_competition_datetime'] = $events[0]->toDateTimeString();
                                $details['last_competition_datetime'] = end($events)->toDateTimeString();
                            } else {
                                $details['note'] = 'Aucune épreuve trouvée';
                            }
                            
                            // Preserve existing ID/Survey
                            $existing = collect($record->participants_data ?? [])->firstWhere('name', $athleteName);
                            if ($existing) {
                                $details['id'] = $existing['id'] ?? $details['id'];
                                $details['survey_response'] = $existing['survey_response'] ?? null;
                            }

                            $participants[] = $details;
                        }

                        $record->update(['participants_data' => $participants]);
                        Notification::make()->title('Calcul du planning terminé')->success()->send();
                        $this->fillForm();
                    }),
            ])
            ->label('Outils & Actions')
            ->icon('heroicon-o-wrench-screwdriver')
            ->color('gray'),

            Actions\DeleteAction::make(),
        ];
    }
}
