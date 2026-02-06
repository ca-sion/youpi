<?php

namespace App\Filament\Resources\EventLogisticResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\EventLogisticResource;

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
                        $raw = $record->inscriptions_raw;
                        if (! $raw) {
                            \Filament\Notifications\Notification::make()->title('Aucune donnée brute trouvée')->warning()->send();

                            return;
                        }
                        $lines = explode("\n", $raw);
                        $parsed = [];
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if (empty($line)) {
                                continue;
                            }

                            // Format: Name (Category) : Disciplines  OR  Name : Disciplines
                            if (preg_match('/^(.+?)(?:\s*\((.+?)\))?\s*:\s*(.+)$/', $line, $matches)) {
                                $name = trim($matches[1]);
                                $category = ! empty($matches[2]) ? trim($matches[2]) : null;
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
                    ->label('Générer planning')
                    ->icon('heroicon-o-sparkles')
                    ->action(function () {
                        $record = $this->getRecord();
                        $inscriptions = $record->inscriptions_data ?? [];
                        $schedule = $record->schedule_raw ?? [];
                        if (is_string($schedule)) {
                            $schedule = json_decode($schedule, true) ?? [];
                        }

                        $settings = $record->settings ?? [];
                        $startDateStr = $settings['start_date'] ?? null;

                        if (! $startDateStr) {
                            Notification::make()->title('Veuillez définir une date de début dans les paramètres')->danger()->send();

                            return;
                        }
                        $startDate = Carbon::parse($startDateStr);
                        $daysMap = [
                            'lundi'    => 1, 'mardi' => 2, 'mercredi' => 3, 'jeudi' => 4,
                            'vendredi' => 5, 'samedi' => 6, 'dimanche' => 7,
                        ];

                        $existingParticipants = collect($record->participants_data ?? []);
                        $processedIds = [];
                        $newParticipants = [];
                        foreach ($inscriptions as $athlete) {
                            $athleteName = $athlete['name'] ?? 'Unknown';
                            $athleteCat = strtoupper($athlete['category'] ?? '');
                            $disciplines = $athlete['disciplines'] ?? [];

                            // Normalize athlete gender/category
                            $athleteGender = null;
                            if (str_ends_with($athleteCat, 'W') || str_ends_with($athleteCat, 'F')) {
                                $athleteGender = 'W';
                            } elseif (str_ends_with($athleteCat, 'M')) {
                                $athleteGender = 'M';
                            } elseif ($athleteCat === 'MAN' || $athleteCat === 'MEN') {
                                $athleteGender = 'M';
                            } elseif ($athleteCat === 'WOMAN' || $athleteCat === 'WOMEN') {
                                $athleteGender = 'W';
                            }

                            $details = ['name' => $athleteName, 'id' => Str::uuid()->toString()];
                            $events = [];

                            foreach ($disciplines as $discipline) {
                                // Extract day if present (Samedi/Dimanche)
                                $prefDay = null;
                                foreach ($daysMap as $dayName => $dayIdx) {
                                    if (stripos($discipline, $dayName) !== false) {
                                        $prefDay = $dayName;
                                        break;
                                    }
                                }

                                // Rounds regex
                                $roundsRegex = '/\b(vl|z|séries|df|f|finale|demi-finale)\b/i';

                                // Base discipline for matching (lowercase, no parentheses, no rounds)
                                $cleanDiscipline = strtolower(trim(preg_replace('/\s*\(.*?\)\s*/', ' ', $discipline)));
                                $baseDiscipline = trim(preg_replace($roundsRegex, '', $cleanDiscipline));

                                // Also strip day names from baseDiscipline if we found a prefDay
                                if ($prefDay) {
                                    $baseDiscipline = trim(str_ireplace($prefDay, '', $baseDiscipline));
                                }

                                if (empty($baseDiscipline)) {
                                    continue;
                                }

                                foreach ($schedule as $event) {
                                    $eventDisciplineRaw = $event['discipline'] ?? '';
                                    $eventDiscipline = strtolower($eventDisciplineRaw);
                                    $eventCat = strtoupper($event['cat'] ?? '');
                                    $eventDayName = strtolower($event['jour'] ?? $event['day'] ?? '');

                                    // 0. Day matching (Day Progression Rule)
                                    if ($prefDay) {
                                        $atDayIdx = $daysMap[strtolower($prefDay)] ?? null;
                                        $evDayIdx = $daysMap[strtolower($eventDayName)] ?? null;

                                        if ($atDayIdx !== null && $evDayIdx !== null) {
                                            if ($atDayIdx > $evDayIdx) {
                                                // Registered for Sunday, event is Saturday. Skip.
                                                continue;
                                            }
                                            if ($atDayIdx < $evDayIdx) {
                                                // Registered for Saturday, event is Sunday. Only allow finales.
                                                $isFinale = preg_match('/\b(f|finale|df|demi-finale)\b/i', $eventDiscipline);
                                                if (! $isFinale) {
                                                    continue;
                                                }
                                            }
                                        }
                                    }

                                    // 1. Discipline matching
                                    $disciplineMatch = false;

                                    // A. Base matching (strip rounds and parentheses)
                                    $cleanEvent = strtolower(trim(preg_replace('/\s*\(.*?\)\s*/', ' ', $eventDiscipline)));
                                    $baseEvent = trim(preg_replace($roundsRegex, '', $cleanEvent));

                                    if ($baseDiscipline === $baseEvent) {
                                        $disciplineMatch = true;
                                    } elseif (stripos($eventDiscipline, $cleanDiscipline) !== false) {
                                        // Still allow substring for special cases, but Hurdle check below will filter 60m vs 60m haies
                                        $disciplineMatch = true;
                                    }

                                    if (! $disciplineMatch) {
                                        continue;
                                    }

                                    // B. Parameters & Suffixes matching (e.g. W1, W2, (4.50))
                                    preg_match_all('/[\d\.]+/', $discipline, $atMatches);
                                    preg_match_all('/[\d\.]+/', $eventDisciplineRaw, $evMatches);

                                    $atNums = array_filter($atMatches[0], fn ($n) => ! empty($n) && ! in_array($n, ['60', '100', '200', '400', '800', '1000', '1500']));
                                    $evNums = array_filter($evMatches[0], fn ($n) => ! empty($n) && ! in_array($n, ['60', '100', '200', '400', '800', '1000', '1500']));

                                    if (! empty($atNums)) {
                                        foreach ($atNums as $num) {
                                            if (! in_array($num, $evNums)) {
                                                $disciplineMatch = false;
                                                break;
                                            }
                                        }
                                    }
                                    if ($disciplineMatch && ! empty($evNums) && empty($atNums)) {
                                        if (preg_match('/\b(w\d)\b/i', $eventDisciplineRaw) || preg_match('/\(([\d\.]+)\)/', $eventDisciplineRaw)) {
                                            $disciplineMatch = false;
                                        }
                                    }

                                    if (! $disciplineMatch) {
                                        continue;
                                    }

                                    // 2. Hurdle check (Distinction between flat and hurdles)
                                    $hurdleKeywords = ['haies', 'hurdles', 'h '];
                                    $athleteIsHurdles = false;
                                    foreach ($hurdleKeywords as $kw) {
                                        if (stripos($discipline, $kw) !== false) {
                                            $athleteIsHurdles = true;
                                            break;
                                        }
                                    }
                                    if (! $athleteIsHurdles && preg_match('/[0-9]mh/i', $discipline)) {
                                        $athleteIsHurdles = true;
                                    }
                                    if (! $athleteIsHurdles && preg_match('/\bH\b/', $discipline)) {
                                        $athleteIsHurdles = true;
                                    }

                                    $eventIsHurdles = false;
                                    foreach ($hurdleKeywords as $kw) {
                                        if (stripos($eventDiscipline, $kw) !== false) {
                                            $eventIsHurdles = true;
                                            break;
                                        }
                                    }
                                    if (! $eventIsHurdles && preg_match('/[0-9]mh/i', $eventDiscipline)) {
                                        $eventIsHurdles = true;
                                    }
                                    if (! $eventIsHurdles && preg_match('/\bH\b/', $eventDiscipline)) {
                                        $eventIsHurdles = true;
                                    }

                                    if ($athleteIsHurdles !== $eventIsHurdles) {
                                        continue;
                                    }

                                    // 3. Category matching (Specificity Rule)
                                    $categoryMatch = false;
                                    if ($athleteCat === $eventCat) {
                                        $categoryMatch = true;
                                    } elseif ($athleteGender && ($eventCat === $athleteGender)) {
                                        // U16M matches M. But check if a better U16M match exists in the schedule for this discipline.
                                        $betterMatchExists = collect($schedule)->contains(function ($item) use ($athleteCat, $baseEvent, $eventDayName, $roundsRegex) {
                                            if (strtoupper($item['cat'] ?? '') !== $athleteCat) {
                                                return false;
                                            }
                                            if (($item['jour'] ?? $item['day'] ?? '') !== $eventDayName) {
                                                return false;
                                            }
                                            $cleanItem = strtolower(trim(preg_replace('/\s*\(.*?\)\s*/', ' ', $item['discipline'] ?? '')));
                                            $baseItem = trim(preg_replace($roundsRegex, '', $cleanItem));

                                            return $baseItem === $baseEvent;
                                        });

                                        if (! $betterMatchExists) {
                                            $categoryMatch = true;
                                        }
                                    } elseif ($eventCat === 'M' && (str_ends_with($athleteCat, 'M'))) {
                                        $categoryMatch = true;
                                    } elseif ($eventCat === 'W' && (str_ends_with($athleteCat, 'W') || str_ends_with($athleteCat, 'F'))) {
                                        $categoryMatch = true;
                                    }

                                    if (! $categoryMatch) {
                                        continue;
                                    }

                                    // 4. Robustness for Heptathlon/Pentathlon (Check composite)
                                    $eventIsComposite = (stripos($eventDiscipline, 'heptathlon') !== false || stripos($eventDiscipline, 'pentathlon') !== false);
                                    $athleteIsComposite = (stripos($discipline, 'heptathlon') !== false || stripos($discipline, 'pentathlon') !== false);
                                    if ($eventIsComposite !== $athleteIsComposite) {
                                        continue;
                                    }

                                    try {
                                        $dayValue = $event['jour'] ?? $event['day'] ?? null;
                                        if (! $dayValue || ! isset($event['time'])) {
                                            continue;
                                        }

                                        $time = Carbon::parse($event['time']);
                                        $startDayIndex = $startDate->dayOfWeekIso;
                                        $eventDayName = strtolower($dayValue);
                                        $eventDayIndex = $daysMap[$eventDayName] ?? null;

                                        if ($eventDayIndex) {
                                            $diff = $eventDayIndex - $startDayIndex;
                                            if ($diff < 0) {
                                                $diff += 7;
                                            }
                                            $eventDate = $startDate->copy()->addDays($diff);
                                        } else {
                                            $eventDate = $startDate->copy();
                                        }

                                        $eventDt = $eventDate->setTime($time->hour, $time->minute);
                                        $events[] = [
                                            'datetime'   => $eventDt,
                                            'discipline' => $eventDisciplineRaw,
                                        ];
                                    } catch (\Exception $e) {
                                    }
                                }
                            }

                            if (count($events) > 0) {
                                // Sort by datetime
                                usort($events, fn ($a, $b) => $a['datetime'] <=> $b['datetime']);

                                $details['first_competition_datetime'] = $events[0]['datetime']->toDateTimeString();
                                $details['last_competition_datetime'] = end($events)['datetime']->toDateTimeString();

                                // Group by day for multi-day display
                                $days = [];
                                foreach ($events as $ev) {
                                    $date = $ev['datetime']->toDateString();
                                    if (! isset($days[$date])) {
                                        $days[$date] = [
                                            'first'       => $ev['datetime']->toDateTimeString(),
                                            'last'        => $ev['datetime']->toDateTimeString(),
                                            'disciplines' => [$ev['discipline']],
                                        ];
                                    } else {
                                        $days[$date]['last'] = $ev['datetime']->toDateTimeString();
                                        if (! in_array($ev['discipline'], $days[$date]['disciplines'])) {
                                            $days[$date]['disciplines'][] = $ev['discipline'];
                                        }
                                    }
                                }
                                $details['competition_days'] = $days;
                            } else {
                                $details['first_competition_datetime'] = null;
                                $details['last_competition_datetime'] = null;
                                $details['competition_days'] = [];
                                $details['note'] = 'Aucune épreuve trouvée';
                            }

                            // Preserve existing ID/Survey (Robust Matching)
                            $normalize = function ($n) {
                                $n = mb_strtolower(trim($n), 'UTF-8');
                                $n = preg_replace('/^\[e\]\s+/', '', $n); // Strip coach prefix for matching
                                $n = preg_replace('/\s+/', ' ', $n);

                                return $n;
                            };
                            $normAthleteName = $normalize($athleteName);

                            $existing = $existingParticipants->first(function ($p) use ($normalize, $normAthleteName) {
                                return $normalize($p['name'] ?? '') === $normAthleteName;
                            });

                            if ($existing) {
                                $details['id'] = $existing['id'] ?? $details['id'];
                                $details['survey_response'] = $existing['survey_response'] ?? null;
                                $details['role'] = $existing['role'] ?? 'athlete';
                                // Keep the existing name if it was manually added/modified to preserve prefixes or custom formatting
                                if (str_starts_with($existing['name'] ?? '', '[E]') || ! empty($existing['survey_response']['filled_at'])) {
                                    $details['name'] = $existing['name'];
                                }
                                $processedIds[] = $details['id'];
                            }

                            $newParticipants[] = $details;
                        }

                        // Preserve manual additions (e.g. coaches, late survey additions)
                        $manualAdditions = $existingParticipants->whereNotIn('id', $processedIds);
                        $finalParticipants = array_merge($newParticipants, $manualAdditions->toArray());

                        $record->update(['participants_data' => $finalParticipants]);
                        Notification::make()->title('Calcul du planning terminé')->success()->send();
                        $this->fillForm();
                    }),
            ])
                ->label('Outils & Actions')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('gray'),

            Actions\Action::make('prepare_document')
                ->label('Préparer Document Voyage')
                ->icon('heroicon-o-document-text')
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->getRecord();
                    $document = $record->document;

                    if (! $document) {
                        $document = \App\Models\Document::create([
                            'name'         => 'Document Voyage - '.$record->name,
                            'type'         => \App\Enums\DocumentType::TRAVEL,
                            'status'       => \App\Enums\DocumentStatus::VALIDATED,
                            'published_on' => now(),
                        ]);
                        $record->update(['document_id' => $document->id]);
                    }

                    $settings = $record->settings ?? [];
                    $startDateStr = $settings['start_date'] ?? null;
                    if (! $startDateStr) {
                        Notification::make()->title('Date de début manquante')->danger()->send();

                        return;
                    }
                    $startDate = Carbon::parse($startDateStr);
                    $daysCount = (int) ($settings['days_count'] ?? 2);
                    $participants = collect($record->participants_data ?? []);

                    $travelData = [
                        'data' => [
                            'modification_deadline'              => null,
                            'modification_deadline_phone'        => null,
                            'location'                           => $record->name,
                            'date'                               => $startDateStr,
                            'departures'                         => [],
                            'arrivals'                           => [],
                            'nights'                             => [],
                            'accomodation'                       => '',
                            'competition'                        => $record->name,
                            'competition_informations_important' => '',
                            'competition_informations'           => '',
                            'competition_schedules'              => '',
                        ],
                    ];

                    $transportPlan = $record->transport_plan ?? [];
                    $stayPlan = $record->stay_plan ?? [];

                    // 1. Map Transport Plan
                    foreach ($transportPlan as $day => $vehicles) {
                        foreach ($vehicles as $v) {
                            $entry = [
                                'day_hour'         => Carbon::parse($v['departure_datetime'] ?? $day)->translatedFormat('D d.m H:i'),
                                'location'         => $v['departure_location'] ?? '',
                                'means'            => ($v['type'] === 'bus' ? 'Bus' : 'Voiture'),
                                'driver'           => $v['driver'] ?? '',
                                'travelers'        => $participants->whereIn('id', $v['passengers'] ?? [])->pluck('name')->implode(', '),
                                'travelers_number' => count($v['passengers'] ?? []),
                            ];

                            if (($v['flow'] ?? 'aller') === 'retour') {
                                $travelData['data']['arrivals'][] = $entry;
                            } else {
                                $travelData['data']['departures'][] = $entry;
                            }
                        }
                    }

                    // 2. Map Independents
                    for ($i = 0; $i < $daysCount; $i++) {
                        $date = $startDate->copy()->addDays($i)->toDateString();
                        $dateLabel = $startDate->copy()->addDays($i)->translatedFormat('D d.m');

                        $assignedAllerIds = [];
                        $assignedRetourIds = [];
                        foreach ($transportPlan as $d => $vList) {
                            if ($d === $date) {
                                foreach ($vList as $v) {
                                    if (($v['flow'] ?? 'aller') === 'retour') {
                                        $assignedRetourIds = array_merge($assignedRetourIds, $v['passengers'] ?? []);
                                    } else {
                                        $assignedAllerIds = array_merge($assignedAllerIds, $v['passengers'] ?? []);
                                    }
                                }
                            }
                        }

                        $indepAller = $participants->filter(function ($p) use ($date, $assignedAllerIds) {
                            $mode = $p['survey_response']['responses'][$date]['aller']['mode'] ?? '';

                            return in_array($mode, ['train', 'car', 'on_site']) && ! in_array($p['id'], $assignedAllerIds);
                        });

                        if ($indepAller->count() > 0) {
                            $travelData['data']['departures'][] = [
                                'day_hour'         => $dateLabel,
                                'location'         => 'Individuel',
                                'means'            => 'Par ses propres moyens',
                                'driver'           => '-',
                                'travelers'        => $indepAller->pluck('name')->implode(', '),
                                'travelers_number' => $indepAller->count(),
                            ];
                        }

                        $indepRetour = $participants->filter(function ($p) use ($date, $assignedRetourIds) {
                            $mode = $p['survey_response']['responses'][$date]['retour']['mode'] ?? '';

                            return in_array($mode, ['train', 'car', 'on_site']) && ! in_array($p['id'], $assignedRetourIds);
                        });

                        if ($indepRetour->count() > 0) {
                            $travelData['data']['arrivals'][] = [
                                'day_hour'         => $dateLabel,
                                'location'         => 'Individuel',
                                'means'            => 'Par ses propres moyens',
                                'driver'           => '-',
                                'travelers'        => $indepRetour->pluck('name')->implode(', '),
                                'travelers_number' => $indepRetour->count(),
                            ];
                        }
                    }

                    // 3. Map Stay Plan (Nights)
                    foreach ($stayPlan as $day => $rooms) {
                        foreach ($rooms as $r) {
                            $travelData['data']['nights'][] = [
                                'day'       => Carbon::parse($day)->translatedFormat('D d.m'),
                                'travelers' => $participants->whereIn('id', $r['occupant_ids'] ?? [])->pluck('name')->implode(', '),
                            ];
                        }
                    }

                    // 4. Map Schedules
                    $schedules = $participants->map(function ($p) {
                        if (! isset($p['first_competition_datetime'])) {
                            return null;
                        }
                        $first = Carbon::parse($p['first_competition_datetime']);
                        $last = isset($p['last_competition_datetime']) ? Carbon::parse($p['last_competition_datetime']) : null;

                        $label = $p['name'].' : ('.$first->translatedFormat('D').') '.$first->format('H:i');
                        if ($last) {
                            $label .= ' - '.$last->format('H:i');
                        }

                        return $label;
                    })->filter()->implode("\n");

                    $travelData['data']['competition_schedules'] = $schedules;

                    $document->update(['travel_data' => $travelData]);

                    Notification::make()->title('Document préparé avec succès')->success()->send();
                    $this->fillForm();
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
