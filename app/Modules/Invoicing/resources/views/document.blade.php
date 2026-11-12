<!DOCTYPE html>
<html lang="{{ $t['lang'] }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} {{ $invoice->number }}</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: Helvetica, Arial, sans-serif; font-size: 9px; color: #444444; }
        .abs { position: absolute; }
        .green { color: #334d33; }
        .pad { padding-left: 39pt; padding-right: 39pt; }

        /* Bandeau vert de l'en-tête */
        .hero { position: absolute; top: 0; left: 0; width: 595pt; height: 147pt; background: #334d33; }
        .hero-name { position: absolute; top: 68pt; left: 141pt; font-size: 24px; font-weight: bold; color: #ffffff; }
        .hero-contact { position: absolute; top: 22pt; right: 39pt; width: 300pt; text-align: right; font-size: 7px; line-height: 12px; color: #ffffff; }

        /* Bande titre */
        .titlebar { position: absolute; top: 147pt; left: 0; width: 595pt; height: 40pt; background: #edf3ee; }
        .titlebar table { width: 517pt; margin: 0 0 0 39pt; height: 40pt; border-collapse: collapse; }
        .doc-title { padding-top: 12pt; font-size: 14px; font-weight: bold; color: #334d33; }
        .doc-date { padding-top: 12pt; font-size: 9px; color: #444444; text-align: right; }

        /* Fournisseur / Client */
        .parties { position: absolute; top: 198pt; left: 39pt; }
        .party-head { background: #e4ebe4; color: #334d33; font-size: 8px; font-weight: bold; text-transform: uppercase; padding: 6pt 6pt; height: 8pt; }
        .party-name { font-size: 10px; font-weight: bold; color: #000000; padding: 4pt 6pt 3pt 6pt; }
        .party-line { font-size: 8px; line-height: 12.5px; padding: 0 6pt; color: #444444; }
        .divider { position: absolute; top: 198pt; left: 297pt; width: 0; height: 114pt; border-left: 1px solid #bbbbbb; }

        /* Lignes */
        table.lines { margin-left: 39pt; width: 517pt; border-collapse: collapse; }
        table.lines th { background: #334d33; color: #ffffff; font-size: 9px; font-weight: bold; text-align: center; height: 33pt; border: 1px solid #334d33; }
        table.lines td { border: 1px solid #c8c8c8; padding: 12pt 10pt; font-size: 9px; color: #555555; vertical-align: middle; }
        .num { text-align: right; white-space: nowrap; }
        .ctr { text-align: center; }

        /* Corps de page 1 sous le tableau (dans le flux) */
        .flow { padding-top: 0; }
        table.total { width: 160pt; margin-left: 357pt; border: 1.5px solid #222222; background: #eeeeee; border-collapse: collapse; }
        table.total td { padding: 10pt 12pt; font-weight: bold; font-size: 11px; color: #333333; }
        table.discount { margin-left: 357pt; width: 160pt; margin-bottom: 8pt; border-collapse: collapse; }
        table.discount td { font-size: 9px; padding: 2pt 12pt; color: #555555; }
        hr.sep { border: 0; border-top: 1px solid #cccccc; margin: 18pt 0 12pt 0; }
        .method-label { color: #666666; font-size: 9px; }
        .method-value { color: #1e40af; font-weight: bold; font-size: 9px; padding-left: 12pt; }
        .conditions, .legal { border: 1px solid #cccccc; background: #f4f8fb; padding: 8pt 12pt; }
        .conditions { margin-top: 34pt; }
        .box-title { color: #334d33; font-size: 9px; font-weight: bold; margin-bottom: 3pt; }
        .box-line { font-size: 7px; line-height: 9px; color: #333333; }

        .pagenum { position: absolute; width: 595pt; text-align: center; font-size: 7px; color: #777777; }

        /* Page 2 */
        .page2 { page-break-before: always; position: relative; width: 595pt; height: 830pt; }
        .p2-head { position: absolute; top: 24pt; left: 39pt; width: 517pt; border-bottom: 1px solid #cccccc; padding-bottom: 4pt; font-size: 7px; color: #666666; }
        .p2-head table { width: 100%; border-collapse: collapse; }
        .legal { position: absolute; top: 56pt; left: 39pt; width: 495pt; }
        .sign { position: absolute; top: 751pt; left: 39pt; width: 195pt; height: 62pt; border: 1px solid #888888; padding: 6pt 9pt; font-size: 8px; font-weight: bold; color: #333333; }
        .thanks { position: absolute; top: 777pt; right: 39pt; font-size: 10px; font-style: italic; color: #334d33; }
    </style>
</head>
<body>
@php
    $isCredit = $invoice->type->value === 'credit_note';
    $date = $invoice->issued_at->format('d/m/Y').' '.$t['at'].' '.$invoice->issued_at->format('H:i');
    $money = function (int $amount): string {
        return number_format($amount, 2, ',', "\u{00A0}")."\u{00A0}CFA";
    };
    $cfa = in_array($invoice->currency, ['XOF', 'XAF'], true);
    $fmt = $cfa ? $money : fn (int $amount) => number_format($amount, 2, ',', "\u{00A0}")."\u{00A0}".$invoice->currency;
    $logo = \App\Modules\Shared\Support\BrandLogo::dataUri();
    $wedge = 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="144" height="57" viewBox="0 0 144 57"><polygon points="0,57 144,57 144,0" fill="#ffffff"/></svg>');
    $heading = $title.' '.$t['number'].' '.$invoice->number;
@endphp

{{-- ───────────── Page 1 ───────────── --}}
<div class="hero"></div>
<img class="abs" style="top: 90pt; left: 451pt; width: 144pt; height: 57pt;" src="{{ $wedge }}" alt="">
@if ($logo !== '')
    <img class="abs" style="top: 14pt; left: 39pt; width: 86pt; height: 86pt;" src="{{ $logo }}" alt="{{ $company['name'] }}">
@endif
<div class="hero-name">{{ mb_strtoupper($company['name']) }}</div>
<div class="hero-contact">
    @if ($company['address'] !== '') {{ $t['address'] }} {{ $company['address'] }}<br> @endif
    @if ($company['phone'] !== '') {{ $t['phone'] }} {{ $company['phone'] }}<br> @endif
    @if ($company['email'] !== '') {{ $t['email'] }} {{ $company['email'] }} @endif
</div>

<div class="titlebar">
    <table>
        <tr>
            <td class="doc-title">{{ $heading }}</td>
            <td class="doc-date">{{ $t['date'] }} {{ $date }}</td>
        </tr>
    </table>
</div>

<table class="parties" style="width: 545pt; border-collapse: collapse;">
    <tr>
        <td style="width: 253pt; vertical-align: top;">
            <div class="party-head">{{ mb_strtoupper($t['supplier']) }}</div>
            <div class="party-name">{{ $company['name'] }}</div>
            @if ($company['address'] !== '') <div class="party-line">{{ $company['address'] }}</div> @endif
            @if ($company['phone'] !== '') <div class="party-line">{{ $t['phone'] }} {{ $company['phone'] }}</div> @endif
            @if ($company['email'] !== '') <div class="party-line">{{ $company['email'] }}</div> @endif
        </td>
        <td style="width: 12pt;"></td>
        <td style="width: 280pt; vertical-align: top;">
            <div class="party-head">{{ mb_strtoupper($t['customer']) }}</div>
            <div class="party-name">{{ $invoice->customer_name }}</div>
            <div class="party-line">{{ $t['order'] }} {{ $invoice->order_number }}</div>
            @if ($creditedNumber !== null) <div class="party-line">{{ $t['cancels'] }} {{ $creditedNumber }}</div> @endif
            @if ($isCredit && $invoice->reason) <div class="party-line">{{ $t['credit_reason'] }} {{ $invoice->reason }}</div> @endif
        </td>
    </tr>
</table>
<div class="divider"></div>

<div style="height: 348pt;"></div>
<table class="lines">
    <thead>
        <tr>
            <th style="width: 159pt;">{{ $t['description'] }}</th>
            <th style="width: 71pt;">{{ $t['quantity'] }}</th>
            <th style="width: 71pt;">{{ $t['unit'] }}</th>
            <th style="width: 108pt;">{{ $t['unit_price'] }}</th>
            <th style="width: 108pt;">{{ $t['line_total'] }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($invoice->lines as $line)
            <tr>
                <td>{{ $line->description }}</td>
                <td class="ctr">{{ $line->quantity }}</td>
                <td class="ctr">{{ $t['piece'] }}</td>
                <td class="num">{{ $fmt($line->unit_price_amount) }}</td>
                <td class="num">{{ $fmt($line->line_total_amount) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div style="margin: 36pt 0 0 39pt; width: 517pt;">
    @if ($invoice->discount_amount > 0)
        <table class="discount">
            <tr>
                <td>{{ $t['discount'] }}{{ $invoice->coupon_code ? ' ('.$invoice->coupon_code.')' : '' }}</td>
                <td class="num">− {{ $fmt($invoice->discount_amount) }}</td>
            </tr>
        </table>
    @endif
    <table class="total">
        <tr>
            <td>{{ $t['total'] }}</td>
            <td class="num">{{ $fmt($invoice->total_amount) }}</td>
        </tr>
    </table>

    <hr class="sep">

    @if ($methodLabel !== null)
        <table style="border-collapse: collapse;">
            <tr>
                <td class="method-label">{{ $t['payment_method'] }}</td>
                <td class="method-value">{{ $methodLabel }}</td>
            </tr>
        </table>
    @endif

    <div class="conditions">
        <div class="box-title">{{ $t['conditions_title'] }}</div>
        @foreach ($t['conditions'] as $condition)
            <div class="box-line">- {{ $condition }}</div>
        @endforeach
    </div>
</div>
<div class="pagenum" style="top: 806pt;">{{ $t['page'] }} 1 {{ $t['of'] }} 2</div>

{{-- ───────────── Page 2 ───────────── --}}
<div class="page2">
    <div class="p2-head">
        <table>
            <tr>
                <td style="font-weight: bold; color: #444444;">{{ $company['name'] }}</td>
                <td style="text-align: right;">{{ ucfirst(mb_strtolower($title)) }} {{ $t['number'] }} {{ $invoice->number }} — {{ $invoice->issued_at->format('d/m/Y') }} — {{ $t['customer'] }} : {{ $invoice->customer_name }}</td>
            </tr>
        </table>
    </div>

    <div class="legal">
        <div class="box-title">{{ $t['legal_title'] }}</div>
        @foreach ($t['legal'] as $mention)
            <div class="box-line">- {{ $mention }}</div>
        @endforeach
        @if ($company['legal'] !== '') <div class="box-line">- {{ $company['legal'] }}</div> @endif
    </div>

    <div class="sign">{{ $t['signature'] }}</div>
    <div class="thanks">{{ $t['thanks'] }}</div>
    <div class="pagenum" style="top: 806pt;">{{ $t['page'] }} 2 {{ $t['of'] }} 2</div>
</div>

</body>
</html>
