<div class="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b">
            <h1 class="text-xl leading-6 font-bold">
                Sondage logistique : {{ $event_logistic->name }}
            </h1>
            <div class="mt-2 text-sm space-y-2">
                <p>Pourquoi ce condage ? Il nous aide à organiser les transports (bus du club, covoiturage) de manière efficace pour tout le monde.</p>
                <div class="bg-white/60 rounded p-3 border text-xs sm:text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Athlètes : Sélectionnez votre nom dans la liste.</li>
                        <li>Parents : Sélectionnez le nom de votre enfant dans la liste.</li>
                        <li>Entraîneurs : Sélectionnez votre nom dans la liste.</li>
                        <li><span class="underline">Pas dans la liste</span> ? <span class="text-red-700">Vérifiez bien toute la liste</span> avant d'ajouter une nouvelle personne via l'option ("Je ne suis pas dans la liste") en bas du menu déroulant.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="p-6 bg-indigo-50/20">
            @if($this->is_survey_closed)
                <div class="rounded-md bg-amber-50 border border-amber-200 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9 9a1 1 0 012 0v4a1 1 0 11-2 0V9zm1-5a1 1 0 00-1 1v1a1 1 0 002 0V5a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-bold text-amber-800">Sondage fermé</h3>
                            <p class="text-xs text-amber-700 mt-1">La date limite est dépassée. Les modifications en ligne ne sont plus acceptées. En cas de changement urgent, veuillez contacter l'administration.</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="space-y-6">
                <div>
                    <label for="participant" class="block text-sm font-medium text-gray-700">Sélectionnez votre nom</label>
                    <select wire:model.live="participantId" id="participant" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">-- Choisir dans la liste --</option>
                        @foreach($participants as $p)
                            <option value="{{ $p['id'] }}" wire:key="opt-{{ $p['id'] }}">{{ $p['name'] }}</option>
                        @endforeach
                        <option value="new">-- Je ne suis pas dans la liste --</option>
                    </select>
                </div>

                @if($participantId === 'new')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-blue-50 p-4 rounded-md border border-blue-100">
                        <div>
                            <label for="newName" class="block text-sm font-medium text-blue-900">Votre Nom et Prénom</label>
                            <input wire:model="newName" type="text" id="newName" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Ex: Jean Dupont">
                            @error('newName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex items-center pt-6">
                            <input wire:model="isCoach" id="isCoach" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="isCoach" class="ml-2 block text-sm font-medium text-blue-900">
                                Je suis un entraîneur
                            </label>
                        </div>
                    </div>
                @endif

                @if($participantId)
                    <div wire:key="form-container-{{ $participantId }}">
                    <form wire:submit="submit" class="space-y-6 pt-6">
                        
                        <!-- Granular Transport per Day -->
                        <div class="space-y-6">
                            <h3 class="text-base font-medium text-gray-900 border-b pb-2">Mes déplacements</h3>
                            
                            @foreach($days as $day)
                                <div class="bg-white rounded-lg p-4 border border-gray-200 space-y-4" wire:key="day-{{ $day['date'] }}">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-gray-700">{{ $day['label'] }}</h4>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Aller Section -->
                                        <div class="space-y-2">
                                            <div class="flex items-center space-x-2">
                                                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                                </svg>
                                                <label class="block text-sm font-semibold text-gray-700">Aller (Départ de Sion)</label>
                                            </div>
                                            <select wire:model.live="responses.{{ $day['date'] }}.aller.mode" class="block w-full pl-3 pr-10 py-2.5 text-sm border-gray-300 focus:outline-none focus:ring-gray-500 focus:border-gray-500 rounded-md bg-white shadow-sm">
                                                <option value="">-- Choisir --</option>
                                                <option value="bus">J'ai besoin d'un transport (Bus du club)</option>
                                                <option value="train">Propres moyens (Train)</option>
                                                <option value="car">Propres moyens (Voiture)</option>
                                                <option value="car_seats">Propres moyens (Voiture) + places dispos</option>
                                                <option value="on_site">Déjà sur place / Pas besoin</option>
                                                <option value="absent">Ne vient pas</option>
                                            </select>

                                            @if(($responses[$day['date']]['aller']['mode'] ?? '') === 'car_seats')
                                                <div class="flex items-center space-x-2 bg-gray-50 p-2.5 rounded-md border border-gray-100 mt-2 animate-in fade-in slide-in-from-top-1">
                                                    <label class="text-xs text-gray-700 font-bold">Places à disposition :</label>
                                                    <input type="number" min="1" wire:model="responses.{{ $day['date'] }}.aller.seats" class="w-20 py-1 text-xs border-gray-300 rounded-md focus:ring-gray-500 focus:border-gray-500" placeholder="Nb places">
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Retour Section -->
                                        <div class="space-y-2">
                                            <div class="flex items-center space-x-2">
                                                <svg class="h-4 w-4 text-gray-500 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                                </svg>
                                                <label class="block text-sm font-semibold text-gray-700">Retour (Retour à Sion)</label>
                                            </div>
                                            <select wire:model.live="responses.{{ $day['date'] }}.retour.mode" class="block w-full pl-3 pr-10 py-2.5 text-sm border-gray-300 focus:outline-none focus:ring-gray-500 focus:border-gray-500 rounded-md bg-white shadow-sm">
                                                <option value="">-- Choisir --</option>
                                                <option value="bus">J'ai besoin d'un transport (Bus du club)</option>
                                                <option value="train">Propres moyens (Train)</option>
                                                <option value="car">Propres moyens (Voiture)</option>
                                                <option value="car_seats">Propres moyens (Voiture) + places dispos</option>
                                                <option value="on_site">Reste sur place / Pas besoin</option>
                                                <option value="absent">Ne vient pas</option>
                                            </select>

                                            @if(($responses[$day['date']]['retour']['mode'] ?? '') === 'car_seats')
                                                <div class="flex items-center space-x-2 bg-gray-50 p-2.5 rounded-md border border-gray-100 mt-2 animate-in fade-in slide-in-from-top-1">
                                                    <label class="text-xs text-gray-700 font-bold">Places à disposition :</label>
                                                    <input type="number" min="1" wire:model="responses.{{ $day['date'] }}.retour.seats" class="w-20 py-1 text-xs border-gray-300 rounded-md focus:ring-gray-500 focus:border-gray-500" placeholder="Nb places">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($this->can_request_hotel)
                            <div class="relative flex items-start bg-yellow-50 p-4 rounded-md border border-yellow-200">
                                <div class="flex items-center h-5">
                                    <input wire:model="hotel_needed" id="hotel_needed" type="checkbox" class="focus:ring-yellow-500 h-4 w-4 text-yellow-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="hotel_needed" class="font-medium text-gray-900">Besoin d'une chambre d'hôtel ?</label>
                                    <p class="text-yellow-700 text-xs">Option réservée aux entraîneurs. Pour les athlètes, l'administration gère directement l'attribution.</p>
                                </div>
                            </div>
                        @else
                            <div class="text-sm bg-gray-50 p-4 rounded-md border border-gray-200">
                                <p class="text-gray-600 italic">Note : L'attribution des chambres d'hôtel pour les athlètes et entraîneurs est gérée directement par le club selon les horaires de compétition.</p>
                            </div>
                        @endif

                        <div>
                            <label for="remarks" class="block text-sm font-medium text-gray-700">Remarques, questions ou précisions</label>
                            <p class="text-xs text-gray-500 mb-1">N'hésitez pas à poser vos questions ou donner des détails ici.</p>
                            <div class="mt-1">
                                <textarea wire:model="remarks" id="remarks" rows="3" class="shadow-sm focus:ring-gray-500 focus:border-gray-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Ex: Départ différé, transport uniquement le matin..."></textarea>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-end">
                                <button type="submit" 
                                    @if($this->is_survey_closed) disabled @endif
                                    class="mb-3 ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white {{ $this->is_survey_closed ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Enregistrer
                                </button>
                            </div>
                        </div>
                    </form>
                    </div>
                @endif
            </div>

            @if (session()->has('message'))
                <div class="rounded-md bg-green-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @error('error')
                <div class="rounded-md bg-red-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ $message }}</p>
                        </div>
                    </div>
                </div>
            @enderror

        </div>

        <div class="p-6 border-t">

            <!-- Compact Summary Table -->
            <div>
                <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Récapitulatif des réponses</h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Responded -->
                    <div class="bg-green-50 rounded-lg p-3 border border-green-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-green-800 uppercase">Ont répondu ({{ $this->stats['responded_count'] }})</span>
                            <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="text-[10px] sm:text-xs text-green-700 line-clamp-3 hover:line-clamp-none transition-all cursor-default">
                            {{ implode(', ', $this->stats['responded']) ?: 'Aucune réponse pour le moment' }}
                        </div>
                    </div>

                    <!-- Not Responded -->
                    <div class="bg-red-50 rounded-lg p-3 border border-red-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-red-800 uppercase">Attente de réponse ({{ $this->stats['not_responded_count'] }})</span>
                            <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-[10px] sm:text-xs text-red-700 line-clamp-3 hover:line-clamp-none transition-all cursor-default">
                            {{ implode(', ', $this->stats['not_responded']) ?: 'Tout le monde a répondu !' }}
                        </div>
                    </div>
                </div>
                </div>

                <!-- Detailed Table -->
                <div class="mt-8 overflow-hidden border border-gray-100 rounded-lg shadow-sm">
                    <div class="bg-gray-50 px-3 py-2 border-b border-gray-100 flex justify-between items-center">
                        <h5 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Détail des présences</h5>
                        <div class="flex items-center space-x-2 text-[9px] text-gray-400 italic">
                            <span class="flex items-center">aller</span>
                            <span class="text-gray-300">|</span>
                            <span class="flex items-center">retour</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Nom</th>
                                    @foreach($days as $day)
                                        <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-500 uppercase border-l border-gray-100 min-w-[70px]">
                                            {{ \Carbon\Carbon::parse($day['date'])->translatedFormat('D d') }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($this->stats['responded_full'] as $p)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-3 py-1.5 whitespace-nowrap text-[11px] font-medium text-gray-700">
                                            {{ $p['name'] }}
                                        </td>
                                        @foreach($days as $day)
                                            @php
                                                $resp = $p['survey_response']['responses'][$day['date']] ?? null;
                                                $aller = $resp['aller']['mode'] ?? '-';
                                                $retour = $resp['retour']['mode'] ?? '-';
                                                
                                                $getIcon = function($mode) {
                                                    return match($mode) {
                                                        'bus' => '🚌',
                                                        'train' => '🚂',
                                                        'car', 'car_seats' => '🚗',
                                                        'on_site' => '📍',
                                                        'absent' => '❌',
                                                        default => '-'
                                                    };
                                                };
                                            @endphp
                                            <td class="px-1 py-1.5 border-l border-gray-100">
                                                <div class="flex items-center justify-center space-x-1.5">
                                                    <div class="flex items-center bg-gray-50 px-1 rounded-sm border border-gray-100" title="Aller: {{ $aller }}">
                                                        <span class="text-[10px]">{{ $getIcon($aller) }}</span>
                                                    </div>
                                                    <div class="flex items-center bg-gray-50 px-1 rounded-sm border border-gray-100" title="Retour: {{ $retour }}">
                                                        <span class="text-[10px]">{{ $getIcon($retour) }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-center space-x-4 text-[10px] text-gray-400">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-green-400 rounded-full mr-1"></div>
                        <span>{{ $this->stats['responded_count'] }} validés</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-red-400 rounded-full mr-1"></div>
                        <span>{{ $this->stats['not_responded_count'] }} en attente</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
