<div class="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Logistique : {{ $event_logistic->event_name }}
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Merci de remplir ce formulaire pour l'organisation des déplacements.
            </p>
        </div>

        <div class="p-6">
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

            <div class="space-y-6">
                <div>
                    <label for="participant" class="block text-sm font-medium text-gray-700">Sélectionnez votre nom (Athlète)</label>
                    <select wire:model.live="participantId" id="participant" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
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
                    <form wire:submit="submit" class="space-y-6 border-t pt-6">
                        
                        <!-- Granular Transport per Day -->
                        <div class="space-y-6">
                            <h3 class="text-base font-medium text-gray-900 border-b pb-2">Mes déplacements</h3>
                            
                            @foreach($days as $day)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 space-y-4" wire:key="day-{{ $day['date'] }}">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-indigo-700">{{ $day['label'] }}</h4>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Aller Section -->
                                        <div class="space-y-2">
                                            <div class="flex items-center space-x-2">
                                                <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                                </svg>
                                                <label class="block text-sm font-semibold text-gray-700">Aller (Départ de Sion)</label>
                                            </div>
                                            <select wire:model.live="responses.{{ $day['date'] }}.aller.mode" class="block w-full pl-3 pr-10 py-2.5 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md bg-white shadow-sm">
                                                <option value="">-- Choisir --</option>
                                                <option value="bus">J'ai besoin d'un transport (Bus du club)</option>
                                                <option value="train">Propres moyens (Train)</option>
                                                <option value="car">Propres moyens (Voiture)</option>
                                                <option value="car_seats">Propres moyens (Voiture) + places dispos</option>
                                                <option value="on_site">Déjà sur place / Pas besoin</option>
                                                <option value="absent">Ne vient pas</option>
                                            </select>

                                            @if(($responses[$day['date']]['aller']['mode'] ?? '') === 'car_seats')
                                                <div class="flex items-center space-x-2 bg-indigo-50 p-2.5 rounded-md border border-indigo-100 mt-2 animate-in fade-in slide-in-from-top-1">
                                                    <label class="text-xs text-indigo-700 font-bold">Places à disposition :</label>
                                                    <input type="number" min="1" wire:model="responses.{{ $day['date'] }}.aller.seats" class="w-20 py-1 text-xs border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nb places">
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Retour Section -->
                                        <div class="space-y-2">
                                            <div class="flex items-center space-x-2">
                                                <svg class="h-4 w-4 text-indigo-500 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                                </svg>
                                                <label class="block text-sm font-semibold text-gray-700">Retour (Retour à Sion)</label>
                                            </div>
                                            <select wire:model.live="responses.{{ $day['date'] }}.retour.mode" class="block w-full pl-3 pr-10 py-2.5 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md bg-white shadow-sm">
                                                <option value="">-- Choisir --</option>
                                                <option value="bus">J'ai besoin d'un transport (Bus du club)</option>
                                                <option value="train">Propres moyens (Train)</option>
                                                <option value="car">Propres moyens (Voiture)</option>
                                                <option value="car_seats">Propres moyens (Voiture) + places dispos</option>
                                                <option value="on_site">Reste sur place / Pas besoin</option>
                                                <option value="absent">Ne vient pas</option>
                                            </select>

                                            @if(($responses[$day['date']]['retour']['mode'] ?? '') === 'car_seats')
                                                <div class="flex items-center space-x-2 bg-indigo-50 p-2.5 rounded-md border border-indigo-100 mt-2 animate-in fade-in slide-in-from-top-1">
                                                    <label class="text-xs text-indigo-700 font-bold">Places à disposition :</label>
                                                    <input type="number" min="1" wire:model="responses.{{ $day['date'] }}.retour.seats" class="w-20 py-1 text-xs border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nb places">
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
                                <p class="text-gray-600 italic">Note : L'attribution des chambres d'hôtel pour les athlètes est gérée directement par l'administration du club selon les horaires de compétition.</p>
                            </div>
                        @endif

                        <div>
                            <label for="remarks" class="block text-sm font-medium text-gray-700">Remarques / Questions</label>
                            <div class="mt-1">
                                <textarea wire:model="remarks" id="remarks" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                            </div>
                        </div>

                        <div class="pt-5">
                            <div class="flex justify-end">
                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Enregistrer mes préférences
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
