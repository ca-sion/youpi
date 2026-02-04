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
                    </select>
                </div>

                @if($participantId)
                    <div wire:key="form-container-{{ $participantId }}">
                    <form wire:submit="submit" class="space-y-6 border-t pt-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Presence Aller -->
                            <fieldset>
                                <legend class="text-base font-medium text-gray-900">Présence Aller (Départ Sion)</legend>
                                <div class="mt-4 space-y-4">
                                    @foreach(['Vendredi', 'Samedi', 'Dimanche'] as $day)
                                        <div class="flex items-start" wire:key="aller-{{ $participantId }}-{{ $day }}">
                                            <div class="flex items-center h-5">
                                                <input wire:model="presence_aller" id="aller_{{ $loop->index }}" value="{{ $day }}" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="aller_{{ $loop->index }}" class="font-medium text-gray-700">{{ $day }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </fieldset>

                            <!-- Presence Retour -->
                            <fieldset>
                                <legend class="text-base font-medium text-gray-900">Présence Retour</legend>
                                <div class="mt-4 space-y-4">
                                    @foreach(['Vendredi', 'Samedi', 'Dimanche'] as $day)
                                        <div class="flex items-start" wire:key="retour-{{ $participantId }}-{{ $day }}">
                                            <div class="flex items-center h-5">
                                                <input wire:model="presence_retour" id="retour_{{ $loop->index }}" value="{{ $day }}" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="retour_{{ $loop->index }}" class="font-medium text-gray-700">{{ $day }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </fieldset>
                        </div>

                        <div>
                            <label for="transport_mode" class="block text-sm font-medium text-gray-700">Mode de transport souhaité</label>
                            <select wire:model.live="transport_mode" id="transport_mode" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">-- Choisir --</option>
                                <option value="bus">Bus Club (si organisé)</option>
                                <option value="voiture_parent">Voiture Parent</option>
                                <option value="train">Train</option>
                                <option value="perso">Par ses propres moyens</option>
                            </select>
                        </div>

                        @if($transport_mode === 'voiture_parent')
                            <div>
                                <label for="voiture_seats" class="block text-sm font-medium text-gray-700">Avez-vous des places pour d'autres athlètes ? (Combien ?)</label>
                                <input wire:model="voiture_seats" type="number" min="0" id="voiture_seats" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="0 = Complet">
                            </div>
                        @endif

                        <div class="relative flex items-start">
                            <div class="flex items-center h-5">
                                <input wire:model="hotel_needed" id="hotel_needed" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="hotel_needed" class="font-medium text-gray-700">Besoin d'une chambre d'hôtel ?</label>
                                <p class="text-gray-500">Cochez uniquement si nécessaire.</p>
                            </div>
                        </div>

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
