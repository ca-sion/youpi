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
            width: 30%;
            font-weight: bold;
        }
        .ca-table-content {
            width: 70%;
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
                    <span class="ca-ball-title">
                        @if ($document->type){{ $document->type->getLabel() }}@else Information @endif
                        {{ $document->number }}
                    </span>
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
            <td align="left" style="width: 70%;">
                <div style="font-size: 11px;">{{ $document->published_on->isoFormat('LL') }}</div>
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

    <table width="100%" style="margin-top: .5rem;">

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
