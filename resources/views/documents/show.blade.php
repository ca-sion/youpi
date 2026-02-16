<x-layouts.app>

    <style>
        @page {
            margin: 1cm 1.5cm 1.5cm 2.5cm;
        }

        .ca-document {
            border: solid 2px #b7b7b7;
            border-top-left-radius: 20px 20px;
            border-top-right-radius: 20px 20px;
            border-bottom-left-radius: 110px 19px;
            border-bottom-right-radius: 120px 24px;
            box-shadow: 5px 10px 8px rgba(0, 0, 0, .15);
            padding: 40px 80px;
            overflow-wrap: break-word;
            word-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
        }

        .ca-identifier {
            margin-left: -42px;
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
            padding-bottom: 1rem;
        }

        .ca-table-row p {
            margin-bottom: 1rem;
        }

        .ca-table-row-block {
            background-color: #eeeeee;
        }

        .ca-table-row-block td {
            padding: .5rem 1rem;
        }

        .ca-table-row-description p {
            margin-top: 0;
            margin-bottom: 0;
        }

        .ca-table-row-description p+p {
            margin-top: .5rem;
        }

        .ca-table-heading,
        .ca-table-row-definition-heading {
            vertical-align: top;
            width: 20%;
            font-weight: bold;
            padding-right: .5rem;
        }

        .ca-table-content {
            width: 80%;
        }

        .ca-document ol,
        .ca-document ul {
            margin-left: 2rem;
        }

        .ca-document li {
            list-style-type: disc;
        }

        .ca-table-content-block {
            margin-bottom: .5rem;
        }

        .ca-table-content-block-warning {
            background-color: rgb(255, 196, 85);
            padding: 8px 12px !important;
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

        @media screen and (max-width: 900px) {
            .ca-document {
                padding: 20px 30px;
            }

            .ca-table-content {
                width: 100%;
            }

            .ca-table-row td.ca-table-row-definition-heading {
                padding-bottom: 0rem;
            }

            .ca-identifier {
                margin-left: -12px;
            }
        }

        @media screen and (max-width: 600px) {
            .ca-document {
                padding: 20px 10px;
                font-size: 75%;
            }

            .ca-ball-title {
                font-size: 14px;
                vertical-align: top;
            }

            .ca-table-row-definition-heading,
            .ca-table-heading {
                width: 100%;
            }

            .ca-table-row-definition,
            .ca-table-row-description {
                display: grid;
                grid-template-columns: minmax(85px, 1fr) 3fr;
                gap: 2px;
            }
        }
    </style>

    <main class="bg-white pb-16 pt-8 antialiased dark:bg-gray-900 lg:pb-24 lg:pt-16">
        <div class="mx-auto flex max-w-screen-xl flex-col justify-between px-4">

            <a class="mb-2 me-2 rounded-lg border border-gray-800 px-5 py-2.5 text-center text-sm font-medium text-gray-900 hover:bg-gray-900 hover:text-white focus:outline-none focus:ring-4 focus:ring-gray-300 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white dark:focus:ring-gray-800"
                href="{{ route('documents.pdf', ['document' => $document->id]) }}"
                style="margin-bottom: 2rem;">Ouvrir le fichier PDF</a>

            <article class="ca-document mx-auto w-full max-w-4xl text-gray-900 dark:format-invert dark:text-white">

                <table width="100%">
                    <tr>
                        <td style="width: 50%;" align="left">
                            <div class="ca-identifier">
                                <span class="ca-ball"></span>
                                <span class="ca-ball-title">
                                    @if ($document->type)
                                        {{ $document->type->getLabel() }}
                                    @else
                                        Information
                                    @endif
                                    {{ $document->number }}
                                </span>
                            </div>
                        </td>
                        <td style="width: 50%;" align="right">
                            <div style="">
                                <img src="https://casion.ch/assets/logo/logo-casion.png"
                                    alt="Logo"
                                    style="width: 80px;">
                            </div>
                        </td>
                    </tr>
                </table>

                <table style="margin-top: 2rem;" width="100%">
                    <tr>
                        <td style="width: 99%;" align="left">
                            <div style="font-size: x-small;">
                                {{ $document->published_on->isoFormat('LL') }}
                                @if ($document->expires_on)
                                    – {{ $document->expires_on->isoFormat('LL') }}
                                @endif
                            </div>
                            <div style="font-size: x-large;font-weight: bold;margin-bottom: 2rem;">{{ $document->name }}</div>

                            @if ($document->status->value != 'validated')
                                <span class="ca-status" style="background-color: {{ $document->status->getBackgroundColor() }};">{{ $document->status->getLabel() }}</span>
                            @endif

                            @if ($document->salutation)
                                <div style="margin-top: 2rem;">{!! nl2br($document->salutation) !!}</div>
                            @endif
                        </td>
                        <td style="width: 10%;" align="left">

                        </td>
                    </tr>
                </table>

                @if ($document->type == \App\Enums\DocumentType::TRAVEL)
                    @if (data_get($document, 'travel_data.data.modification_deadline'))
                        <table style="margin-top: 2rem; margin-bottom: 1rem;" width="100%">

                            <tr class="ca-table-row ca-table-row-description">
                                <td class="ca-table-heading" align="left">

                                </td>
                                <td class="ca-table-content ca-table-content-block-warning" align="left">
                                    Si tu te déplaces par tes propres moyens, merci d’aviser le CT ou le secrétariat avant le {{ Carbon\Carbon::parse(data_get($document, 'travel_data.data.modification_deadline'))->isoFormat('dddd D.MM.Y') }} 21h. Tél : {{ data_get($document, 'travel_data.data.modification_deadline_phone') }}
                                </td>
                            </tr>
                        </table>
                    @endif
                    <table width="100%">

                        @if (data_get($document, 'travel_data.data.location'))
                            <tr class="ca-table-row ca-table-row-description">
                                <td class="ca-table-heading" align="left">
                                    Lieu
                                </td>
                                <td class="ca-table-content" align="left">
                                    {{ data_get($document, 'travel_data.data.location') }}
                                </td>
                            </tr>
                        @endif

                        @if (data_get($document, 'travel_data.data.date'))
                            <tr class="ca-table-row ca-table-row-description">
                                <td class="ca-table-heading" align="left">
                                    Date
                                </td>
                                <td class="ca-table-content" align="left">
                                    {{ data_get($document, 'travel_data.data.date') }}
                                </td>
                            </tr>
                        @endif

                        @if (data_get($document, 'travel_data.data.competition'))
                            <tr class="ca-table-row ca-table-row-description">
                                <td class="ca-table-heading" align="left">
                                    Compétition
                                </td>
                                <td class="ca-table-content" align="left">
                                    {{ data_get($document, 'travel_data.data.competition') }}
                                </td>
                            </tr>
                        @endif

                        @if (data_get($document, 'travel_data.data.departures'))
                            <tr class="ca-table-row ca-table-row-description">
                                <td class="ca-table-heading" align="left">
                                    Aller
                                </td>
                                <td class="ca-table-content" align="left">
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
                                <td class="ca-table-heading" align="left">
                                    Retour
                                </td>
                                <td class="ca-table-content" align="left">
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
                                <td class="ca-table-heading" align="left">
                                    Hébergement
                                </td>
                                <td class="ca-table-content" align="left">
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
                            <td class="ca-table-heading" align="left">
                                Frais
                            </td>
                            <td class="ca-table-content" align="left">
                                <div style="margin-bottom: .5rem">
                                    <strong>Par le CA Sion</strong>
                                    @if (data_get($document, 'travel_data.data.accomodation'))
                                        <p>Hébergement, repas du soir à l’hôtel et déplacement ci-dessus</p>
                                    @else
                                        <p>Déplacement ci-dessus</p>
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
                            <td class="ca-table-heading" align="left">
                                A prendre
                            </td>
                            <td class="ca-table-content" align="left">
                                @if (data_get($document, 'travel_data.data.accomodation'))
                                    <p>T-shirt ou tenue du club, pointes, baskets, scotch pour marques, casquette, habits de sport, pic-nic, gourde, habits pour la pluie, habits civils, linge et sous-vêtements, brosse à dents</p>
                                @else
                                    <p>T-shirt ou tenue du club, pointes, baskets, scotch pour marques, casquette, habits de sport, pic-nic, gourde, habits pour la pluie, si douche : habits civils, linge et sous-vêtements</p>
                                @endif
                            </td>
                        </tr>

                        @if (data_get($document, 'travel_data.data.competition_informations_important'))
                            <tr class="ca-table-row ca-table-row-block ca-table-row-important">
                                <td class="ca-table-content"
                                    align="left"
                                    colspan="2">
                                    {!! data_get($document, 'travel_data.data.competition_informations_important') !!}
                                </td>
                            </tr>
                        @endif

                        @if (data_get($document, 'travel_data.data.competition_informations'))
                            <tr class="ca-table-row ca-table-row-description">
                                <td class="ca-table-heading" align="left">
                                    À lire et à savoir
                                </td>
                                <td class="ca-table-content" align="left">
                                    {!! data_get($document, 'travel_data.data.competition_informations') !!}
                                </td>
                            </tr>
                        @endif

                        @if (data_get($document, 'travel_data.data.competition_schedules'))
                            <tr class="ca-table-row ca-table-row-description">
                                <td class="ca-table-heading" align="left">
                                    Horaire
                                </td>
                                <td class="ca-table-content" align="left">
                                    <div style="margin-bottom: .5rem">{!! nl2br(data_get($document, 'travel_data.data.competition_schedules')) !!}</div>
                                    <div>L’horaire et à titre indicatif. On est pas à l’abri d’une erreur de notre côté ou d’un changement auprès de l’organisateur.</div>
                                </td>
                            </tr>
                        @endif

                        <tr class="ca-table-row ca-table-row-description">
                            <td class="ca-table-heading" align="left">
                                Renseignements
                            </td>
                            <td class="ca-table-content" align="left">
                                {{ $document->author }}
                                @if (data_get($document, 'travel_data.data.modification_deadline_phone'))
                                    – {{ data_get($document, 'travel_data.data.modification_deadline_phone') }}
                                @endif
                            </td>
                        </tr>

                    </table>
                @endif

                <table style="margin-top: 2rem;" width="100%">

                    @if ($document->sections)
                        @foreach ($document->sections as $section)
                            @if (data_get($section, 'type') == 'paragraph')
                                <tr class="ca-table-row">
                                    <td class="ca-table-content"
                                        align="left"
                                        colspan="2">
                                        {!! data_get($section, 'data.content') !!}
                                    </td>
                                </tr>
                            @endif

                            @if (data_get($section, 'type') == 'block')
                                <tr class="ca-table-row ca-table-row-block">
                                    <td class="ca-table-content"
                                        align="left"
                                        colspan="2">
                                        {!! data_get($section, 'data.content') !!}
                                    </td>
                                </tr>
                                <!-- Space-->
                                <tr class="ca-table-row">
                                    <td class="ca-table-content"
                                        align="left"
                                        colspan="2"></td>
                                </tr>
                            @endif

                            @if (data_get($section, 'type') == 'description')
                                <tr class="ca-table-row ca-table-row-definition">
                                    <td class="ca-table-row-definition-heading" align="left">
                                        {!! data_get($section, 'data.heading') !!}
                                    </td>
                                    <td class="ca-table-content" align="left">
                                        {!! str(data_get($section, 'data.content'))->markdown() !!}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif

                </table>

                @if ($document->signature)
                    <table style="margin-top: 2rem;" width="100%">
                        <tr>
                            <td style="width: 70%;" align="left">

                            </td>
                            <td style="width: 30%;" align="left">
                                {!! nl2br($document->signature) !!}
                            </td>
                        </tr>
                    </table>
                @endif

            </article>

        </div>
    </main>
</x-layouts.app>
