<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $event->name }}</title>

    <style>
        @page {
            margin: 1cm 1.5cm 1.5cm 2.5cm;
        }
        #header {
            font-size: xx-small;
            position: fixed;
            top: -30px; height: 30px;
        }
        #footer {
            font-size: xx-small;
            position: fixed;
            bottom: -1cm;
            height: 1cm;
        }
        body {
            font-family: sans-serif;
            font-size: small;
            color:#222222
        }
        .unicode {
            font-family: 'DejaVu Sans', sans-serif;
        }
        .ca-ball {
            display: inline-block;
            width: 25px;
            height: 25px;
            background-color: #D81010;
            border-radius: 100%;
        }
        .ca-ball-title {
            display: inline-block;
            height: 25px;
            font-size: 24px;
            font-weight: bold;
            margin-left: 10px;
            margin-bottom: 3px;
        }
        .ca-table-row td {
            padding-bottom: .5rem;
        }
        .ca-table-heading {
            vertical-align: top;
            width: 30%;
            font-weight: bold;
        }
        .ca-table-content {
            width: 70%;
        }
        .ca-table-content-block {
            margin-bottom: .5rem;
        }
    </style>
</head>
<body>

    <div id="footer">
        <p>Centre athlétique de Sion · Case postale 4057, 1950 Sion 4<br>secretariat@casion.ch · +41 77 505 43 13</p>
    </div>

    <table width="100%">
        <tr>
            <td align="left" style="width: 30%;">
                <div style="margin-left: -42px;">
                    <span class="ca-ball"></span>
                    <span class="ca-ball-title">@if ($event->types){{ $event->types->first()->getLabel() }}@else Information @endif</span>
                </div>
            </td>
            <td align="right" style="width: 70%;">
                <div style="">
                    <img src="https://casion.ch/assets/logo/logo-casion.png" alt="Logo" style="width: 80px;">
                </div>
            </td>
        </tr>
    </table>
    <table width="100%" style="margin-top: 2rem;">
        <tr>
            <td align="left" style="width: 30%;">
                 
            </td>
            <td align="left" style="width: 70%;">
                <div style="font-size: 24px;font-weight: bold;margin-bottom: 4px;">{{ $event->name }}</div>
                <div style="font-size: 16px;">{{ $event->starts_at->isoFormat('LL') }}</div>
                @if ($event->location)
                <div style="font-size: 16px;">{{ $event->location }}</div>
                @endif
                @if ($event->description)
                <div style="margin-top: 1rem;">{!! nl2br($event->description) !!}</div>
                @endif
            </td>
        </tr>
    </table>

    <table width="100%" style="margin-top: 2rem;">
        <tr class="ca-table-row">
            <td align="left" class="ca-table-heading">
                Date et lieu
            </td>
            <td align="left" class=ca-table-content">
                {{ $event->starts_at->isoFormat('dddd') }} {{ $event->starts_at->isoFormat('DD MMMM YYYY') }}
                @if ($event->location) · {{ $event->location }}@endif
            </td>
        </tr>
        @if ($event->getAthleteCategories)
        <tr class="ca-table-row">
            <td align="left" class="ca-table-heading">
                Catégories
            </td>
            <td align="left" class="ca-table-content">
                {{ $event->getAthleteCategories }}
            </td>
        </tr>
        @endif
        @if ($event->has_deadline)
        <tr class="ca-table-row">
            <td align="left" class="ca-table-heading">
                Inscription
            </td>
            <td align="left" class="ca-table-content">
                @if ($event->deadline_at){{ $event->deadline_at->isoFormat('LLLL') }}@endif
                @if ($event->deadline_at && $event->deadline_type == 'tiiva') · @endif
                @if ($event->deadline_type == 'tiiva')Délai donné sur Tiiva @endif

                @if ($event->deadline_type == 'url')<br>Sur <a href="{{ $event->deadline_url }}">{{ $event->deadline_url }}<i class="unicode"> ➝</i></a>@elseif ($event->deadline_type == 'text')<br>{{ $event->deadline_text }}@endif
            </td>
        </tr>
        @endif
        @if ($event->has_qualified)
        <tr class="ca-table-row">
            <td align="left" class="ca-table-heading">
                Qualifiés
            </td>
            <td align="left" class="ca-table-content">
                @if ($event->qualified_type == 'url')
                <a href="{{ $event->qualified_url }}">Liste des qualifiés<i class="unicode"> ➝</i></a>
                @elseif ($event->qualified_type == 'list')
                <div>Les athlète qualifiés sont les suivants :</div>
                {!! nl2br($event->qualified_list) !!}
                <br>
                @endif
                @if ($event->qualified_already_received)
                <p>{{ $event->qualified_already_received }}</p>
                @endif
            </td>
        </tr>
        @endif
        @if ($event->has_convocation)
        <tr class="ca-table-row">
            <td align="left" class="ca-table-heading">
                Convocation
            </td>
            <td align="left" class="ca-table-content">
                @if ($event->convocation_type == 'text')
                    {{ $event->convocation_text }}
                @endif
            </td>
        </tr>
        @endif
        @if ($event->has_entrants)
        <tr class="ca-table-row">
            <td align="left" class="ca-table-heading">
                Liste des inscrits
            </td>
            <td align="left" class="ca-table-content">
                @if ($event->entrants_type == 'url')
                Merci de contrôler la <a href="{{ $event->entrants_url }}">liste des athlètes inscrits<i class="unicode"> ➝</i></a>
                @elseif ($event->entrants_type == 'text')
                {!! nl2br($event->entrants_text) !!}
                @endif
            </td>
        </tr>
        @endif
        @if (
                $event->has_provisional_timetable ||
                $event->has_final_timetable ||
                $event->has_publication ||
                $event->has_rules
            )

        <tr class="ca-table-row">
            <td align="left" class="ca-table-heading">
                Informations
            </td>
            <td align="left" class="ca-table-content">
                @if ($event->has_provisional_timetable)
                <div class="ca-table-content-block">
                    <a href="{{ $event->provisional_timetable_url }}">Horaire provisoire<i class="unicode"> ➝</i></a>
                    {{ $event->provisional_timetable_text }}
                </div>
                @endif

                @if ($event->has_final_timetable)
                <div class="ca-table-content-block">
                    <a href="{{ $event->final_timetable_url }}">Horaire définitif<i class="unicode"> ➝</i></a>
                    {{ $event->final_timetable_text }}
                </div>
                @endif

                @if ($event->has_publication)
                <div class="ca-table-content-block">
                    <a href="{{ $event->publication_url }}">Publication<i class="unicode"> ➝</i></a>
                </div>
                @endif

                @if ($event->has_rules)
                <div class="ca-table-content-block">
                    <a href="{{ $event->rules_url }}">Règlement<i class="unicode"> ➝</i></a>
                </div>
                @endif
            </td>
        </tr>
        @endif
        @if ($event->has_trainers_presences && $event->trainers_presences_type == 'table')
        <tr class="ca-table-row">
            <td align="left" class="ca-table-heading">
                Entraîneurs
            </td>
            <td align="left" class="ca-table-content">
                <div>Présences :</div>
                <ul style="margin-top: 0;">
                @foreach ($event->trainersPresences as $tp)
                @if ($tp->presence)
                   <li>{{ $tp->trainer->name }}@if ($tp->note) <span style="font-size: x-small;">· {{ $tp->note }}</span>@endif</li>
                @endif
                @endforeach
                </ul>
                <p>Les autres moniteurs sont absents ou n'ont pas donnés réponses.</p>
            </td>
        </tr>
        @endif

    </table>

    <script type="text/php">
        if ( isset($pdf) ) {
            $x = 540;
            $y = 808;
            $text = "{PAGE_NUM}/{PAGE_COUNT}";
            $font = $fontMetrics->get_font("sans-serif", "normal");
            $size = 6;
            $color = array(0,0,0);
            $word_space = 0.0;  //  default
            $char_space = 0.0;  //  default
            $angle = 0.0;   //  default
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>
</body>
</html>
