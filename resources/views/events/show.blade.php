<x-layouts.app>

<main class="pt-8 pb-16 lg:pt-16 lg:pb-24 bg-white dark:bg-gray-900 antialiased">
    <div class="flex justify-between px-4 mx-auto max-w-screen-xl">
        <article class="mx-auto w-full max-w-2xl format format-sm sm:format-base lg:format-lg format-blue dark:format-invert">
            <header class="mb-4 lg:mb-6 not-format">
                <div class="mb-4 lg:mb-6">
                    <h1 class="text-3xl font-extrabold leading-tight text-gray-900 lg:text-4xl dark:text-white">{{ $event->name }} {{ $event->codes }}</h1>
                    @if (data_get($event, 'status.value') != 'planned')
                    <span class="bg-gray-100 text-gray-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-400 border border-gray-500">{{ $event->status->getLabel() }}</span>
                    @endif
                </div>

                <div class="text-gray-900">
                    <div class="flex">
                        <div class="font-bold min-w-[100px]">Quand</div>
                        <div>{{ $event->starts_at->isoFormat('dddd') }} {{ $event->starts_at->isoFormat('DD.MM.YYYY') }}</div>
                    </div>
                    @if ($event->location)
                    <div class="flex">
                        <div class="font-bold min-w-[100px]">Où</div>
                        <div>{{ $event->location }}</div>
                    </div>
                    @endif
                    <div class="flex">
                        <div class="font-bold min-w-[100px]">Concerne</div>
                        <div>{{ $event->getAthleteCategories }}</div>
                    </div>
                </div>

            </header>
            @if ($event->description)
            <p class="lead text-gray-900">
                {!! nl2br($event->description) !!}
            </p>
            @else
            <p class="lead">
                Le {{ $event->starts_at->isoFormat('dddd') }} {{ $event->starts_at->isoFormat('DD.MM.YYYY') }} a lieu l'événement {{ $event->name }} @if ($event->location)à {{ $event->location }}@endif. Vous trouverez ci-après les informations nécessaires.
            </p>
            @endif

            @if ($event->sections)
            <div>
                @foreach ($event->sections as $section)
                @if (data_get($section, 'type') == 'block')
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 dark:text-gray-300">
                <div class="font-bold min-w-[100px]">{{ data_get($section, 'heading') }}</div>
                @else
                <div class="font-bold min-w-[100px]">{{ data_get($section, 'heading') }}</div>
                @endif

                <div>{!! nl2br(data_get($section, 'content')) !!}</div>

                @if (data_get($section, 'type') == 'block')</div>@endif

                @endforeach
                <br>
            </div>
            @endif

            @if ($event->has_deadline)
                <h2>📝 Inscription</h2>
                <div class="flex">
                    <div class="font-bold min-w-[100px]">Délai</div>
                    <div>
                        @if ($event->deadline_at){{ $event->deadline_at->isoFormat('LLLL') }}@endif
                        @if (! $event->deadline_at && $event->deadline_type == 'tiiva')Délai donné sur Tiiva @endif
                    </div>
                </div>
                <div class="flex">
                    <div class="font-bold min-w-[100px]">Où</div>
                    <div>@if ($event->deadline_type == 'tiiva')sur Tiiva @elseif ($event->deadline_type == 'url'){{ $event->deadline_url }}@elseif ($event->deadline_type == 'text'){{ $event->deadline_text }}@endif</div>
                </div>
                <br>
            @endif

            @if ($event->has_qualified)
            <div>
                @if ($event->qualified_type == 'url')
                <a href="{{ $event->qualified_url }}">Liste des qualifiés</a>
                @elseif ($event->qualified_type == 'list')
                <div>Les athlète qualifiés sont les suivants :</div>
                {!! nl2br($event->qualified_list) !!}
                <br>
                @endif
                @if ($event->qualified_already_received)
                <p>{{ $event->qualified_already_received }}</p>
                @endif
            </div>
            @endif

            @if ($event->has_convocation)
                <div>
                @if ($event->convocation_type == 'text')
                    {{ $event->convocation_text }}
                    <br>
                @endif
                <br>
                </div>
            @endif

            @if ($event->has_entrants)
            <div>
                <h2>✅ Liste des inscrits</h2>
                @if ($event->entrants_type == 'url')
                Merci de contrôler la <a href="{{ $event->entrants_url }}">liste des athlètes inscrits</a>
                <br>
                @elseif ($event->entrants_type == 'text')
                {!! nl2br($event->entrants_text) !!}
                <br>
                @endif
                <br>
            </div>
            @endif

            @if (
                $event->has_provisional_timetable ||
                $event->has_final_timetable ||
                $event->has_publication ||
                $event->has_rules
            )
            <h2>ℹ️ Informations</h2>

                @if ($event->has_provisional_timetable)
                <div>
                    <a href="{{ $event->provisional_timetable_url }}">Horaire provisoire</a>
                    {{ $event->provisional_timetable_text }}
                </div>
                @endif

                @if ($event->has_final_timetable)
                <div>
                    <a href="{{ $event->final_timetable_url }}">Horaire définitif</a>
                    {{ $event->final_timetable_text }}
                </div>
                @endif

                @if ($event->has_publication)
                <div>
                    <a href="{{ $event->publication_url }}">Publication</a>
                </div>
                @endif

                @if ($event->has_rules)
                <div>
                    <a href="{{ $event->rules_url }}">Règlement</a>
                </div>
                @endif
            <br>
            @endif

            @if ($event->has_trip)
            <h2>🚘 Déplacement</h2>
                @if ($event->trip_type == 'url')
                    <a href="{{ $event->trip_url }}">Informations de déplacement</a>
                @elseif ($event->trip_type == 'text')
                    <div>{{ $event->trip_text }}</div>
                @endif
            @endif


            @if ($event->has_trainers_presences && $event->trainers_presences_type == 'table')
            <h2>⏱ Entraîneurs</h2>
            @if ($event->trainersPresences->count() > 0)
            <div class="not-format">
                <ul class="list-disc mt-0">
                @foreach ($event->trainersPresences->sortBy('trainer.name') as $tp)
                @if ($tp->presence)
                <li class="ml-4 mb-0">{{ $tp->trainer->name }}@if ($tp->note) <span class="text-sm">· {{ $tp->note }}</span>@endif</li>
                @endif
                @endforeach
                </ul>
            </div>
            <p>Les autres moniteurs sont absents ou n'ont pas donnés réponses.</p>
            @else
            <p>Aucun entraîneur présent</p>
            @endif
            @endif

        </article>
    </div>
  </main>
</x-layouts.app>
