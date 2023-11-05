<div>
    <div>
        *Aux entraîneurs {{ $event->getAthleteCategories }}*
        <br>
        *{{ $event->name }}* {{ $event->codes }}@if ($event->location)à {{ $event->location }}@endif du {{ $event->starts_at->isoFormat('dddd') }} {{ $event->starts_at->isoFormat('DD.MM.YYYY') }}
        <br>
    </div>

    @if ($event->description)
    <div>
        {!! nl2br($event->description) !!}
        <br>
    </div>
    @endif

    @if ($event->has_qualified)
    <div>
        @if ($event->qualified_type == 'url')
        - Liste des qualifiés : {{ $event->qualified_url }}
        <br>
        @elseif ($event->qualified_type == 'list')
        Les athlète qualifiés sont les suivants :
        <br>
        {!! nl2br($event->qualified_list) !!}
        <br><br>
        @endif
        @if ($event->qualified_already_received)
        {{ $event->qualified_already_received }}
        <br>
        @endif
        <br>
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
        ---- ✅ Liste des inscrits
        <br>
        @if ($event->entrants_type == 'url')
        Merci de contrôler la liste des athlètes inscrits : {{ $event->entrants_url }}
        <br>
        @elseif ($event->entrants_type == 'text')
        {!! nl2br($event->entrants_text) !!}
        <br>
        @endif
        <br>
    </div>
    @endif

    @if ($event->has_provisional_timetable)
    <div>
        ---- 🕔 Horaire provisoire : {{ $event->provisional_timetable_url }}
        <br>
        {{ $event->provisional_timetable_text }}
        <br>
        <br>
    </div>
    @endif

    @if ($event->has_final_timetable)
    <div>
        ---- 🕔 Horaire définitif : {{ $event->final_timetable_url }} @if ($event->final_timetable_text) / {{ $event->final_timetable_text }}@endif
        <br>
    </div>
    @endif

    @if ($event->has_publication)
    <div>
        ---- ℹ️ Informations : {{ $event->publication_url }}
        <br>
    </div>
    @endif

    @if ($event->has_rules)
    <div>
        ---- 📐 Règlement : {{ $event->rules_url }}
        <br>
    </div>
    @endif

    <div>
        <br>
        ---- 🚘 Déplacement
        <br>
        @if (! $event->has_trip)
        Aucun déplacement organisé n'est prévu.
        <br>
        <br>
        @else
        @if ($event->trip_type == 'url')
            Lien : {{ $event->trip_url }}
            <br>
            @elseif ($event->trip_type == 'text')
            {{ $event->trip_text }}
            <br>
        @endif
        <br>
        @endif
    </div>

    @if ($event->has_trainers_presences)
    <div>
        ---- ⏱ Accompagnement/présence
        <br>
        @if ($event->trainers_presences_type == 'table')
        @if ($event->trainersPresences->count() > 0)
            <div>
                @foreach ($event->trainersPresences as $tp)
                @if ($tp->presence)
                - {{ $tp->trainer->name }}@if ($tp->note) · {{ $tp->note }}@endif<br>
                @endif
                @endforeach
            </div>
            <p>Les autres moniteurs sont absents ou n'ont pas donnés réponses.</p>
            @else
            <p>Aucun entraîneur présent</p>
        @endif
        @endif
    </div>
    @endif

    Je reste à disposition si vous avez des questions.<br>
    Michael
    <br>
</div>
