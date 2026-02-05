<x-filament-panels::page>
    <div
        x-data="transportBoard({
            transportPlans: @entangle('transportPlans'),
            stayPlans: @entangle('stayPlans'),
            unassignedTransport: @entangle('unassignedTransport'),
            unassignedStay: @entangle('unassignedStay'),
            participantsMap: @js($participantsMap),
            hotelNeededIds: @js($hotelNeededIds),
            globalAlerts: @entangle('globalAlerts'),
            alerts: @entangle('alerts'),
            selectedDay: @entangle('selectedDay').live,
            days: @js($days)
        })"
        x-init="init()"
        x-on:refresh-sortables.window="$nextTick(() => setupSortables())"
        class="space-y-4"
    >
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-3 bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                    <x-heroicon-o-truck class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-tight">Logistique Flux</h2>
                    <p class="text-xs text-gray-500 font-medium" x-text="formatDate(selectedDay)"></p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-lg">
                    <span class="text-xs font-bold text-gray-400 uppercase">Jour</span>
                    <select x-model="selectedDay" class="bg-transparent border-none p-0 text-sm font-bold text-gray-700 focus:ring-0 cursor-pointer w-32">
                        <template x-for="day in days" :key="day.date">
                            <option :value="day.date" x-text="day.label" :selected="day.date === selectedDay"></option>
                        </template>
                    </select>
                </div>

                <x-filament::button 
                    x-on:click="saveAll()" 
                    color="primary" 
                    size="sm"
                    icon="heroicon-m-check"
                >
                    Enregistrer
                </x-filament::button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left: Unassigned Transport -->
            <div class="lg:col-span-2 flex flex-col gap-4">
                <div class="bg-gray-50 border border-gray-200 rounded-xl flex flex-col h-[calc(100vh-220px)] sticky top-6 overflow-hidden">
                    <div class="p-3 border-b border-gray-200 bg-gray-100 flex justify-between items-center">
                        <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest">Attente Transport</h3>
                        <span class="text-xs font-bold bg-white border border-gray-200 px-1.5 rounded-full text-gray-600" x-text="unassignedTransport.length"></span>
                    </div>
                    
                    <div wire:ignore id="transport-unassigned" class="flex-1 overflow-y-auto p-2 space-y-2" data-group="transport">
                        <template x-for="p in unassignedTransport" :key="p.id">
                            <div class="bg-white border border-gray-200 p-2 rounded-lg shadow-sm cursor-grab active:cursor-grabbing hover:border-indigo-400 hover:shadow-md transition-all group relative" :data-id="p.id">
                                <div class="flex justify-between items-start gap-2">
                                    <div class="text-xs font-bold text-gray-800 leading-tight w-full" x-text="p.name"></div>
                                    <template x-if="hotelNeededIds.includes(p.id)">
                                        <x-heroicon-s-home class="w-3 h-3 text-indigo-400 shrink-0" />
                                    </template>
                                </div>
                                <div class="mt-1.5 inline-flex items-center gap-1 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100" x-show="getParticipantTimes(p)">
                                     <x-heroicon-s-clock class="w-3 h-3 text-gray-400" />
                                     <span class="text-xs font-mono font-medium text-gray-600" x-text="getParticipantTimes(p)"></span>
                                </div>
                                <div class="flex flex-wrap gap-1 mt-1.5">
                                    <template x-if="getTransportMode(p, 'aller')">
                                        <span class="text-xs font-bold px-1.5 py-0.5 rounded border" 
                                              :class="getTransportMode(p, 'aller') === 'bus' ? 'bg-blue-50 text-blue-700 border-blue-100' : 'bg-gray-50 text-gray-500 border-gray-100'"
                                              x-text="'A: ' + getTransportMode(p, 'aller').substring(0,3)"></span>
                                    </template>
                                    <template x-if="getTransportMode(p, 'retour')">
                                        <span class="text-xs font-bold px-1.5 py-0.5 rounded border" 
                                              :class="getTransportMode(p, 'retour') === 'bus' ? 'bg-orange-50 text-orange-700 border-orange-100' : 'bg-gray-50 text-gray-500 border-gray-100'"
                                              x-text="'R: ' + getTransportMode(p, 'retour').substring(0,3)"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right: Kanban -->
            <div class="lg:col-span-10 flex flex-col gap-8">
                
                <!-- Section 1: Transports -->
                <div class="bg-white border border-gray-200 rounded-xl p-1">
                    <div class="flex items-center justify-between p-3 border-b border-gray-100 mb-2">
                        <div class="flex items-center gap-3">
                            <span class="w-1.5 h-6 bg-blue-500 rounded-full"></span>
                            <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">1. Transports & Véhicules</h3>
                        </div>
                        <div class="flex gap-2">
                             <x-filament::button wire:click="mountAction('auto_dispatch')" color="gray" size="sm" variant="outlined">Auto Distribute</x-filament::button>
                             <x-filament::button wire:click="addVehicle('car')" color="gray" size="sm" variant="outlined" icon="heroicon-m-plus">Voiture</x-filament::button>
                             <x-filament::button wire:click="addVehicle('bus')" color="gray" size="sm" variant="outlined" icon="heroicon-m-plus">Bus</x-filament::button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4 p-3 bg-gray-50/50 min-h-[200px] rounded-lg">
                        <template x-for="(v, index) in (transportPlans[selectedDay] || [])" :key="v.id || index">
                            <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:border-blue-300 transition-all group overflow-hidden flex flex-col">
                                <!-- Card Header -->
                                <div class="px-3 py-2 bg-gray-50 border-b border-gray-100 flex justify-between items-start gap-2">
                                    <div class="flex-1 min-w-0 space-y-1">
                                        <div class="flex items-center gap-2">
                                            <template x-if="v.type === 'bus'">
                                                <div class="p-1 bg-blue-100 text-blue-600 rounded">
                                                    <x-heroicon-s-truck class="w-3.5 h-3.5" />
                                                </div>
                                            </template>
                                            <template x-if="v.type !== 'bus'">
                                                <div class="p-1 bg-gray-100 text-gray-500 rounded">
                                                    <x-heroicon-s-users class="w-3.5 h-3.5" />
                                                </div>
                                            </template>
                                            <input type="text" x-model="v.name" class="p-0 border-none bg-transparent text-xs font-black uppercase text-gray-800 focus:ring-0 w-full truncate placeholder-gray-300" placeholder="NOM VÉHICULE">
                                        </div>
                                        <input type="text" x-model="v.driver" class="block w-full p-0 border-none bg-transparent text-xs font-medium text-gray-500 italic focus:ring-0 placeholder-gray-300" placeholder="Nom du chauffeur...">
                                        
                                        <!-- Vehicle specific alerts -->
                                        <div class="flex flex-col gap-1 mt-1">
                                            <template x-for="alert in (alerts[index] || [])">
                                                <div class="flex items-center gap-1.5 px-2 py-1 rounded text-[10px] font-bold border"
                                                     :class="alert.type === 'danger' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-orange-50 text-orange-600 border-orange-100'">
                                                    <x-heroicon-s-exclamation-triangle class="w-3 h-3 shrink-0" />
                                                    <span x-text="alert.msg"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <button x-on:click="removeVehicle(index)" class="text-gray-400 hover:text-red-600 p-1 hover:bg-red-50 rounded transition-colors" title="Supprimer">
                                            <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                                        </button>
                                        <div class="flex items-center bg-white border border-gray-200 rounded px-1.5 py-0.5">
                                            <span class="text-xs font-bold" :class="(v.passengers || []).length > (v.capacity || 0) ? 'text-red-600' : 'text-gray-700'" x-text="(v.passengers || []).length"></span>
                                            <span class="text-xs text-gray-300 mx-0.5">/</span>
                                            <input type="number" x-model.number="v.capacity" class="w-6 p-0 border-none bg-transparent text-xs font-bold text-gray-500 focus:ring-0 text-center">
                                        </div>
                                    </div>
                                </div>

                                <!-- Time & Location Inputs -->
                                <div class="px-3 py-2 grid grid-cols-2 gap-2 border-b border-gray-50">
                                    <div class="bg-gray-50 rounded px-2 py-1 border border-gray-100 focus-within:border-blue-300 focus-within:bg-white transition-colors">
                                        <label class="text-xs font-black text-gray-400 uppercase block">Départ</label>
                                        <input type="time" :value="getTimeFromDatetime(v.departure_datetime)" x-on:change="v.departure_datetime = selectedDay + ' ' + $event.target.value + ':00'" class="w-full p-0 border-none bg-transparent text-xs font-bold text-gray-700 focus:ring-0 h-4">
                                    </div>
                                    <div class="bg-gray-50 rounded px-2 py-1 border border-gray-100 focus-within:border-blue-300 focus-within:bg-white transition-colors">
                                        <label class="text-xs font-black text-gray-400 uppercase block">Lieu</label>
                                        <input type="text" x-model="v.departure_location" class="w-full p-0 border-none bg-transparent text-xs font-bold text-gray-700 focus:ring-0 h-4 placeholder-gray-300" placeholder="Ex: Stade">
                                    </div>
                                </div>

                                <!-- Passengers Drop Zone -->
                                <div class="flex-1 bg-gray-50/30 p-2">
                                    <div wire:ignore
                                         class="min-h-[60px] space-y-1 rounded border-2 border-dashed border-transparent transition-colors" 
                                         :class="(v.passengers || []).length === 0 ? 'border-gray-200 bg-gray-50 flex items-center justify-center' : ''"
                                         :id="'v-list-' + index" 
                                         data-group="transport" 
                                         :data-index="index">
                                        
                                        <template x-if="(v.passengers || []).length === 0">
                                            <span class="text-xs text-gray-300 font-medium select-none pointer-events-none">Glisser ici</span>
                                        </template>

                                        <template x-for="pId in v.passengers" :key="pId">
                                            <div class="flex items-center justify-between px-2 py-1.5 bg-white border border-gray-200 rounded shadow-sm group/p hover:border-blue-300 cursor-grab active:cursor-grabbing" :data-id="pId">
                                                <div class="flex items-center gap-2 overflow-hidden">
                                                    <div class="w-1 h-3 rounded-full bg-blue-400 shrink-0"></div>
                                                    <span class="text-xs font-bold text-gray-700 truncate" x-text="participantsMap[pId] ? participantsMap[pId].name : 'Inconnu'"></span>
                                                </div>
                                                <template x-if="hotelNeededIds.includes(pId)">
                                                    <x-heroicon-s-home class="w-3 h-3 text-indigo-400" />
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                
                                <div class="p-2 border-t border-gray-100 bg-gray-50">
                                    <input type="text" x-model="v.note" class="w-full p-1.5 border border-gray-200 rounded text-xs text-gray-600 focus:border-blue-400 focus:ring-0" placeholder="Note interne...">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>


                <!-- Section 2: Hébergement -->
                <div class="bg-white border border-gray-200 rounded-xl p-1">
                     <div class="flex items-center justify-between p-3 border-b border-gray-100 mb-2">
                         <div class="flex items-center gap-3">
                             <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span>
                             <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">2. Hébergement & Chambres</h3>
                         </div>
                         <x-filament::button wire:click="addRoom" color="gray" size="sm" variant="outlined" icon="heroicon-m-plus">Ajouter Chambre</x-filament::button>
                     </div>

                     <div class="grid grid-cols-1 lg:grid-cols-6 gap-6 p-3">
                        <!-- HOUSING WAITING LIST -->
                        <div class="lg:col-span-1 bg-indigo-50 border border-indigo-100 rounded-xl flex flex-col h-[350px] overflow-hidden">
                            <div class="p-3 border-b border-indigo-100 bg-indigo-50/50 flex justify-between items-center">
                                <h3 class="text-xs font-black text-indigo-400 uppercase tracking-widest">En Attente</h3>
                                <span class="text-xs font-bold bg-white text-indigo-600 px-2 py-0.5 rounded-full border border-indigo-100" x-text="unassignedStay.length"></span>
                            </div>
                            <div wire:ignore id="stay-unassigned" class="flex-1 overflow-y-auto p-2 space-y-2" data-group="stay">
                                <template x-for="p in unassignedStay" :key="p.id">
                                    <div class="bg-white border border-indigo-100 p-2.5 rounded-lg shadow-sm cursor-grab active:cursor-grabbing hover:border-indigo-300 hover:shadow" :data-id="p.id">
                                        <div class="text-xs font-bold text-gray-700 truncate" x-text="p.name"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- ROOMS GRID -->
                        <div class="lg:col-span-5 grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-4">
                            <template x-for="(r, index) in (stayPlans[selectedDay] || [])" :key="r.id || index">
                                <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:border-indigo-300 transition-all flex flex-col">
                                    <div class="px-3 py-2 bg-indigo-50/30 border-b border-indigo-50 flex justify-between items-center">
                                        <div class="flex items-center gap-2 flex-1">
                                            <div class="text-indigo-500">
                                                <x-heroicon-s-home class="w-3.5 h-3.5" />
                                            </div>
                                            <input type="text" x-model="r.name" class="p-0 border-none bg-transparent text-xs font-black uppercase text-indigo-900 focus:ring-0 w-full placeholder-indigo-300" placeholder="NOM CHAMBRE">
                                        </div>
                                        <button x-on:click="removeRoom(index)" class="text-gray-300 hover:text-red-500 p-1 rounded hover:bg-red-50 transition-colors">
                                            <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                    
                                    <div class="flex-1 p-2">
                                        <div wire:ignore
                                             class="min-h-[80px] space-y-1 rounded border-2 border-dashed border-transparent transition-all"
                                             :class="(r.occupant_ids || []).length === 0 ? 'border-indigo-100 bg-indigo-50/10 flex items-center justify-center' : ''"
                                             :id="'r-list-' + index" 
                                             data-group="stay" 
                                             :data-index="index">
                                             
                                            <template x-if="(r.occupant_ids || []).length === 0">
                                                <span class="text-xs text-indigo-200 font-medium select-none pointer-events-none">Glisser occupants</span>
                                            </template>

                                            <template x-for="pId in (r.occupant_ids || [])" :key="pId">
                                                <div class="flex items-center justify-between px-2 py-1.5 bg-indigo-50 border border-indigo-100 rounded text-indigo-900 cursor-grab active:cursor-grabbing hover:bg-white hover:shadow-sm transition-all" :data-id="pId">
                                                    <span class="text-xs font-bold truncate" x-text="participantsMap[pId] ? participantsMap[pId].name : '?'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    <div class="p-2 border-t border-gray-50">
                                        <textarea x-model="r.note" rows="1" class="w-full p-1.5 bg-gray-50 border border-gray-100 rounded text-xs text-gray-600 focus:border-indigo-300 focus:ring-0 resize-none" placeholder="Note chambre..."></textarea>
                                    </div>
                                </div>
                            </template>
                        </div>
                     </div>
                </div>
            </div>
        </div>

        <!-- Floating Alerts -->
        <template x-if="globalAlerts.length > 0">
            <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 max-w-sm pointer-events-none">
                <template x-for="alert in globalAlerts" :key="alert.msg">
                    <div x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="translate-x-full opacity-0"
                         x-transition:enter-end="translate-x-0 opacity-100"
                         x-transition:leave="transition ease-in duration-200 transform"
                         x-transition:leave-start="translate-x-0 opacity-100"
                         x-transition:leave-end="translate-x-full opacity-0"
                         class="bg-white border-l-4 shadow-lg rounded-r p-3 flex items-start gap-3 pointer-events-auto"
                         :class="alert.type === 'danger' ? 'border-red-500' : 'border-orange-500'">
                        <div class="shrink-0" :class="alert.type === 'danger' ? 'text-red-500' : 'text-orange-500'">
                            <x-heroicon-s-exclamation-triangle class="w-5 h-5" />
                        </div>
                        <div class="text-xs font-medium text-gray-700" x-text="alert.msg"></div>
                    </div>
                </template>
            </div>
        </template>
    </div>

    <x-filament-actions::modals />

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script>
        function transportBoard(config) {
            return {
                transportPlans: config.transportPlans,
                stayPlans: config.stayPlans,
                unassignedTransport: config.unassignedTransport,
                unassignedStay: config.unassignedStay,
                participantsMap: config.participantsMap,
                hotelNeededIds: config.hotelNeededIds,
                globalAlerts: config.globalAlerts || [],
                alerts: config.alerts || {},
                selectedDay: config.selectedDay,
                days: config.days,

                init() {
                    this.setupSortables();
                    // Sync unassigned when they change via entangle
                    this.$watch('unassignedTransport', () => this.$nextTick(() => this.setupSortables()));
                    this.$watch('unassignedStay', () => this.$nextTick(() => this.setupSortables()));
                },

                setupSortables() {
                    const containers = document.querySelectorAll('[data-group]');
                    containers.forEach(el => {
                        if (el.sortableInstance) el.sortableInstance.destroy();
                        
                        el.sortableInstance = new Sortable(el, {
                            group: el.getAttribute('data-group'),
                            animation: 100,
                            delay: 0,
                            delayOnTouchOnly: true,
                            touchStartThreshold: 3, 
                            ghostClass: 'opacity-50',
                            dragClass: 'opacity-100',
                            onEnd: (evt) => {
                                this.handleSort(evt);
                            }
                        });
                    });
                },

                handleSort(evt) {
                    const fromGroup = evt.from.getAttribute('data-group');
                    const toGroup = evt.to.getAttribute('data-group');
                    if (fromGroup !== toGroup) return; 

                    const pId = evt.item.getAttribute('data-id');
                    const fromIdx = evt.from.getAttribute('data-index'); 
                    const toIdx = evt.to.getAttribute('data-index');

                    // 1. Update logic for Transport
                    if (fromGroup === 'transport') {
                        if (!this.transportPlans[this.selectedDay]) this.transportPlans[this.selectedDay] = [];

                        // Remove from source
                        if (fromIdx !== null) {
                            this.transportPlans[this.selectedDay][fromIdx].passengers = this.transportPlans[this.selectedDay][fromIdx].passengers.filter(id => id != pId);
                        } else {
                            this.unassignedTransport = this.unassignedTransport.filter(p => p.id != pId);
                        }

                        // Add to destination
                        if (toIdx !== null) {
                             const newOrder = Array.from(evt.to.querySelectorAll('[data-id]')).map(el => el.getAttribute('data-id'));
                             this.transportPlans[this.selectedDay][toIdx].passengers = newOrder;
                        } else {
                            const pObj = this.participantsMap[pId];
                            if (pObj && !this.unassignedTransport.find(p => p.id == pId)) {
                                this.unassignedTransport.push(pObj);
                            }
                        }
                    } 
                    // 2. Update logic for Stay
                    else if (fromGroup === 'stay') {
                        if (!this.stayPlans[this.selectedDay]) this.stayPlans[this.selectedDay] = [];

                        if (fromIdx !== null) {
                            this.stayPlans[this.selectedDay][fromIdx].occupant_ids = this.stayPlans[this.selectedDay][fromIdx].occupant_ids.filter(id => id != pId);
                        } else {
                            this.unassignedStay = this.unassignedStay.filter(p => p.id != pId);
                        }

                        if (toIdx !== null) {
                             const newOrder = Array.from(evt.to.querySelectorAll('[data-id]')).map(el => el.getAttribute('data-id'));
                             this.stayPlans[this.selectedDay][toIdx].occupant_ids = newOrder;
                        } else {
                            const pObj = this.participantsMap[pId];
                            if (pObj && !this.unassignedStay.find(p => p.id == pId)) {
                                this.unassignedStay.push(pObj);
                            }
                        }
                    }

                    // Force Alpine to re-render the lists if Sortable moved the DOM
                    this.$nextTick(() => this.setupSortables());
                },

                removeVehicle(index) {
                    if (confirm('Supprimer ce véhicule ?')) {
                        this.$wire.removeVehicle(index);
                    }
                },

                removeRoom(index) {
                    if (confirm('Supprimer cette chambre ?')) {
                        this.$wire.removeRoom(index);
                    }
                },

                saveAll() {
                    // All plans are already entangled, but we call save to persist to DB
                    this.$wire.saveAllPlans(this.transportPlans, this.stayPlans);
                },

                getTimeFromDatetime(dt) {
                    if (!dt) return '';
                    const parts = dt.split(' ');
                    if (parts.length < 2) return '';
                    return parts[1].substring(0, 5);
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = this.days.find(x => x.date === dateStr);
                    return d ? d.label : dateStr;
                },

                getParticipantTimes(p) {
                    const startRaw = p.first_competition_datetime;
                    const endRaw = p.last_competition_datetime;
                    if (!startRaw || !startRaw.includes(' ')) return '';
                    if (!startRaw.startsWith(this.selectedDay)) return '';

                    const start = startRaw.split(' ')[1].substring(0, 5);
                    const end = endRaw && endRaw.includes(' ') ? endRaw.split(' ')[1].substring(0, 5) : '...';
                    return `${start} - ${end}`;
                },
                
                getTransportMode(p, type) {
                    const dayResp = p.survey_response?.responses?.[this.selectedDay];
                    if (!dayResp) return null;
                    return dayResp[type]?.mode;
                }
            }
        }
    </script>
    @endpush
</x-filament-panels::page>
