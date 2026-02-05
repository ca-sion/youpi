@php
    $distance = $settings['distance_km'] ?? 0;
    $speedVoiture = $settings['car_speed'] ?? 120;
    $speedBus = $settings['bus_speed'] ?? 100;
    $prepMin = $settings['duration_prep_min'] ?? 90;

    $getArrivalTime = function($departureTime, $type) use ($distance, $speedVoiture, $speedBus) {
        if (!$departureTime || !$distance) return null;
        $speed = ($type === 'bus') ? $speedBus : $speedVoiture;
        $travelMin = ($distance / $speed) * 60;
        return \Carbon\Carbon::parse($departureTime)->addMinutes($travelMin);
    };
@endphp

<x-layouts.app>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-12">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-gray-200 pb-6">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-indigo-600">
                        <li>Logistique</li>
                        <li>
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        </li>
                        <li class="text-gray-500">Résumé</li>
                    </ol>
                </nav>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">{{ $event->name }}</h1>
            </div>
            
            <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-2xl shadow-sm border border-gray-100">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                    <x-heroicon-o-map-pin class="w-5 h-5" />
                </div>
                <div class="text-xs font-bold text-gray-500 uppercase leading-none">
                    Distance: <span class="text-gray-900">{{ $distance }} km</span>
                </div>
            </div>
        </div>

        @foreach($days as $day)
            <div class="space-y-6">
                <!-- Day Header -->
                <div class="flex items-center gap-4">
                    <div class="bg-indigo-600 px-4 py-1 rounded-full text-white font-black text-sm uppercase tracking-widest shadow-lg shadow-indigo-100 italic">
                        {{ $day['label'] }}
                    </div>
                    <div class="flex-1 h-px bg-gradient-to-r from-gray-200 to-transparent"></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Transport Section -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="flex items-center gap-2 px-1">
                            <x-heroicon-o-truck class="w-5 h-5 text-gray-400" />
                            <h3 class="font-bold text-gray-900 uppercase tracking-tight text-sm">Transports</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php $dayTransport = $transportPlan[$day['date']] ?? []; @endphp
                            @forelse($dayTransport as $vehicle)
                                @php 
                                    $arrival = $getArrivalTime($vehicle['departure_datetime'], $vehicle['type']);
                                    $isFull = count($vehicle['passengers']) >= ($vehicle['capacity'] ?? 99);
                                @endphp
                                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden group">
                                    <div class="p-4 border-b border-gray-50 flex justify-between items-start bg-gray-50/30 group-hover:bg-gray-50/80 transition-colors">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <div class="p-1 rounded {{ $vehicle['type'] === 'bus' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                    @if($vehicle['type'] === 'bus')
                                                        <x-heroicon-s-truck class="w-3.5 h-3.5" />
                                                    @else
                                                        <x-heroicon-s-users class="w-3.5 h-3.5" />
                                                    @endif
                                                </div>
                                                <h4 class="font-black text-gray-900 text-xs uppercase truncate">{{ $vehicle['name'] }}</h4>
                                            </div>
                                            <p class="text-[10px] text-gray-500 font-medium mt-0.5 truncate italic">Chauffeur: <span class="text-gray-700">{{ $vehicle['driver'] ?? 'À définir' }}</span></p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $isFull ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                                                {{ count($vehicle['passengers']) }}/{{ $vehicle['capacity'] ?? '?' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="p-4 grid grid-cols-2 gap-4">
                                        <div class="space-y-0.5">
                                            <span class="text-[9px] font-black text-gray-400 uppercase leading-none block">Départ</span>
                                            <div class="flex items-center gap-1.5 font-black text-gray-900 text-base tabular-nums">
                                                <x-heroicon-o-clock class="w-4 h-4 text-indigo-500" />
                                                {{ $vehicle['departure_datetime'] ? \Carbon\Carbon::parse($vehicle['departure_datetime'])->format('H:i') : '--:--' }}
                                            </div>
                                            <span class="text-[10px] text-gray-500 font-medium truncate block">{{ $vehicle['departure_location'] ?? 'Stade' }}</span>
                                        </div>
                                        <div class="space-y-0.5">
                                            <span class="text-[9px] font-black text-gray-400 uppercase leading-none block italic">Arrivée est.</span>
                                            <div class="flex items-center gap-1.5 font-bold text-indigo-600 text-base tabular-nums opacity-60 group-hover:opacity-100 transition-opacity">
                                                <x-heroicon-o-arrow-right-circle class="w-4 h-4" />
                                                {{ $arrival ? $arrival->format('H:i') : '--:--' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-4 pb-4">
                                        <div class="bg-gray-50 rounded-xl p-3 space-y-2">
                                            <span class="text-[9px] font-black text-gray-400 uppercase block tracking-widest">Liste des Voyageurs</span>
                                            <div class="grid grid-cols-1 gap-1">
                                                @foreach($vehicle['passengers'] as $pid)
                                                    @php 
                                                        $p = $participants[$pid] ?? null;
                                                        $firstComp = $p && isset($p['first_competition_datetime']) ? \Carbon\Carbon::parse($p['first_competition_datetime']) : null;
                                                        $isTight = $arrival && $firstComp && $arrival->copy()->addMinutes($prepMin)->gt($firstComp);
                                                    @endphp
                                                    <div class="flex items-center justify-between text-[11px] font-medium bg-white px-2 py-1.5 rounded-lg border border-gray-100 shadow-sm">
                                                        <span class="text-gray-800 truncate">{{ $p['name'] ?? 'Inconnu' }}</span>
                                                        @if($firstComp && $firstComp->toDateString() === $day['date'])
                                                            <div class="flex items-center gap-1 {{ $isTight ? 'text-red-500' : 'text-gray-400' }}">
                                                                <x-heroicon-s-bolt class="w-3 h-3" />
                                                                <span class="font-bold tabular-nums">{{ $firstComp->format('H:i') }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @if(!empty($vehicle['note']))
                                            <div class="mt-2 text-[10px] text-gray-500 italic px-1 flex gap-1 items-start">
                                                <x-heroicon-s-information-circle class="w-3 h-3 shrink-0" />
                                                {{ $vehicle['note'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full py-8 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                                    <p class="text-sm text-gray-500 font-medium italic">Aucun transport planifié pour cette journée.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    @if(!$loop->last)
                    <!-- Stay/Hosting Section -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 px-1">
                            <x-heroicon-o-home class="w-5 h-5 text-gray-400" />
                            <h3 class="font-bold text-gray-900 uppercase tracking-tight text-sm">Hébergement</h3>
                        </div>

                        <div class="space-y-3">
                            @php $dayStay = $stayPlan[$day['date']] ?? []; @endphp
                            @forelse($dayStay as $room)
                                <div class="bg-indigo-50/50 rounded-2xl border border-indigo-100 p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div class="p-1 bg-white rounded shadow-sm text-indigo-600 border border-indigo-100">
                                                <x-heroicon-s-home class="w-3 h-3" />
                                            </div>
                                            <h4 class="font-black text-indigo-900 text-[11px] uppercase truncate">{{ $room['name'] }}</h4>
                                        </div>
                                        <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-tighter">{{ count($room['occupant_ids']) }} pers.</span>
                                    </div>
                                    
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($room['occupant_ids'] as $pid)
                                            <div class="px-2 py-1 bg-white rounded-lg border border-indigo-100 text-[10px] font-bold text-gray-800 shadow-sm">
                                                {{ $participants[$pid]['name'] ?? '?' }}
                                            </div>
                                        @endforeach
                                    </div>
                                    @if(!empty($room['note']))
                                        <div class="text-[10px] text-indigo-400 italic font-medium leading-tight">
                                            {{ $room['note'] }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="py-6 text-center bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest leading-tight">Pas d'hébergement<br>ce jour-là</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        @endforeach

        <!-- Global Summary / Athlètes -->
        <div class="pt-8 border-t border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-black text-gray-900">Planning des Athlètes</h2>
                    <p class="text-sm text-gray-500 font-medium">Récapitulatif des heures d'épreuve estimées.</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-black uppercase tracking-widest text-gray-400">
                            <th class="px-6 py-4">Athlète</th>
                            @foreach($days as $day)
                                <th class="px-6 py-4">{{ $day['label'] }}</th>
                            @endforeach
                            <th class="px-6 py-4">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($participants as $p)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-black text-gray-900">{{ $p['name'] }}</div>
                                </td>
                                @foreach($days as $day)
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $first = $p['first_competition_datetime'] ?? null;
                                            $last = $p['last_competition_datetime'] ?? null;
                                            $isOnDay = $first && str_starts_with($first, $day['date']);
                                        @endphp
                                        @if($isOnDay)
                                            <div class="inline-flex items-center gap-2 bg-indigo-50 px-2 py-1 rounded-lg">
                                                <div class="text-xs font-black text-indigo-700 tabular-nums">
                                                    {{ \Carbon\Carbon::parse($first)->format('H:i') }}
                                                </div>
                                                <div class="w-px h-3 bg-indigo-200"></div>
                                                <div class="text-xs font-medium text-indigo-400 tabular-nums">
                                                    {{ $last ? \Carbon\Carbon::parse($last)->format('H:i') : '--:--' }}
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-300 text-xs">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-6 py-4 text-xs text-gray-500 italic font-medium">
                                    {{ $p['note'] ?? '' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
