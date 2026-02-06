@php
    $distance = $settings['distance_km'] ?? 0;
    $speedVoiture = $settings['car_speed'] ?? 120;
    $speedBus = $settings['bus_speed'] ?? 100;
    $prepMin = $settings['duration_prep_min'] ?? 90;

    $getTravelTime = function($type) use ($distance, $speedVoiture, $speedBus) {
        if (!$distance) return 0;
        $speed = ($type === 'bus') ? $speedBus : $speedVoiture;
        return ($distance / $speed) * 60;
    };

    $getArrivalTime = function($departureTime, $type) use ($getTravelTime) {
        if (!$departureTime) return null;
        return \Carbon\Carbon::parse($departureTime)->addMinutes($getTravelTime($type));
    };

    $carTime = $getTravelTime('car');
    $busTime = $getTravelTime('bus');
    
    $formatDuration = function($minutes) {
        $h = floor($minutes / 60);
        $m = $minutes % 60;
        return ($h > 0 ? $h.'h' : '') . str_pad($m, 2, '0', STR_PAD_LEFT);
    };
@endphp

<x-layouts.app>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-12">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-gray-100">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-[10px] font-black uppercase tracking-[0.15em] text-indigo-500/80">
                        <li>Logistique</li>
                        <li><x-heroicon-s-chevron-right class="w-2.5 h-2.5 text-gray-300" /></li>
                        <li class="text-gray-400">Résumé</li>
                    </ol>
                </nav>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">{{ $event->name }}</h1>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-2xl border border-gray-100 shadow-sm ring-1 ring-gray-50">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                        <x-heroicon-s-map-pin class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-0.5">Distance Totale</div>
                        <div class="text-base font-black text-gray-900 tabular-nums">{{ $distance }}km</div>
                    </div>
                </div>

                <div class="flex items-center gap-6 bg-white px-5 py-2 rounded-2xl border border-gray-100 shadow-sm ring-1 ring-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-blue-50 text-blue-600 rounded-lg">
                            <x-heroicon-s-users class="w-4 h-4" />
                        </div>
                        <div>
                            <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest leading-none">Voiture</div>
                            <div class="text-sm font-bold text-gray-700 tabular-nums">{{ $formatDuration($carTime) }}</div>
                        </div>
                    </div>
                    <div class="w-px h-8 bg-gray-100"></div>
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-orange-50 text-orange-600 rounded-lg">
                            <x-heroicon-s-truck class="w-4 h-4" />
                        </div>
                        <div>
                            <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest leading-none">Bus</div>
                            <div class="text-sm font-bold text-gray-700 tabular-nums">{{ $formatDuration($busTime) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach($days as $day)
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="bg-gray-900 px-4 py-1.5 rounded-xl text-white font-black text-xs uppercase tracking-[0.2em] italic shadow-md ring-4 ring-gray-50">
                        {{ $day['label'] }}
                    </div>
                    <div class="flex-1 h-px bg-gradient-to-r from-gray-200 to-transparent"></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    
                    <!-- Transport Section -->
                    <div class="lg:col-span-3 space-y-6">
                        @php 
                            $dayTransport = $transportPlan[$day['date']] ?? [];
                            $groupedTransport = collect($dayTransport)->groupBy('flow');
                        @endphp
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach(['aller', 'retour'] as $flow)
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between px-2">
                                        <div class="flex items-center gap-2">
                                            <div class="p-1 px-2 rounded-lg font-black text-[10px] uppercase tracking-widest {{ $flow === 'aller' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600' }}">
                                                {{ $flow }}
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold text-gray-300 uppercase tracking-tighter">{{ count($groupedTransport->get($flow, [])) }} trajets</span>
                                    </div>

                                    <div class="space-y-3">
                                        @forelse($groupedTransport->get($flow, []) as $vehicle)
                                            @php 
                                                $arrival = $getArrivalTime($vehicle['departure_datetime'], $vehicle['type']);
                                                $isFull = count($vehicle['passengers']) >= ($vehicle['capacity'] ?? 99);
                                            @endphp
                                            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden ring-1 ring-gray-100/50 hover:shadow-md transition-shadow">
                                                <div class="px-4 py-2.5 bg-gray-50/50 flex items-center justify-between border-b border-gray-50">
                                                    <div class="flex items-center gap-2.5 min-w-0">
                                                        @if($vehicle['type'] === 'bus')
                                                            <x-heroicon-s-truck class="w-3.5 h-3.5 text-blue-500" />
                                                        @else
                                                            <x-heroicon-s-users class="w-3.5 h-3.5 text-slate-400" />
                                                        @endif
                                                        <span class="text-xs font-black text-gray-800">{{ $vehicle['name'] }}</span>
                                                        <span class="text-[10px] text-gray-400 font-medium truncate">/ {{ $vehicle['driver'] ?? '?' }}</span>
                                                    </div>
                                                    <div class="flex items-center bg-white px-2 py-0.5 rounded-lg border border-gray-100 shadow-sm">
                                                        <span class="text-[11px] font-black {{ $isFull ? 'text-red-500' : 'text-emerald-600' }}">
                                                            {{ count($vehicle['passengers']) }}
                                                        </span>
                                                        <span class="text-[10px] text-gray-300 mx-0.5">/</span>
                                                        <span class="text-[10px] font-bold text-gray-400">{{ $vehicle['capacity'] ?? '?' }}</span>
                                                    </div>
                                                </div>
                                                <div class="p-4">
                                                    <div class="flex items-center justify-between mb-4 px-1">
                                                        <div class="flex flex-col">
                                                            <span class="text-[8px] text-gray-800 uppercase font-black tracking-widest leading-none mb-1">Départ</span>
                                                            <div class="flex items-baseline gap-1.5">
                                                                <span class="text-lg font-black text-gray-900 tabular-nums leading-none">{{ $vehicle['departure_datetime'] ? \Carbon\Carbon::parse($vehicle['departure_datetime'])->format('H:i') : '--:--' }}</span>
                                                                <span class="text-[10px] text-gray-400 font-bold uppercase">{{ $vehicle['departure_location'] ?? 'Stade' }}</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="flex-1 flex justify-center px-4">
                                                            <div class="w-full flex items-center gap-2">
                                                                <div class="flex-1 h-px bg-gray-100"></div>
                                                                <x-heroicon-s-arrow-long-right class="w-4 h-4 text-gray-400" />
                                                                <div class="flex-1 h-px bg-gray-100"></div>
                                                            </div>
                                                        </div>

                                                        <div class="flex flex-col text-right">
                                                            <span class="text-[8px] text-gray-400 uppercase font-black tracking-widest leading-none mb-1">Arrivée est.</span>
                                                            <span class="text-lg text-gray-500 font-black tabular-nums leading-none">{{ $arrival ? $arrival->format('H:i') : '--:--' }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-wrap gap-1.5">
                                                        @foreach($vehicle['passengers'] as $pid)
                                                            @php 
                                                                $p = $participants[$pid] ?? null;
                                                                $timeKey = ($flow === 'retour' ? 'last_competition_datetime' : 'first_competition_datetime');
                                                                $compTime = $p && isset($p[$timeKey]) ? \Carbon\Carbon::parse($p[$timeKey]) : null;
                                                                $isTight = false;
                                                                if ($flow === 'aller') {
                                                                    $isTight = $arrival && $compTime && $arrival->copy()->addMinutes($prepMin)->gt($compTime);
                                                                } else {
                                                                    $isTight = $vehicle['departure_datetime'] && $compTime && \Carbon\Carbon::parse($vehicle['departure_datetime'])->lt($compTime);
                                                                }
                                                            @endphp
                                                            <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg border text-[10px] shadow-sm {{ $isTight ? 'text-red-600 border-red-100 bg-red-50 ring-1 ring-red-100' : 'text-gray-700 bg-white border-gray-100' }}">
                                                                <span class="font-bold">{{ $p['name'] ?? '?' }}</span>
                                                                @if($compTime && $compTime->toDateString() === $day['date'])
                                                                    <span class="text-[9px] opacity-60 font-mono font-bold">({{ $compTime->format('H:i') }})</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    @if(!empty($vehicle['note']))
                                                        <div class="mt-4 text-[10px] text-gray-500 italic px-3 py-2 bg-gray-50/50 rounded-xl border border-gray-100/50 flex gap-2 items-start">
                                                            <x-heroicon-s-information-circle class="w-3.5 h-3.5 shrink-0 text-gray-300" />
                                                            <span class="leading-tight">{{ $vehicle['note'] }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div class="py-8 text-center border-2 border-dashed border-gray-100 rounded-2xl bg-gray-50/30">
                                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em]">Aucun trajet</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Hébergement Section -->
                    @if(!$loop->last)
                    <div class="space-y-4">
                        <div class="flex items-center justify-between px-2">
                            <div class="flex items-center gap-2">
                                <div class="p-1 px-2 rounded-lg font-black text-[10px] uppercase tracking-widest bg-indigo-50 text-indigo-600">
                                    Hébergement
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            @php $dayStay = $stayPlan[$day['date']] ?? []; @endphp
                            @forelse($dayStay as $room)
                                <div class="bg-indigo-50/30 rounded-2xl border border-indigo-100/50 p-4 ring-1 ring-indigo-100/30 shadow-sm relative overflow-hidden">
                                    <div class="absolute top-0 right-0 p-2 opacity-10">
                                        <x-heroicon-s-home class="w-12 h-12 text-indigo-900" />
                                    </div>
                                    <div class="flex items-center justify-between mb-3 relative">
                                        <span class="text-xs font-black text-indigo-900 uppercase tracking-tight truncate pr-4">{{ $room['name'] }}</span>
                                        <span class="text-[10px] font-black text-indigo-400 bg-white px-2 py-0.5 rounded-lg border border-indigo-100 shadow-sm">{{ count($room['occupant_ids']) }} pers.</span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 relative">
                                        @foreach($room['occupant_ids'] as $pid)
                                            <span class="px-2 py-1 bg-white text-indigo-700 text-[10px] font-bold rounded-lg border border-indigo-50 shadow-sm">
                                                {{ $participants[$pid]['name'] ?? '?' }}
                                            </span>
                                        @endforeach
                                    </div>
                                    @if(!empty($room['note']))
                                        <div class="mt-3 text-[10px] text-indigo-500/70 italic font-medium leading-tight relative flex gap-2 items-start bg-white/40 p-2 rounded-lg">
                                            <x-heroicon-s-chat-bubble-bottom-center-text class="w-3 h-3 shrink-0" />
                                            {{ $room['note'] }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="py-8 text-center bg-gray-50/30 border-2 border-dashed border-gray-100 rounded-2xl">
                                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] leading-tight">Aucune chambre</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @endforeach

        <!-- Own Transport Summary -->
        @php
            $independentParticipants = $participants->filter(function($p) use ($days, $transportPlan) {
                foreach ($days as $day) {
                    $dayPlan = $transportPlan[$day['date']] ?? [];
                    $assignedAller = [];
                    $assignedRetour = [];
                    foreach ($dayPlan as $v) {
                        if (($v['flow'] ?? 'aller') === 'retour') {
                            $assignedRetour = array_merge($assignedRetour, $v['passengers'] ?? []);
                        } else {
                            $assignedAller = array_merge($assignedAller, $v['passengers'] ?? []);
                        }
                    }
                    
                    $resp = $p['survey_response']['responses'][$day['date']] ?? null;
                    if ($resp) {
                        $allerMode = $resp['aller']['mode'] ?? '';
                        $retourMode = $resp['retour']['mode'] ?? '';
                        $independentModes = ['train', 'car', 'on_site'];
                        
                        if ((in_array($allerMode, $independentModes) && !in_array($p['id'], $assignedAller)) || 
                            (in_array($retourMode, $independentModes) && !in_array($p['id'], $assignedRetour))) {
                            return true;
                        }
                    }
                }
                return false;
            });
        @endphp

        @if($independentParticipants->isNotEmpty())
            <div class="pt-8 border-t border-gray-100 space-y-6">
                <div class="flex items-end justify-between px-2">
                    <div>
                        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Transports non organisés</h2>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.2em] mt-1">Voyages par ses propres moyens uniquement</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden overflow-x-auto ring-1 ring-gray-50">
                    <table class="w-full text-left table-fixed">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                <th class="px-6 py-4">Participant</th>
                                @foreach($days as $day)
                                    <th class="px-6 py-4 text-center">{{ $day['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($independentParticipants as $p)
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-semibold text-gray-800">{{ $p['name'] }}</div>
                                    </td>
                                    @foreach($days as $day)
                                        @php
                                            $resp = $p['survey_response']['responses'][$day['date']] ?? null;
                                            $dayPlan = $transportPlan[$day['date']] ?? [];
                                            $assignedAller = [];
                                            $assignedRetour = [];
                                            foreach ($dayPlan as $v) {
                                                if (($v['flow'] ?? 'aller') === 'retour') {
                                                    $assignedRetour = array_merge($assignedRetour, $v['passengers'] ?? []);
                                                } else {
                                                    $assignedAller = array_merge($assignedAller, $v['passengers'] ?? []);
                                                }
                                            }
                                            
                                            $aller = $resp['aller']['mode'] ?? null;
                                            $retour = $resp['retour']['mode'] ?? null;
                                            $independentModes = ['train', 'car', 'on_site'];
                                            
                                            $showAller = $aller && in_array($aller, $independentModes) && !in_array($p['id'], $assignedAller);
                                            $showRetour = $retour && in_array($retour, $independentModes) && !in_array($p['id'], $assignedRetour);
                                        @endphp
                                        <td class="px-6 py-4 text-center">
                                            @if($showAller || $showRetour)
                                                <div class="flex flex-col gap-1.5 items-center">
                                                    @if($showAller)
                                                        <span class="inline-flex items-center gap-1.5 bg-blue-50/50 text-blue-700 px-2 py-0.5 rounded-lg border border-blue-100 text-[9px] font-black tracking-tighter uppercase whitespace-nowrap shadow-sm">
                                                            <x-heroicon-s-arrow-up-right class="w-3 h-3" />
                                                            {{ $aller === 'on_site' ? 'Sur place' : $aller }}
                                                        </span>
                                                    @endif
                                                    @if($showRetour)
                                                        <span class="inline-flex items-center gap-1.5 bg-orange-50/50 text-orange-700 px-2 py-0.5 rounded-lg border border-orange-100 text-[9px] font-black tracking-tighter uppercase whitespace-nowrap shadow-sm">
                                                            <x-heroicon-s-arrow-down-left class="w-3 h-3" />
                                                            {{ $retour === 'on_site' ? 'Sur place' : $retour }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-100 font-bold text-xs">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Athletes Planning -->
        <div class="pt-8 border-t border-gray-100 space-y-6">
            <div class="flex items-end justify-between px-2">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Planning des athlètes</h2>
                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.2em] mt-1">Horaires de compétition à respecter</p>
                </div>
                <div class="flex items-center gap-3 bg-gray-50/50 px-3 py-1.5 rounded-xl border border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-indigo-600 shadow-sm shadow-indigo-100"></div>
                        <span class="text-[9px] font-black text-gray-500 uppercase tracking-tighter">1ère épreuve</span>
                    </div>
                    <div class="w-px h-3 bg-gray-200"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full border-2 border-indigo-200 bg-white"></div>
                        <span class="text-[9px] font-black text-gray-500 uppercase tracking-tighter">Dernière épreuve</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden overflow-x-auto ring-1 ring-gray-50">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] font-black uppercase tracking-widest text-gray-400">
                            <th class="px-6 py-4 w-56">Athlète</th>
                            @foreach($days as $day)
                                <th class="px-6 py-4 text-center">{{ $day['label'] }}</th>
                            @endforeach
                            <th class="px-6 py-4 text-right w-64">Observations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($participants as $p)
                            <tr class="hover:bg-gray-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-xs font-semibold text-gray-800">{{ $p['name'] }}</div>
                                </td>
                                @foreach($days as $day)
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $first = $p['first_competition_datetime'] ?? null;
                                            $last = $p['last_competition_datetime'] ?? null;
                                            $isOnDay = $first && str_starts_with($first, $day['date']);
                                        @endphp
                                        @if($isOnDay)
                                            <div class="inline-flex items-center gap-1.5 bg-gray-50/50 px-2 py-1 rounded-lg border border-gray-100/50 shadow-sm">
                                                <span class="text-[11px] font-black text-indigo-600 tabular-nums">
                                                    {{ \Carbon\Carbon::parse($first)->format('H:i') }}
                                                </span>
                                                <span class="text-gray-300 font-black text-[10px]">/</span>
                                                <span class="text-[11px] font-bold text-gray-400 tabular-nums">
                                                    {{ $last ? \Carbon\Carbon::parse($last)->format('H:i') : '--:--' }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-gray-100 font-bold text-xs">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-6 py-4 text-right">
                                    <span class="text-[10px] text-gray-400 italic font-medium truncate block max-w-full" title="{{ $p['survey_response']['remarks'] ?? '' }}">
                                        {{ $p['survey_response']['remarks'] ?? '' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
