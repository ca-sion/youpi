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

        .ca-table-heading {
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

            .ca-table-heading {
                width: 100%;
            }

            .ca-table-row td.ca-table-heading {
                padding-bottom: 0rem;
            }

            .ca-identifier {
                margin-left: -12px;
            }
        }

        @media screen and (max-width: 600px) {
            .ca-document {
                padding: 20px 20px;
            }

            .ca-ball-title {
                font-size: 14px;
                vertical-align: top;
            }

            .ca-table-row-description {
                display: grid;
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
                        <td style="width: 70%;" align="left">
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
                        <td style="width: 30%;" align="left">

                        </td>
                    </tr>
                </table>

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
                                <tr class="ca-table-row ca-table-row-description">
                                    <td class="ca-table-heading" align="left">
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
