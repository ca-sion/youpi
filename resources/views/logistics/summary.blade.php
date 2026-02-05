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
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-8">
        <!-- Header Compact -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <nav class="flex mb-1" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-1 text-[10px] font-bold uppercase tracking-wider text-indigo-500">
                        <li>Logistique</li>
                        <li><x-heroicon-s-chevron-right class="w-3 h-3 text-gray-300" /></li>
                        <li class="text-gray-400">Résumé</li>
                    </ol>
                </nav>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">{{ $event->name }}</h1>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg">
                        <x-heroicon-s-map-pin class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-[9px] font-black text-gray-400 uppercase leading-none">Distance</div>
                        <div class="text-sm font-bold text-gray-900">{{ $distance }}km</div>
                    </div>
                </div>
                <div class="flex items-center gap-4 border-l border-gray-100 pl-6">
                    <div class="flex items-center gap-2 text-gray-600">
                        <x-heroicon-s-users class="w-4 h-4 text-gray-400" />
                        <span class="text-xs font-bold">{{ $formatDuration($carTime) }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <x-heroicon-s-truck class="w-4 h-4 text-gray-400" />
                        <span class="text-xs font-bold">{{ $formatDuration($busTime) }}</span>
                    </div>
                </div>
            </div>
        </div>

        @foreach($days as $day)
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="bg-gray-900 px-3 py-1 rounded-lg text-white font-black text-xs uppercase tracking-widest italic shadow-sm">
                        {{ $day['label'] }}
                    </div>
                    <div class="flex-1 h-px bg-gray-100"></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    
                    <!-- Transport Section Compact -->
                    <div class="lg:col-span-3 space-y-4">
                        @php 
                            $dayTransport = $transportPlan[$day['date']] ?? [];
                            $groupedTransport = collect($dayTransport)->groupBy('flow');
                        @endphp
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach(['aller', 'retour'] as $flow)
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 px-1">
                                        @if($flow === 'aller')
                                            <x-heroicon-s-arrow-up-right class="w-3.5 h-3.5 text-blue-500" />
                                        @else
                                            <x-heroicon-s-arrow-down-left class="w-3.5 h-3.5 text-orange-500" />
                                        @endif
                                        <h3 class="font-black text-gray-900 uppercase tracking-widest text-[10px]">{{ $flow }}</h3>
                                    </div>

                                    <div class="space-y-2">
                                        @forelse($groupedTransport->get($flow, []) as $vehicle)
                                            @php 
                                                $arrival = $getArrivalTime($vehicle['departure_datetime'], $vehicle['type']);
                                                $isFull = count($vehicle['passengers']) >= ($vehicle['capacity'] ?? 99);
                                            @endphp
                                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                                                <div class="px-3 py-2 bg-gray-50/50 flex items-center justify-between border-b border-gray-50">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <span class="text-[10px] font-black {{ $vehicle['type'] === 'bus' ? 'text-blue-600' : 'text-gray-600' }}">{{ $vehicle['name'] }}</span>
                                                        <span class="text-[9px] text-gray-400 font-medium truncate">/ {{ $vehicle['driver'] ?? '?' }}</span>
                                                    </div>
                                                    <span class="text-[9px] font-bold {{ $isFull ? 'text-orange-600' : 'text-green-600' }}">
                                                        {{ count($vehicle['passengers']) }}/{{ $vehicle['capacity'] ?? '?' }}
                                                    </span>
                                                </div>
                                                <div class="p-3">
                                                    <div class="flex items-center justify-between mb-3 px-1">
                                                        <div class="flex items-baseline gap-1">
                                                            <span class="text-sm font-black text-gray-900 tabular-nums">{{ $vehicle['departure_datetime'] ? \Carbon\Carbon::parse($vehicle['departure_datetime'])->format('H:i') : '--:--' }}</span>
                                                            <span class="text-[9px] text-gray-400 uppercase font-bold">{{ $vehicle['departure_location'] ?? 'Stade' }}</span>
                                                        </div>
                                                        <x-heroicon-s-chevron-right class="w-3 h-3 text-gray-300" />
                                                        <div class="flex items-baseline gap-1 text-right">
                                                            <span class="text-[9px] text-gray-400 uppercase font-bold">Arrivée est.</span>
                                                            <span class="text-sm font-black text-indigo-600 tabular-nums">{{ $arrival ? $arrival->format('H:i') : '--:--' }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-wrap gap-1">
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
                                                            <div class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-gray-50 border border-gray-100 text-[10px] {{ $isTight ? 'text-red-600 border-red-100 bg-red-50' : 'text-gray-700' }}">
                                                                <span class="font-bold">{{ $p['name'] ?? '?' }}</span>
                                                                @if($compTime && $compTime->toDateString() === $day['date'])
                                                                    <span class="text-[8px] opacity-70 tabular-nums">({{ $compTime->format('H:i') }})</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    @if(!empty($vehicle['note']))
                                                        <div class="mt-2 text-[9px] text-gray-500 italic px-1 flex gap-1 items-start bg-gray-50/50 p-1.5 rounded-lg border border-gray-100/50">
                                                            <x-heroicon-s-information-circle class="w-3 h-3 shrink-0 text-gray-400" />
                                                            {{ $vehicle['note'] }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div class="py-4 text-center border border-dashed border-gray-100 rounded-xl">
                                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Aucun trajet</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Hébergement Section Compact -->
                    @if(!$loop->last)
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 px-1">
                            <x-heroicon-s-home class="w-3.5 h-3.5 text-gray-400" />
                            <h3 class="font-black text-gray-900 uppercase tracking-widest text-[10px]">Hébergement</h3>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            @php $dayStay = $stayPlan[$day['date']] ?? []; @endphp
                            @forelse($dayStay as $room)
                                <div class="bg-indigo-50/40 rounded-xl border border-indigo-100/50 p-2.5">
                                    <div class="flex items-center justify-between mb-1.5 px-0.5">
                                        <span class="text-[10px] font-black text-indigo-900 uppercase truncate">{{ $room['name'] }}</span>
                                        <span class="text-[8px] font-bold text-indigo-400 uppercase tracking-tighter">{{ count($room['occupant_ids']) }} pers.</span>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($room['occupant_ids'] as $pid)
                                            <span class="px-1.5 py-0.5 bg-white text-indigo-700 text-[9px] font-bold rounded border border-indigo-50 shadow-sm">
                                                {{ $participants[$pid]['name'] ?? '?' }}
                                            </span>
                                        @endforeach
                                    </div>
                                    @if(!empty($room['note']))
                                        <div class="mt-1.5 text-[9px] text-indigo-400 italic font-medium leading-tight px-0.5">
                                            {{ $room['note'] }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="py-4 text-center bg-gray-50/50 rounded-xl">
                                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest leading-tight">Aucun</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @endforeach

        <!-- Athletes Planning Compact -->
        <div class="pt-6 border-t border-gray-100 space-y-4">
            <div class="flex items-end justify-between">
                <div>
                    <h2 class="text-xl font-black text-gray-900">Planning Athlètes</h2>
                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Compétitions estimées</p>
                    <div class="flex items-center gap-4 mt-1">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 rounded-full bg-indigo-600"></div>
                            <span class="text-[9px] font-bold text-gray-500 uppercase">1ère épreuve</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 rounded-full border-2 border-indigo-200"></div>
                            <span class="text-[9px] font-bold text-gray-500 uppercase">Dernière épreuve</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-[9px] font-black uppercase tracking-widest text-gray-400">
                            <th class="px-4 py-3">Athlète</th>
                            @foreach($days as $day)
                                <th class="px-4 py-3">{{ $day['label'] }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-right">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($participants as $p)
                            <tr class="hover:bg-gray-50/30 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs font-black text-gray-900">{{ $p['name'] }}</div>
                                </td>
                                @foreach($days as $day)
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $first = $p['first_competition_datetime'] ?? null;
                                            $last = $p['last_competition_datetime'] ?? null;
                                            $isOnDay = $first && str_starts_with($first, $day['date']);
                                        @endphp
                                        @if($isOnDay)
                                            <div class="inline-flex items-center gap-1.5 bg-gray-50 px-2 py-1 rounded border border-gray-100">
                                                <span class="text-[10px] font-black text-indigo-600 tabular-nums">
                                                    {{ \Carbon\Carbon::parse($first)->format('H:i') }}
                                                </span>
                                                <span class="text-[8px] text-gray-300 font-bold">→</span>
                                                <span class="text-[10px] font-medium text-gray-400 tabular-nums">
                                                    {{ $last ? \Carbon\Carbon::parse($last)->format('H:i') : '--:--' }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-gray-200 text-xs">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-right">
                                    <span class="text-[10px] text-gray-400 italic font-medium max-w-[200px] truncate block ml-auto" title="{{ $p['survey_response']['remarks'] ?? '' }}">
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
