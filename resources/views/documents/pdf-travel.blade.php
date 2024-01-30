<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $document->name }}</title>

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
            padding-bottom: .25rem;
        }
        .ca-table-row-block {
            background-color: #eeeeee;
        }
        .ca-table-row-block td {
            padding: .25rem 1rem;
        }
        .ca-table-row-description p {
            margin-top: 0;
            margin-bottom: 0;
        }
        .ca-table-row-description p+p {
            margin-top: .5rem;
        }
        .ca-table-heading {
            vertical-align: top;
            width: 20%;
            font-weight: bold;
        }
        .ca-table-content {
            width: 80%;
        }
        .ca-table-content-block {
            margin-bottom: .5rem;
        }
        .ca-table-content-block-warning {
            background-color: rgb(255, 196, 85);
            padding: 8px 12px!important;
        }
        .ca-table-row-important td {
            background-color: rgb(253, 227, 227);
            padding: 8px 12px;
            padding-left: 20%;
            border-radius: 8px;
        }
        .ca-status {
            padding: 2px 8px;
            background-color: #222222;
            color: #ffffff;
            border-radius: 5%;
            font-size: x-small;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <div id="footer">
        <p>Centre athlétique de Sion · Case postale 4057, 1950 Sion 4<br>secretariat@casion.ch · +41 77 505 43 13</p>
    </div>

    <table width="100%">
        <tr>
            <td align="left" style="width: 50%;">
                <div style="margin-left: -42px;">
                    <span class="ca-ball"></span>
                    <span class="ca-ball-title">
                        @if ($document->type){{ $document->type->getLabel() }}@else Information @endif
                        {{ $document->number }}
                    </span>
                </div>
            </td>
            <td align="right" style="width: 50%;">
                <div style="">
                    <img src="https://casion.ch/assets/logo/logo-casion.png" alt="Logo" style="width: 80px;">
                </div>
            </td>
        </tr>
    </table>

    <table width="100%" style="margin-top: 2rem;">
        <tr>
            <td align="left" style="width: 70%;">
                <div style="font-size: 11px;">
                    {{ $document->published_on->isoFormat('LL') }}
                    @if ($document->expires_on)
                        – {{ $document->expires_on->isoFormat('LL') }}
                    @endif
                </div>
                <div style="font-size: 24px;font-weight: bold;">{{ $document->name }}</div>

                @if ($document->status->value != 'validated')
                <span class="ca-status" style="background-color: {{ $document->status->getBackgroundColor() }};">{{ $document->status->getLabel() }}</span>
                @endif

                @if ($document->salutation)
                <div style="margin-top: 2rem;">{!! nl2br($document->salutation) !!}</div>
                @endif
            </td>
            <td align="left" style="width: 30%;">
                 
            </td>
        </tr>
    </table>

    @if (data_get($document, 'travel_data.data.modification_deadline'))
    <table width="100%" style="margin-top: 2rem; margin-bottom: 1rem;">

        <tr class="ca-table-row ca-table-row-description">
            <td align="left" class="ca-table-heading">
                 
            </td>
            <td align="left" class="ca-table-content ca-table-content-block-warning">
                Si tu te déplaces par tes propres moyens, merci d’aviser le CT ou le secrétariat avant le {{ Carbon\Carbon::parse(data_get($document, 'travel_data.data.modification_deadline'))->isoFormat('dddd D.MM.Y') }} 21h. Tél : {{ data_get($document, 'travel_data.data.modification_deadline_phone') }}
            </td>
        </tr>
    </table>
    @endif

    <table width="100%">

        @if (data_get($document, 'travel_data.data.location'))
        <tr class="ca-table-row ca-table-row-description">
            <td align="left" class="ca-table-heading">
                Lieu
            </td>
            <td align="left" class=ca-table-content">
                {{ data_get($document, 'travel_data.data.location') }}
            </td>
        </tr>
        @endif

        @if (data_get($document, 'travel_data.data.date'))
        <tr class="ca-table-row ca-table-row-description">
            <td align="left" class="ca-table-heading">
                Date
            </td>
            <td align="left" class=ca-table-content">
                {{ data_get($document, 'travel_data.data.date') }}
            </td>
        </tr>
        @endif

        @if (data_get($document, 'travel_data.data.competition'))
        <tr class="ca-table-row ca-table-row-description">
            <td align="left" class="ca-table-heading">
                Compétition
            </td>
            <td align="left" class=ca-table-content">
                {{ data_get($document, 'travel_data.data.competition') }}
            </td>
        </tr>
        @endif

        @if (data_get($document, 'travel_data.data.departures'))
        <tr class="ca-table-row ca-table-row-description">
            <td align="left" class="ca-table-heading">
                Aller
            </td>
            <td align="left" class=ca-table-content">
                @foreach (data_get($document, 'travel_data.data.departures') as $departure)
                    <div style="margin-bottom: .5rem">
                        <strong>{{ data_get($departure, 'day_hour') }}</strong>
                        @if (data_get($departure, 'location'))
                        <span>– {{ data_get($departure, 'location') }}</span>
                        <br>
                        @endif
                        <span>{{ data_get($departure, 'means') }}</span>
                        @if (data_get($departure, 'driver'))
                        <span>– Chauffeur : {{ data_get($departure, 'driver') }}</span>
                        <br>
                        @endif
                        @if (data_get($departure, 'travelers_number'))
                        <span>({{ data_get($departure, 'travelers_number') }})</span>
                        @endif
                        <span>{{ data_get($departure, 'travelers') }}</span>
                    </div>
                @endforeach
            </td>
        </tr>
        @endif

        @if (data_get($document, 'travel_data.data.arrivals'))
        <tr class="ca-table-row ca-table-row-description">
            <td align="left" class="ca-table-heading">
                Retour
            </td>
            <td align="left" class=ca-table-content">
                @foreach (data_get($document, 'travel_data.data.arrivals') as $arrival)
                    <div style="margin-bottom: .5rem">
                        <strong>{{ data_get($arrival, 'day_hour') }}</strong>
                        @if (data_get($arrival, 'location'))
                        <span>– {{ data_get($arrival, 'location') }}</span>
                        <br>
                        @endif
                        <span>{{ data_get($arrival, 'means') }}</span>
                        @if (data_get($arrival, 'driver'))
                        <span>– Chauffeur : {{ data_get($arrival, 'driver') }}</span>
                        <br>
                        @endif
                        @if (data_get($arrival, 'travelers_number'))
                        <span>({{ data_get($arrival, 'travelers_number') }})</span>
                        @endif
                        <span>{{ data_get($arrival, 'travelers') }}</span>
                    </div>
                @endforeach
            </td>
        </tr>
        @endif

        @if (data_get($document, 'travel_data.data.accomodation'))
        <tr class="ca-table-row ca-table-row-description">
            <td align="left" class="ca-table-heading">
                Hébergement
            </td>
            <td align="left" class=ca-table-content">
                <div style="margin-bottom: .5rem">
                    {!! nl2br(data_get($document, 'travel_data.data.accomodation')) !!}
                </div>
                @foreach (data_get($document, 'travel_data.data.nights') as $night)
                    <div style="margin-bottom: .5rem">
                        <strong>{{ data_get($night, 'day') }}</strong>
                        @if (data_get($night, 'travelers'))
                        <br>
                        <span>{{ data_get($night, 'travelers') }}</span>
                        @endif
                    </div>
                @endforeach
            </td>
        </tr>
        @endif

        <tr class="ca-table-row ca-table-row-description">
            <td align="left" class="ca-table-heading">
                Frais
            </td>
            <td align="left" class=ca-table-content">
                <div style="margin-bottom: .5rem">
                    <strong>Par le CA Sion</strong>
                    @if (data_get($document, 'travel_data.data.accomodation'))
                    <p>Hébergement, repas du soir à l’hôtel et déplacement ci-dessus (défrayement pour les voitures)</p>
                    @else
                    <p>Déplacement ci-dessus (défrayement pour les voitures)</p>
                    @endif
                </div>
                <div style="margin-bottom: .5rem">
                    <strong>Par l'athlète</strong>
                    @if (data_get($document, 'travel_data.data.accomodation'))
                    <div>Déplacement et hébergement individuel par ses propres moyens</div>
                    @else
                    <div>Déplacement individuel par ses propres moyens</div>
                    @endif
                    <div>Repas du midi</div>
                </div>
            </td>
        </tr>

        <tr class="ca-table-row ca-table-row-description">
            <td align="left" class="ca-table-heading">
                A prendre
            </td>
            <td align="left" class=ca-table-content">
                @if (data_get($document, 'travel_data.data.accomodation'))
                <p>T-shirt ou tenue du club, pointes, baskets, scotch pour marques, casquette, habits de sport, pic-nic, gourde, habits pour la pluie, habits civils, linge et sous-vêtements, brosse à dents</p>
                @else
                <p>T-shirt ou tenue du club, pointes, baskets, scotch pour marques, casquette, habits de sport, pic-nic, gourde, habits pour la pluie, si douche : habits civils, linge et sous-vêtements</p>
                @endif
            </td>
        </tr>

        @if (data_get($document, 'travel_data.data.competition_informations_important'))
        <tr class="ca-table-row ca-table-row-block ca-table-row-important">
            <td align="left" class="ca-table-content" colspan="2">
                {!! data_get($document, 'travel_data.data.competition_informations_important') !!}
            </td>
        </tr>
        @endif

        @if (data_get($document, 'travel_data.data.competition_informations'))
        <tr class="ca-table-row ca-table-row-description">
            <td align="left" class="ca-table-heading">
                À lire et à savoir
            </td>
            <td align="left" class=ca-table-content">
                {!! data_get($document, 'travel_data.data.competition_informations') !!}
            </td>
        </tr>
        @endif

        @if (data_get($document, 'travel_data.data.competition_schedules'))
        <tr class="ca-table-row ca-table-row-description">
            <td align="left" class="ca-table-heading">
                Horaire
            </td>
            <td align="left" class=ca-table-content">
                <div style="margin-bottom: .5rem">{!! nl2br(data_get($document, 'travel_data.data.competition_schedules')) !!}</div>
                <div>L’horaire et à titre indicatif. On est pas à l’abri d’une erreur de notre côté ou d’un changement auprès de l’organisateur.</div>
            </td>
        </tr>
        @endif

        <tr class="ca-table-row ca-table-row-description">
            <td align="left" class="ca-table-heading">
                Renseignements
            </td>
            <td align="left" class=ca-table-content">
                {{ $document->author }}
                @if (data_get($document, 'travel_data.data.modification_deadline_phone'))
                    – {{ data_get($document, 'travel_data.data.modification_deadline_phone') }}
                @endif
            </td>
        </tr>

    </table>

    <table width="100%" style="margin-top: 2rem;">

        @if ($document->sections)
        @foreach ($document->sections as $section)

        @if (data_get($section, 'type') == 'paragraph')
        <tr class="ca-table-row">
            <td align="left" class="ca-table-content" colspan="2">
                {!! data_get($section, 'data.content') !!}
            </td>
        </tr>
        @endif

        @if (data_get($section, 'type') == 'block')
        <tr class="ca-table-row ca-table-row-block">
            <td align="left" class="ca-table-content" colspan="2">
                {!! data_get($section, 'data.content') !!}
            </td>
        </tr>
        <!-- Space-->
        <tr class="ca-table-row">
            <td align="left" class="ca-table-content" colspan="2"></td>
        </tr>
        @endif

        @if (data_get($section, 'type') == 'description')
        <tr class="ca-table-row ca-table-row-description">
            <td align="left" class="ca-table-heading">
                {!! data_get($section, 'data.heading') !!}
            </td>
            <td align="left" class=ca-table-content">
                {!! str(data_get($section, 'data.content'))->markdown() !!}
            </td>
        </tr>
        @endif

        @endforeach
        @endif

    </table>

    @if ($document->signature)
    <table width="100%" style="margin-top: 2rem;">
        <tr>
            <td align="left" style="width: 70%;">
                 
            </td>
            <td align="left" style="width: 30%;">
                {!! nl2br($document->signature) !!}
            </td>
        </tr>
    </table>
    @endif

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

            $x = 536;
            $y = 15;
            $text = "{{ $document->identifier }}";
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
