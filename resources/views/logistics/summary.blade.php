<x-layouts.app>
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ $event->event_name }} - Logistique</h1>

        <!-- Transport Plan -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Transport</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Plan de déplacement et horaires.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                @forelse($transportPlan as $vehicle)
                    <div class="border rounded-xl p-5 bg-white shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="font-bold text-xl text-gray-800">{{ $vehicle['name'] }}</div>
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">{{ count($vehicle['passengers']) }} passagers</span>
                        </div>
                        
                        <div class="text-sm text-gray-600 mb-4 space-y-2">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span>Chauffeur: <strong>{{ $vehicle['driver'] ?? 'N/A' }}</strong></span>
                            </div>
                            <div class="flex items-center text-indigo-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-bold text-lg">{{ $vehicle['departure_datetime'] ? \Carbon\Carbon::parse($vehicle['departure_datetime'])->format('H:i') : 'TBD' }}</span>
                                <span class="text-gray-500 ml-2 text-xs">({{ $vehicle['departure_location'] ?? 'Stade' }})</span>
                            </div>
                        </div>
                        
                        <div class="border-t pt-3">
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Passagers</div>
                            <ul class="text-sm text-gray-600 space-y-1">
                                @foreach($vehicle['passengers'] as $pid)
                                    <li class="flex items-center">
                                        <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        {{ $participants[$pid]['name'] ?? 'Unknown' }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-4 text-gray-500">Aucun transport planifié pour le moment.</div>
                @endforelse
            </div>
        </div>

        <!-- Planning / Participants -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
             <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Planning Athlètes</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Heures de première et dernière épreuve (Estimées).</p>
            </div>
            <div class="flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Athlète</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Première épreuve</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dernière épreuve</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($participants as $p)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $p['name'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if(isset($p['first_competition_datetime']))
                                                    {{ \Carbon\Carbon::parse($p['first_competition_datetime'])->isoFormat('ddd H:mm') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if(isset($p['last_competition_datetime']))
                                                    {{ \Carbon\Carbon::parse($p['last_competition_datetime'])->isoFormat('ddd H:mm') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $p['note'] ?? '' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
