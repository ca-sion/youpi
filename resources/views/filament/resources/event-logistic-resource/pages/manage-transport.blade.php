<x-filament-panels::page>
    <style>
        .text-11px { font-size: 11px; }
        .text-10px { font-size: 10px; }
        .text-9px { font-size: 9px; }
        .text-8px { font-size: 8px; }
        .text-7px { font-size: 7px; }
        /* Hide number input spinners */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <div
        x-data="transportBoard({
            transportPlans: @entangle('transportPlans'),
            stayPlans: @entangle('stayPlans'),
            unassignedTransport: @entangle('unassignedTransport'),
            unassignedTransportRetour: @entangle('unassignedTransportRetour'),
            independentAller: @entangle('independentAller'),
            independentRetour: @entangle('independentRetour'),
            unassignedStay: @entangle('unassignedStay'),
            participantsMap: @entangle('participantsMap'),
            hotelNeededIds: @entangle('hotelNeededIds'),
            autoHotelIds: @entangle('autoHotelIds'),
            hotelOverrideIds: @entangle('hotelOverrideIds'),
            globalAlerts: @entangle('globalAlerts'),
            alerts: @entangle('alerts'),
            selectedDay: @entangle('selectedDay').live,
            days: @js($days),
            settings: @js($record->settings)
        })"
        x-init="init()"
        x-on:refresh-sortables.window="$nextTick(() => setupSortables())"
        class="space-y-4"
    >
        <!-- Header -->
        <div class="sticky top-0 z-40 flex flex-wrap items-center justify-between gap-3 px-4 py-2 bg-white/95 backdrop-blur-sm border border-gray-200 rounded-xl shadow-sm -mx-2 mb-6">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                    <x-heroicon-o-truck class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-tight">Informations</h2>
                    <div class="flex items-center gap-2">
                        <p class="text-xs text-gray-500 font-medium" x-text="formatDate(selectedDay)"></p>
                        <template x-if="settings.distance_km">
                            <div class="flex items-center gap-2 text-xs bg-gray-100 px-2 py-0.5 rounded-full text-gray-600">
                                <span x-text="settings.distance_km + ' km'"></span>
                                <span class="text-gray-300">|</span>
                                <span x-text="'🚗 ' + Math.round(settings.distance_km / (settings.car_speed || 120) * 60) + 'm'"></span>
                                <span class="text-gray-300">|</span>
                                <span x-text="'🚌 ' + Math.round(settings.distance_km / (settings.bus_speed || 100) * 60) + 'm'"></span>
                            </div>
                        </template>
                    </div>
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

        <div class="flex flex-col gap-8">
                <!-- Section 1: Transports Aller -->
                <div class="bg-blue-50/20 border border-blue-100 rounded-2xl overflow-hidden shadow-sm">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-blue-50 bg-blue-50/30">
                        <div class="flex items-center gap-3">
                            <span class="w-1.5 h-5 bg-blue-500 rounded-full"></span>
                            <h3 class="text-xs font-black text-gray-800 uppercase tracking-widest">1. Transports Aller</h3>
                        </div>
                        <div class="flex gap-2">
                             <x-filament::button wire:click="mountAction('auto_dispatch')" color="gray" size="sm" variant="outlined">Distribuer</x-filament::button>
                             <x-filament::button wire:click="addVehicle('car', 'aller')" color="gray" size="sm" variant="outlined" icon="heroicon-m-plus">Voiture Aller</x-filament::button>
                             <x-filament::button wire:click="addVehicle('bus', 'aller')" color="gray" size="sm" variant="outlined" icon="heroicon-m-plus">Bus Aller</x-filament::button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-6 gap-6 p-4">
                        <!-- ALLER WAITING LIST -->
                        <div class="lg:col-span-1 bg-white border border-blue-100 rounded-xl flex flex-col min-h-[100px] overflow-hidden shadow-sm">
                            <div>
                                <div class="px-3 py-2 border-b border-blue-50 bg-blue-50/30 flex justify-between items-center">
                                    <h3 class="text-10px font-black text-blue-500 uppercase tracking-widest">En Attente</h3>
                                    <span class="text-10px font-bold bg-white text-blue-600 px-1.5 py-0.5 rounded-md border border-blue-100" x-text="unassignedTransport.length"></span>
                                </div>
                                <div wire:ignore id="transport-unassigned" class="overflow-y-auto p-1.5 space-y-1.5" data-group="transport-aller">
                                    <template x-for="p in unassignedTransport" :key="p.id">
                                        <div class="bg-white border border-blue-100 p-2 rounded-lg shadow-sm cursor-grab active:cursor-grabbing hover:border-blue-400 hover:shadow transition-all group relative" :data-id="p.id">
                                            <div class="flex justify-between items-center gap-2">
                                                <div class="min-w-0 flex-1 flex items-center justify-between gap-1.5">
                                                    <div class="text-xs font-bold text-gray-800 truncate" x-text="p.name"></div>
                                                    <div class="flex items-center gap-1 shrink-0" x-show="getParticipantFirstTime(p.id)">
                                                         <span class="text-10px font-mono font-bold text-blue-600 bg-blue-50 px-1 rounded tabular-nums" x-text="getParticipantFirstTime(p.id)"></span>
                                                    </div>
                                                </div>
                                                <template x-if="hotelNeededIds.includes(p.id)">
                                                    <div class="flex items-center gap-1 bg-indigo-50 px-1 py-0.5 rounded border border-indigo-100">
                                                        <template x-if="hotelOverrideIds.includes(p.id)">
                                                            <span title="Manuel" class="text-8px font-black text-indigo-600 uppercase">M</span>
                                                        </template>
                                                        <template x-if="autoHotelIds.includes(p.id)">
                                                            <span title="Suggestion Auto" class="text-8px font-black text-amber-600 uppercase">A</span>
                                                        </template>
                                                        <x-heroicon-s-home class="w-3 h-3 text-indigo-500 shrink-0" />
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- INDEPENDENT ALLER -->
                            <div class="px-3 py-2 bg-blue-50/10">
                                <h4 class="text-9px font-black text-blue-300 uppercase tracking-widest mb-1.5">Propres moyens</h4>
                                <div wire:ignore id="transport-aller-independent" class="space-y-1" data-group="transport-aller">
                                    <template x-for="p in independentAller" :key="p.id">
                                        <div class="bg-white/50 border border-blue-50 p-1.5 rounded shadow-sm opacity-70 hover:opacity-100 cursor-grab active:cursor-grabbing transition-all" :data-id="p.id">
                                            <div class="flex justify-between items-center gap-2">
                                                <span class="text-10px font-bold text-gray-500 truncate" x-text="p.name"></span>
                                                <span class="text-9px font-black px-1 rounded bg-gray-100 text-gray-400 uppercase" x-text="getTransportMode(p, 'aller')"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- ALLER VEHICLES GRID -->
                        <div class="lg:col-span-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                        <template x-for="(v, index) in (transportPlans[selectedDay] || [])" :key="v.id || index">
                            <template x-if="(v.flow || 'aller') === 'aller'">
                                <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:border-blue-300 transition-all group overflow-hidden flex flex-col">
                                    <div class="px-2.5 py-1.5 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center gap-2">
                                        <div class="flex-1 min-w-0 flex items-center gap-2">
                                            <template x-if="v.type === 'bus'">
                                                <div class="p-1 bg-blue-500 text-white rounded-md shadow-sm">
                                                    <x-heroicon-s-truck class="w-3.5 h-3.5" />
                                                </div>
                                            </template>
                                            <template x-if="v.type !== 'bus'">
                                                <div class="p-1 bg-slate-600 text-white rounded-md shadow-sm">
                                                    <x-heroicon-s-users class="w-3.5 h-3.5" />
                                                </div>
                                            </template>
                                            <div class="flex-1 min-w-0">
                                                <input type="text" x-model="v.name" class="p-0 border-none bg-transparent text-11px font-black uppercase text-gray-900 focus:ring-0 w-full truncate placeholder-gray-300 tracking-tight" placeholder="NOM VÉHICULE">
                                                <input type="text" x-model="v.driver" class="block w-full p-0 border-none bg-transparent text-10px font-medium text-gray-500 italic focus:ring-0 placeholder-gray-300 -mt-1" placeholder="Chauffeur...">
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <div class="flex items-center bg-white border border-gray-200 rounded-lg px-2 py-0.5 shadow-sm">
                                                <span class="text-11px font-bold" :class="(v.passengers || []).length > (v.capacity || 0) ? 'text-red-500' : 'text-gray-700'" x-text="(v.passengers || []).length"></span>
                                                <span class="text-10px text-gray-300 mx-0.5">/</span>
                                                <input type="number" x-model.number="v.capacity" class="w-7 p-0 border-none bg-transparent text-11px font-bold text-gray-400 focus:ring-0 text-center">
                                            </div>
                                            <button x-on:click="removeVehicle(index)" class="text-gray-300 hover:text-red-500 p-1 hover:bg-red-50 rounded-lg transition-colors">
                                                <x-heroicon-m-x-mark class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>

                                    <div class="px-2.5 py-1.5 grid grid-cols-2 gap-2 bg-white/50 border-b border-gray-50">
                                        <div class="bg-gray-50/50 rounded-lg px-2 py-0.5 border border-gray-100 focus-within:border-blue-300 focus-within:bg-white transition-all shadow-sm">
                                            <label class="text-7px font-black text-gray-400 uppercase leading-none block">Départ</label>
                                            <input type="time" :value="getTimeFromDatetime(v.departure_datetime)" x-on:change="v.departure_datetime = selectedDay + ' ' + $event.target.value + ':00'" class="w-full p-0 border-none bg-transparent text-11px font-bold text-gray-700 focus:ring-0 h-4">
                                        </div>
                                        <div class="bg-gray-50/50 rounded-lg px-2 py-0.5 border border-gray-100 focus-within:border-blue-300 focus-within:bg-white transition-all shadow-sm">
                                            <label class="text-7px font-black text-gray-400 uppercase leading-none block">Lieu</label>
                                            <input type="text" x-model="v.departure_location" class="w-full p-0 border-none bg-transparent text-11px font-bold text-gray-700 focus:ring-0 h-4 placeholder-gray-300" placeholder="Lieu...">
                                        </div>
                                    </div>

                                    <template x-if="getArrivalTime(v)">
                                        <div class="px-3 py-1 bg-indigo-50/40 text-9px font-black text-indigo-600 flex items-center justify-center gap-1.5 border-b border-indigo-50/50">
                                            <x-heroicon-s-clock class="w-3 h-3" />
                                            <span>ARRIVÉE ESTIMÉE <span class="bg-indigo-100 px-1 rounded" x-text="getArrivalTime(v)"></span></span>
                                        </div>
                                    </template>

                                    <div class="flex flex-col px-2 pt-1 gap-1">
                                        <template x-for="alert in (alerts[index] || [])">
                                            <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md text-9px font-bold border"
                                                 :class="alert.type === 'danger' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-orange-50 text-orange-600 border-orange-100'">
                                                <x-heroicon-s-exclamation-triangle class="w-2.5 h-2.5 shrink-0" />
                                                <span x-text="alert.msg"></span>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="flex-1 bg-gray-50/40 p-1.5 min-h-[50px]">
                                        <div wire:ignore
                                             class="min-h-[40px] space-y-1 rounded-lg border-2 border-dashed border-transparent transition-colors" 
                                             :class="(v.passengers || []).length === 0 ? 'border-blue-100 bg-blue-50/20 flex flex-col items-center justify-center' : ''"
                                             :id="'v-list-' + index" 
                                             data-group="transport-aller" 
                                             :data-index="index">
                                            <template x-if="(v.passengers || []).length === 0">
                                                <div class="flex flex-col items-center opacity-40">
                                                    <x-heroicon-o-plus-circle class="w-4 h-4 text-blue-400" />
                                                    <span class="text-9px text-blue-400 font-black uppercase tracking-tighter">Déposer</span>
                                                </div>
                                            </template>
                                            <template x-for="pId in v.passengers" :key="pId">
                                                <div class="bg-white border border-gray-200 rounded px-1.5 py-1 shadow-sm group/p hover:border-blue-400 cursor-grab active:cursor-grabbing overflow-hidden flex" :data-id="pId">
                                                    <div class="flex items-center justify-between w-full gap-2">
                                                        <div class="flex items-center gap-1.5 overflow-hidden">
                                                            <div class="w-0.5 h-3 rounded-full bg-blue-400 shrink-0"></div>
                                                            <span class="text-10px font-bold text-gray-700 truncate" x-text="participantsMap[pId] ? participantsMap[pId].name : '?'"></span>
                                                        </div>
                                                         <div class="flex items-center gap-1 shrink-0">
                                                            <template x-if="getParticipantFirstTime(pId)">
                                                                <span class="text-9px font-mono font-bold text-blue-600 bg-blue-50 px-1 rounded tabular-nums" x-text="getParticipantFirstTime(pId)"></span>
                                                            </template>
                                                            <template x-if="hotelNeededIds.includes(pId)">
                                                                <div class="flex gap-0.5 items-center px-1 bg-indigo-50 rounded border border-indigo-100">
                                                                     <template x-if="hotelOverrideIds.includes(pId)">
                                                                        <span title="Manuel" class="text-7px font-black text-indigo-600">M</span>
                                                                    </template>
                                                                    <template x-if="autoHotelIds.includes(pId)">
                                                                        <span title="Suggestion Auto" class="text-7px font-black text-amber-600">A</span>
                                                                    </template>
                                                                    <x-heroicon-s-home class="w-2.5 h-2.5 text-indigo-400 shrink-0" />
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="px-2 py-1.5 border-t border-gray-100 bg-gray-50/50">
                                        <input type="text" x-model="v.note" class="w-full px-2 py-1 border border-gray-200 rounded-md text-10px text-gray-600 focus:border-blue-400 focus:ring-1 focus:ring-blue-100 placeholder-gray-300" placeholder="Note interne...">
                                    </div>
                                </div>
                            </template>
                        </template>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Transports Retour -->
                <div class="bg-orange-50/20 border border-orange-100 rounded-2xl overflow-hidden shadow-sm">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-orange-50 bg-orange-50/30">
                        <div class="flex items-center gap-3">
                            <span class="w-1.5 h-5 bg-orange-500 rounded-full"></span>
                            <h3 class="text-xs font-black text-gray-800 uppercase tracking-widest">2. Transports Retour</h3>
                        </div>
                        <div class="flex gap-2">
                             <x-filament::button wire:click="addVehicle('car', 'retour')" color="gray" size="sm" variant="outlined" icon="heroicon-m-plus">Voiture Retour</x-filament::button>
                             <x-filament::button wire:click="addVehicle('bus', 'retour')" color="gray" size="sm" variant="outlined" icon="heroicon-m-plus">Bus Retour</x-filament::button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-6 gap-6 p-4">
                        <!-- RETOUR WAITING LIST -->
                        <div class="lg:col-span-1 bg-white border border-orange-100 rounded-xl flex flex-col min-h-[100px] overflow-hidden shadow-sm">
                            <div>
                                <div class="px-3 py-2 border-b border-orange-50 bg-orange-50/30 flex justify-between items-center">
                                    <h3 class="text-10px font-black text-orange-500 uppercase tracking-widest">En Attente</h3>
                                    <span class="text-10px font-bold bg-white text-orange-600 px-1.5 py-0.5 rounded-md border border-orange-100" x-text="unassignedTransportRetour.length"></span>
                                </div>
                                <div wire:ignore id="transport-retour-unassigned" class="overflow-y-auto p-1.5 space-y-1.5" data-group="transport-retour">
                                    <template x-for="p in unassignedTransportRetour" :key="p.id">
                                        <div class="bg-white border border-orange-100 p-2 rounded-lg shadow-sm cursor-grab active:cursor-grabbing hover:border-orange-400 hover:shadow transition-all group relative" :data-id="p.id">
                                            <div class="flex justify-between items-center gap-2">
                                                <div class="min-w-0 flex-1 flex items-center justify-between gap-1.5">
                                                    <div class="text-xs font-bold text-gray-800 truncate" x-text="p.name"></div>
                                                    <div class="flex items-center gap-1 shrink-0" x-show="getParticipantLastTime(p.id)">
                                                         <span class="text-10px font-mono font-bold text-orange-600 bg-orange-50 px-1 rounded tabular-nums" x-text="getParticipantLastTime(p.id)"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- INDEPENDENT RETOUR -->
                            <div class="px-3 py-2 bg-orange-50/10">
                                <h4 class="text-9px font-black text-orange-300 uppercase tracking-widest mb-1.5">Propres moyens</h4>
                                <div wire:ignore id="transport-retour-independent" class="space-y-1" data-group="transport-retour">
                                    <template x-for="p in independentRetour" :key="p.id">
                                        <div class="bg-white/50 border border-orange-50 p-1.5 rounded shadow-sm opacity-70 hover:opacity-100 cursor-grab active:cursor-grabbing transition-all" :data-id="p.id">
                                            <div class="flex justify-between items-center gap-2">
                                                <span class="text-10px font-bold text-gray-500 truncate" x-text="p.name"></span>
                                                <span class="text-9px font-black px-1 rounded bg-gray-100 text-gray-400 uppercase" x-text="getTransportMode(p, 'retour')"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- RETOUR VEHICLES GRID -->
                        <div class="lg:col-span-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                        <template x-for="(v, index) in (transportPlans[selectedDay] || [])" :key="v.id || index">
                            <template x-if="v.flow === 'retour'">
                                <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:border-orange-300 transition-all group overflow-hidden flex flex-col">
                                    <div class="px-2.5 py-1.5 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center gap-2">
                                        <div class="flex-1 min-w-0 flex items-center gap-2">
                                            <template x-if="v.type === 'bus'">
                                                <div class="p-1 bg-orange-500 text-white rounded-md shadow-sm">
                                                    <x-heroicon-s-truck class="w-3.5 h-3.5" />
                                                </div>
                                            </template>
                                            <template x-if="v.type !== 'bus'">
                                                <div class="p-1 bg-slate-600 text-white rounded-md shadow-sm">
                                                    <x-heroicon-s-users class="w-3.5 h-3.5" />
                                                </div>
                                            </template>
                                            <div class="flex-1 min-w-0">
                                                <input type="text" x-model="v.name" class="p-0 border-none bg-transparent text-11px font-black uppercase text-gray-900 focus:ring-0 w-full truncate placeholder-gray-300 tracking-tight" placeholder="NOM VÉHICULE">
                                                <input type="text" x-model="v.driver" class="block w-full p-0 border-none bg-transparent text-10px font-medium text-gray-500 italic focus:ring-0 placeholder-gray-300 -mt-1" placeholder="Chauffeur...">
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <div class="flex items-center bg-white border border-gray-200 rounded-lg px-2 py-0.5 shadow-sm">
                                                <span class="text-11px font-bold" :class="(v.passengers || []).length > (v.capacity || 0) ? 'text-red-500' : 'text-gray-700'" x-text="(v.passengers || []).length"></span>
                                                <span class="text-10px text-gray-300 mx-0.5">/</span>
                                                <input type="number" x-model.number="v.capacity" class="w-7 p-0 border-none bg-transparent text-11px font-bold text-gray-400 focus:ring-0 text-center">
                                            </div>
                                            <button x-on:click="removeVehicle(index)" class="text-gray-300 hover:text-red-500 p-1 hover:bg-red-50 rounded-lg transition-colors">
                                                <x-heroicon-m-x-mark class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>

                                    <div class="px-2.5 py-1.5 grid grid-cols-2 gap-2 bg-white/50 border-b border-gray-50">
                                        <div class="bg-gray-50/50 rounded-lg px-2 py-0.5 border border-gray-100 focus-within:border-orange-300 focus-within:bg-white transition-all shadow-sm">
                                            <label class="text-7px font-black text-gray-400 uppercase leading-none block">Départ</label>
                                            <input type="time" :value="getTimeFromDatetime(v.departure_datetime)" x-on:change="v.departure_datetime = selectedDay + ' ' + $event.target.value + ':00'" class="w-full p-0 border-none bg-transparent text-11px font-bold text-gray-700 focus:ring-0 h-4">
                                        </div>
                                        <div class="bg-gray-50/50 rounded-lg px-2 py-0.5 border border-gray-100 focus-within:border-orange-300 focus-within:bg-white transition-all shadow-sm">
                                            <label class="text-7px font-black text-gray-400 uppercase leading-none block">Lieu</label>
                                            <input type="text" x-model="v.departure_location" class="w-full p-0 border-none bg-transparent text-11px font-bold text-gray-700 focus:ring-0 h-4 placeholder-gray-300" placeholder="Lieu...">
                                        </div>
                                    </div>

                                    <div class="flex flex-col px-2 pt-1 gap-1">
                                        <template x-for="alert in (alerts[index] || [])">
                                            <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md text-9px font-bold border"
                                                 :class="alert.type === 'danger' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-orange-50 text-orange-600 border-orange-100'">
                                                <x-heroicon-s-exclamation-triangle class="w-2.5 h-2.5 shrink-0" />
                                                <span x-text="alert.msg"></span>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="flex-1 bg-gray-50/40 p-1.5 min-h-[50px]">
                                        <div wire:ignore
                                             class="min-h-[40px] space-y-1 rounded-lg border-2 border-dashed border-transparent transition-colors" 
                                             :class="(v.passengers || []).length === 0 ? 'border-orange-100 bg-orange-50/20 flex flex-col items-center justify-center' : ''"
                                             :id="'v-list-' + index" 
                                             data-group="transport-retour" 
                                             :data-index="index">
                                            <template x-if="(v.passengers || []).length === 0">
                                                <div class="flex flex-col items-center opacity-40">
                                                    <x-heroicon-o-plus-circle class="w-4 h-4 text-orange-400" />
                                                    <span class="text-9px text-orange-400 font-black uppercase tracking-tighter">Déposer</span>
                                                </div>
                                            </template>
                                            <template x-for="pId in v.passengers" :key="pId">
                                                <div class="bg-white border border-gray-200 rounded px-1.5 py-1 shadow-sm group/p hover:border-orange-400 cursor-grab active:cursor-grabbing overflow-hidden flex" :data-id="pId">
                                                    <div class="flex items-center justify-between w-full gap-2">
                                                        <div class="flex items-center gap-1.5 overflow-hidden">
                                                            <div class="w-0.5 h-3 rounded-full bg-orange-400 shrink-0"></div>
                                                            <span class="text-10px font-bold text-gray-700 truncate" x-text="participantsMap[pId] ? participantsMap[pId].name : '?'"></span>
                                                        </div>
                                                        <template x-if="getParticipantLastTime(pId)">
                                                            <span class="text-10px font-mono font-bold text-orange-600 bg-orange-50 px-1 rounded tabular-nums" x-text="getParticipantLastTime(pId)"></span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="px-2 py-1.5 border-t border-gray-100 bg-gray-50/50">
                                        <input type="text" x-model="v.note" class="w-full px-2 py-1 border border-gray-200 rounded-md text-10px text-gray-600 focus:border-orange-400 focus:ring-1 focus:ring-orange-100 placeholder-gray-300" placeholder="Note interne...">
                                    </div>
                                </div>
                            </template>
                        </template>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Hébergement -->
                <div class="bg-indigo-50/20 border border-indigo-100 rounded-2xl overflow-hidden shadow-sm" x-show="!isLastDay()">
                     <div class="flex items-center justify-between px-4 py-3 border-b border-indigo-50 bg-indigo-50/30">
                         <div class="flex items-center gap-3">
                             <span class="w-1.5 h-5 bg-indigo-500 rounded-full"></span>
                             <h3 class="text-xs font-black text-gray-800 uppercase tracking-widest">3. Hébergement & Chambres</h3>
                         </div>
                         <x-filament::button wire:click="addRoom" color="gray" size="sm" variant="outlined" icon="heroicon-m-plus">Ajouter Chambre</x-filament::button>
                     </div>

                     <div class="grid grid-cols-1 lg:grid-cols-6 gap-6 p-4">
                        <!-- HOUSING WAITING LIST -->
                        <div class="lg:col-span-1 bg-white border border-indigo-100 rounded-xl flex flex-col min-h-[100px] overflow-hidden shadow-sm">
                            <div class="px-3 py-2 border-b border-indigo-50 bg-indigo-50/30 flex justify-between items-center">
                                <h3 class="text-10px font-black text-indigo-500 uppercase tracking-widest">En Attente</h3>
                                <span class="text-10px font-bold bg-white text-indigo-600 px-1.5 py-0.5 rounded-md border border-indigo-100" x-text="unassignedStay.length"></span>
                            </div>
                            <div wire:ignore id="stay-unassigned" class="flex-1 overflow-y-auto p-1.5 space-y-1.5" data-group="stay">
                                <template x-for="p in unassignedStay" :key="p.id">
                                    <div class="bg-white border border-indigo-100 p-2 rounded-lg shadow-sm cursor-grab active:cursor-grabbing hover:border-indigo-400 hover:shadow transition-all group" :data-id="p.id">
                                        <div class="text-10px font-bold text-gray-700 truncate" x-text="p.name"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- ROOMS GRID -->
                        <div class="lg:col-span-5 grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-4">
                            <template x-for="(r, index) in (stayPlans[selectedDay] || [])" :key="r.id || index">
                                <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:border-indigo-300 transition-all flex flex-col">
                                    <div class="px-2.5 py-1.5 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center gap-2">
                                        <div class="flex items-center gap-2 flex-1 min-w-0">
                                            <div class="p-1 bg-indigo-500 text-white rounded-md shadow-sm">
                                                <x-heroicon-s-home class="w-3.5 h-3.5" />
                                            </div>
                                            <input type="text" x-model="r.name" class="p-0 border-none bg-transparent text-11px font-black uppercase text-indigo-900 focus:ring-0 w-full placeholder-indigo-300 tracking-tight" placeholder="NOM CHAMBRE">
                                        </div>
                                        <button x-on:click="removeRoom(index)" class="text-gray-300 hover:text-red-500 p-1 rounded-lg hover:bg-red-50 transition-colors">
                                            <x-heroicon-m-x-mark class="w-4 h-4" />
                                        </button>
                                    </div>
                                    
                                    <div class="flex-1 p-1.5 bg-gray-50/40 min-h-[60px]">
                                        <div wire:ignore
                                             class="min-h-[50px] space-y-1 rounded-lg border-2 border-dashed border-transparent transition-all"
                                             :class="(r.occupant_ids || []).length === 0 ? 'border-indigo-100 bg-indigo-50/20 flex flex-col items-center justify-center' : ''"
                                             :id="'r-list-' + index" 
                                             data-group="stay" 
                                             :data-index="index">
                                             
                                            <template x-if="(r.occupant_ids || []).length === 0">
                                                <div class="flex flex-col items-center opacity-40">
                                                    <x-heroicon-o-plus-circle class="w-4 h-4 text-indigo-400" />
                                                    <span class="text-9px text-indigo-400 font-black uppercase tracking-tighter">Déposer</span>
                                                </div>
                                            </template>

                                            <template x-for="pId in (r.occupant_ids || [])" :key="pId">
                                                <div class="flex items-center justify-between px-2 py-1 bg-white border border-indigo-100 rounded shadow-sm text-indigo-900 cursor-grab active:cursor-grabbing hover:border-indigo-400 transition-all" :data-id="pId">
                                                    <span class="text-10px font-bold truncate" x-text="participantsMap[pId] ? participantsMap[pId].name : '?'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    <div class="px-2 py-1.5 border-t border-gray-100 bg-white/50">
                                        <textarea x-model="r.note" rows="1" class="w-full px-2 py-1 bg-gray-50/50 border border-gray-100 rounded-md text-10px text-gray-600 focus:border-indigo-300 focus:ring-1 focus:ring-indigo-100 resize-none placeholder-gray-300" placeholder="Note chambre..."></textarea>
                                    </div>
                                </div>
                            </template>
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
                unassignedTransportRetour: config.unassignedTransportRetour,
                independentAller: config.independentAller,
                independentRetour: config.independentRetour,
                unassignedStay: config.unassignedStay,
                participantsMap: config.participantsMap,
                hotelNeededIds: config.hotelNeededIds,
                autoHotelIds: config.autoHotelIds,
                hotelOverrideIds: config.hotelOverrideIds,
                globalAlerts: config.globalAlerts || [],
                alerts: config.alerts || {},
                selectedDay: config.selectedDay,
                days: config.days,
                settings: config.settings,

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
                            ghostClass: 'opacity-30',
                            chosenClass: 'ring-2',
                            dragClass: 'opacity-100',
                            onStart: (evt) => {
                                document.body.classList.add('is-dragging');
                            },
                            onEnd: (evt) => {
                                document.body.classList.remove('is-dragging');
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
                    if (fromGroup === 'transport-aller' || fromGroup === 'transport-retour') {
                        if (!this.transportPlans[this.selectedDay]) this.transportPlans[this.selectedDay] = [];

                        // Remove from source
                        if (fromIdx !== null) {
                            this.transportPlans[this.selectedDay][fromIdx].passengers = this.transportPlans[this.selectedDay][fromIdx].passengers.filter(id => id != pId);
                        } else {
                            if (fromGroup === 'transport-aller') {
                                this.unassignedTransport = this.unassignedTransport.filter(p => p.id != pId);
                                this.independentAller = this.independentAller.filter(p => p.id != pId);
                            } else {
                                this.unassignedTransportRetour = this.unassignedTransportRetour.filter(p => p.id != pId);
                                this.independentRetour = this.independentRetour.filter(p => p.id != pId);
                            }
                        }

                        // Add to destination
                        if (toIdx !== null) {
                             const newOrder = Array.from(evt.to.querySelectorAll('[data-id]')).map(el => el.getAttribute('data-id'));
                             this.transportPlans[this.selectedDay][toIdx].passengers = newOrder;
                        } else {
                            const pObj = this.participantsMap[pId];
                            if (toGroup === 'transport-aller') {
                                if (pObj) {
                                    const mode = this.getTransportMode(pObj, 'aller');
                                    if (mode === 'bus') {
                                        if (!this.unassignedTransport.find(p => p.id == pId)) this.unassignedTransport.push(pObj);
                                    } else {
                                        if (!this.independentAller.find(p => p.id == pId)) this.independentAller.push(pObj);
                                    }
                                }
                            } else {
                                if (pObj) {
                                    const mode = this.getTransportMode(pObj, 'retour');
                                    if (mode === 'bus') {
                                        if (!this.unassignedTransportRetour.find(p => p.id == pId)) this.unassignedTransportRetour.push(pObj);
                                    } else {
                                        if (!this.independentRetour.find(p => p.id == pId)) this.independentRetour.push(pObj);
                                    }
                                }
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
                },

                getArrivalTime(v) {
                    if (!v.departure_datetime || !this.settings.distance_km) return null;
                    const speed = v.type === 'bus' ? (this.settings.bus_speed || 100) : (this.settings.car_speed || 120);
                    const travelMin = (this.settings.distance_km / speed) * 60;
                    
                    const time = this.getTimeFromDatetime(v.departure_datetime);
                    if (!time) return null;
                    
                    const [h, m] = time.split(':').map(Number);
                    let totalMin = h * 60 + m + Math.round(travelMin);
                    
                    const arrH = Math.floor(totalMin / 60) % 24;
                    const arrM = totalMin % 60;
                    return `${String(arrH).padStart(2,'0')}:${String(arrM).padStart(2,'0')}`;
                },

                getParticipantFirstTime(pId) {
                    const p = this.participantsMap[pId];
                    if (!p || !p.first_competition_datetime) return null;
                    if (!p.first_competition_datetime.startsWith(this.selectedDay)) return null;
                    return p.first_competition_datetime.split(' ')[1].substring(0, 5);
                },

                getParticipantLastTime(pId) {
                    const p = this.participantsMap[pId];
                    if (!p || !p.last_competition_datetime) return null;
                    if (!p.last_competition_datetime.startsWith(this.selectedDay)) return null;
                    return p.last_competition_datetime.split(' ')[1].substring(0, 5);
                },

                isLastDay() {
                    if (!this.days || this.days.length === 0) return false;
                    return this.selectedDay === this.days[this.days.length - 1].date;
                }
            }
        }
    </script>
    @endpush
</x-filament-panels::page>
