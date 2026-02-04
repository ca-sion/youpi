<x-filament-panels::page>
    <div
        x-data="transportBoard()"
        x-init="initWait()"
        class="space-y-6"
    >
        <!-- Instructions -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 text-sm text-blue-700">
                    <p>
                        <strong>Mode d'emploi :</strong> Glissez les athlètes entre les colonnes. Cliquez sur l'icône <svg class="inline w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg> pour modifier les détails d'un transport ou d'une chambre.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-4">
                <h2 class="text-xl font-bold text-gray-800">1. Transport</h2>
                <div class="text-sm text-gray-500 italic">
                    N'oubliez pas d'enregistrer après vos modifications.
                </div>
            </div>
            <div class="flex gap-2">
                <x-filament::button 
                    x-on:click="saveAll()" 
                    color="success" 
                    icon="heroicon-o-check-circle"
                    size="lg"
                >
                    Enregistrer tout le Plan
                </x-filament::button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Transport Unassigned Column -->
            <div class="lg:col-span-1 bg-gray-50 p-4 rounded-xl border border-gray-200 flex flex-col h-[600px]">
                <h3 class="font-bold text-gray-700 mb-2 flex justify-between items-center">
                    <span>Athlètes (Transport)</span>
                    <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full font-mono">{{ count($unassignedTransport) }}</span>
                </h3>
                <div id="transport-unassigned-list" class="flex-1 overflow-y-auto space-y-2 min-h-[100px] p-1" data-group="transport">
                    @foreach($unassignedTransport as $p)
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 cursor-move hover:border-blue-300 hover:shadow-md transition-all relative group" data-id="{{ $p['id'] }}">
                            <div class="font-medium text-gray-900">{{ $p['name'] }}</div>
                            <div class="text-[10px] text-gray-500 flex justify-between mt-1">
                                <span>{{ $p['survey_response']['transport_mode'] ?? '?' }}</span>
                                @if(in_array($p['id'], $hotelNeededIds))
                                    <span class="text-blue-500 font-bold">🏠 Hôtel</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Vehicles Columns -->
            <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 overflow-y-auto h-[600px] content-start pr-2">
                @foreach($transportPlan as $index => $vehicle)
                    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col transition-all hover:shadow-md" wire:key="v-{{ $vehicle['id'] ?? $index }}">
                        <div class="flex justify-between items-start mb-3 pb-3 border-b border-gray-100">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                                    @if(($vehicle['type'] ?? 'car') === 'bus')
                                        <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8a2 2 0 012 2v10a2 2 0 01-2 2H8a2 2 0 01-2-2V9a2 2 0 012-2zm0 0V5a2 2 0 012-2h4a2 2 0 012 2v2M9 17h.01M15 17h.01" /></svg>
                                    @else
                                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                                    @endif
                                    {{ $vehicle['name'] }}
                                </h3>
                                <div class="text-xs text-gray-500 italic">{{ $vehicle['driver'] ?? 'À définir' }}</div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="inline-block {{ count($vehicle['passengers']) > ($vehicle['capacity'] ?? 0) ? 'bg-red-100 text-red-800 font-bold' : 'bg-blue-100 text-blue-800' }} text-[10px] px-2 py-0.5 rounded-full">
                                    {{ count($vehicle['passengers']) }} / {{ $vehicle['capacity'] }}
                                </span>
                                <div class="flex gap-2">
                                    <button 
                                        x-on:click="$wire.mountAction('editVehicle', { index: {{ $index }} })"
                                        class="text-gray-400 hover:text-blue-600 transition-colors"
                                        title="Modifier les détails"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <button 
                                        wire:click="removeVehicle({{ $index }})" 
                                        wire:confirm="Supprimer ce transport ?"
                                        class="text-gray-300 hover:text-red-500 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3 text-[10px] space-y-1 bg-gray-50 p-2 rounded-lg border border-gray-100">
                             <div class="flex justify-between items-center text-gray-600">
                                <span class="font-medium">Départ :</span>
                                <span class="font-mono font-bold px-2 py-0.5 rounded {{ empty($vehicle['departure_datetime'] ?? null) ? 'text-red-500 bg-red-50' : 'text-green-700 bg-green-50' }}">
                                    {{ ($vehicle['departure_datetime'] ?? null) ? \Carbon\Carbon::parse($vehicle['departure_datetime'])->format('H:i') : 'TBD' }} 
                                    @if(!empty($vehicle['departure_location'])) | {{ $vehicle['departure_location'] }} @endif
                                </span>
                             </div>

                             @if(!empty($vehicle['note']))
                                <div class="text-gray-500 italic mt-1 border-t pt-1">Note: {{ $vehicle['note'] }}</div>
                             @endif
                             
                             @if(isset($alerts[$index]))
                                <div class="space-y-1 pt-2">
                                    @foreach($alerts[$index] as $alert)
                                        <div class="flex items-start text-[9px] leading-tight px-2 py-1 rounded {{ $alert['type'] == 'danger' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}">
                                            <svg class="w-3 h-3 mr-1 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $alert['msg'] }}
                                        </div>
                                    @endforeach
                                </div>
                             @endif
                        </div>

                        <div id="vehicle-{{ $index }}" class="flex-1 space-y-1 min-h-[60px] bg-gray-50/50 p-2 rounded-lg border-2 border-dashed border-gray-200" data-group="transport" data-vehicle-index="{{ $index }}">
                            @foreach($vehicle['passengers'] as $pid)
                                @php $p = $participantsMap[$pid] ?? ['name' => '?', 'id' => $pid]; @endphp
                                <div class="bg-white p-2 rounded shadow-sm border border-gray-100 cursor-move hover:border-blue-300 transition-all font-medium text-xs text-gray-700 flex justify-between" data-id="{{ $pid }}">
                                    <span>{{ $p['name'] }}</span>
                                    @if(in_array($pid, $hotelNeededIds))
                                        <span class="text-[9px] text-blue-500">🏠</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <hr class="my-8 border-gray-200" />

        <!-- Stay Management Section -->
        <div class="flex justify-between items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-800">2. Hébergement (Hôtel)</h2>
            <div class="text-sm text-gray-500 italic">
                Glissez les athlètes ayant besoin d'un hôtel dans les chambres.
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Stay Unassigned Column -->
            <div class="lg:col-span-1 bg-gray-50 p-4 rounded-xl border border-gray-200 flex flex-col h-[600px]">
                <h3 class="font-bold text-gray-700 mb-2 flex justify-between items-center">
                    <span>Athlètes (Stay)</span>
                    <span class="bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full font-mono">{{ count($unassignedStay) }}</span>
                </h3>
                <div id="stay-unassigned-list" class="flex-1 overflow-y-auto space-y-2 min-h-[100px] p-1" data-group="stay">
                    @foreach($unassignedStay as $p)
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200 cursor-move hover:border-indigo-300 hover:shadow-md transition-all relative group" data-id="{{ $p['id'] }}">
                            <div class="font-medium text-gray-900">{{ $p['name'] }}</div>
                             <div class="text-[10px] text-indigo-500 font-bold">🏠 Hôtel requis</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Rooms Columns -->
            <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 overflow-y-auto h-[600px] content-start pr-2">
                @foreach($stayPlan as $index => $room)
                    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col transition-all hover:shadow-md" wire:key="stay-{{ $room['id'] ?? $index }}">
                        <div class="flex justify-between items-start mb-3 pb-3 border-b border-gray-100">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                    {{ $room['name'] ?? 'Chambre' }}
                                </h3>
                                @if(!empty($room['note']))
                                    <div class="text-[10px] text-gray-500 italic">{{ \Illuminate\Support\Str::limit($room['note'], 40) }}</div>
                                @endif
                            </div>
                            <div class="flex gap-1">
                                <button 
                                    x-on:click="$wire.mountAction('editRoom', { index: {{ $index }} })"
                                    class="p-1 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                    title="Modifier la chambre"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                                <button 
                                    wire:click="removeRoom({{ $index }})" 
                                    wire:confirm="Supprimer cette chambre ?"
                                    class="p-1 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Supprimer la chambre"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>

                        <div id="room-{{ $index }}" class="flex-1 space-y-1 min-h-[60px] bg-indigo-50/30 p-2 rounded-lg border-2 border-dashed border-indigo-100" data-group="stay" data-room-index="{{ $index }}">
                            @foreach($room['occupant_ids'] ?? [] as $pid)
                                @php $p = $participantsMap[$pid] ?? ['name' => '?', 'id' => $pid]; @endphp
                                <div class="bg-white p-2 rounded shadow-sm border border-gray-100 cursor-move hover:border-indigo-300 transition-all font-medium text-xs text-gray-700 flex justify-between" data-id="{{ $pid }}">
                                    <span>{{ $p['name'] }}</span>
                                    <span class="text-[9px] text-blue-500 font-bold">🏠</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if(!empty($globalAlerts))
            <div class="mt-12 p-6 bg-white rounded-2xl shadow-sm border border-gray-100 space-y-4">
                <h4 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    Alertes Générales
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($globalAlerts as $alert)
                        <div class="flex items-center text-sm px-4 py-3 rounded-xl border {{ $alert['type'] == 'danger' ? 'bg-red-50 border-red-100 text-red-800' : 'bg-orange-50 border-orange-100 text-orange-800' }}">
                            {{ $alert['msg'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Modals & Scripts -->
    <x-filament-actions::modals />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script>
        function transportBoard() {
            return {
                initWait() {
                    setTimeout(() => {
                        this.setupSortables();
                    }, 100);
                },

                setupSortables() {
                    const containers = document.querySelectorAll('[data-group]');
                    containers.forEach(el => {
                        new Sortable(el, {
                            group: el.getAttribute('data-group'), // Separate groups: transport vs stay
                            animation: 150,
                            ghostClass: 'bg-blue-50',
                            dragClass: 'opacity-50',
                        });
                    });
                },

                saveAll() {
                    // 1. Transport Plan - Synchronize with latest server state
                    const vehicleEls = document.querySelectorAll('[data-vehicle-index]');
                    // Get latest plan from Livewire public property instead of static directive
                    const newTransportPlan = JSON.parse(JSON.stringify(this.$wire.transportPlan));
                    
                    vehicleEls.forEach(el => {
                        const index = el.getAttribute('data-vehicle-index');
                        if (newTransportPlan[index]) {
                            const pIds = Array.from(el.children)
                                .map(child => child.getAttribute('data-id'))
                                .filter(id => id);
                            newTransportPlan[index].passengers = pIds;
                        }
                    });

                    // 2. Stay Plan - Synchronize with latest server state
                    const roomEls = document.querySelectorAll('[data-room-index]');
                    const newStayPlan = JSON.parse(JSON.stringify(this.$wire.stayPlan));

                    roomEls.forEach(el => {
                        const index = el.getAttribute('data-room-index');
                        if (newStayPlan[index]) {
                            const pIds = Array.from(el.children)
                                .map(child => child.getAttribute('data-id'))
                                .filter(id => id);
                            newStayPlan[index].occupant_ids = pIds;
                        }
                    });

                    this.$wire.saveAllPlans(newTransportPlan, newStayPlan);
                }
            }
        }
    </script>
</x-filament-panels::page>
