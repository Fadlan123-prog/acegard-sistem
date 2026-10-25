<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            margin: 1.4cm 1.2cm;
            size: A4;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
        }

        table { border-collapse: collapse; width: 100%; }

        .header-table td { vertical-align: top; }
        .logo { height: 20px; }

        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 2px;
        }
        .doc-subtitle { color: #64748b; font-size: 11px; margin: 0; }
        .company-line { color: #64748b; font-size: 10px; line-height: 1.5; margin: 8px 0 0; }

        .meta-table td { padding: 2px 0; font-size: 11.5px; }
        .meta-label { color: #64748b; width: 90px; }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: bold;
            color: #fff;
        }
        .badge-type   { background: #1e3a8a; }

        hr.divider { border: none; border-top: 1px solid #e2e8f0; margin: 14px 0; }

        .section-label {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #64748b;
            margin: 0 0 4px;
        }

        .party-box, .payment-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
        }
        .party-name { font-size: 13px; font-weight: bold; color: #0f172a; margin: 0 0 2px; }
        .party-line { font-size: 11px; color: #334155; margin: 1px 0; }

        .payment-table td { padding: 2px 0; font-size: 11px; vertical-align: top; }
        .payment-table .pay-label { color: #64748b; width: 85px; }

        table.items { margin-top: 16px; }
        table.items thead th {
            background: #0f172a;
            color: #cbd5e1;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .03em;
            padding: 8px 10px;
            text-align: left;
        }
        table.items thead th.num { text-align: right; }
        table.items tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11.5px;
        }
        table.items tbody td.num { text-align: right; }
        table.items tfoot td {
            padding: 10px;
            font-size: 12.5px;
            font-weight: bold;
            border-top: 2px solid #0f172a;
        }
        table.items tfoot td.num { text-align: right; color: #0f172a; }

        .notes-box {
            margin-top: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 11px;
            color: #475569;
        }

        .footer {
            margin-top: 28px;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

@php
    $idr = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
@endphp

<table class="header-table">
    <tr>
        <td style="width: 55%;">
            <img class="logo" src="{{ public_path('assets/frontend/images/new-acegard-logo.webp') }}" alt="Acegard">
            <p class="doc-title" style="margin-top: 10px;">Invoice Penjualan Roll</p>
            <p class="doc-subtitle">Pusat ke Mitra / Non-Franchise</p>
            <p class="company-line">
                Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Kec. Cipondoh, Kota Tangerang, Banten 15146<br>
                WhatsApp: 0821-2003-331 &middot; acegardindonesia@gmail.com
            </p>
        </td>
        <td style="width: 45%; text-align: right;">
            <table class="meta-table" style="margin-left: auto; width: 220px;">
                <tr>
                    <td class="meta-label">No. Invoice</td>
                    <td style="font-weight: bold;">: {{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Tanggal</td>
                    <td>: {{ $invoice->invoice_date?->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Jatuh Tempo</td>
                    <td>: {{ $invoice->due_date?->format('d M Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Status</td>
                    <td style="font-weight: bold; color: {{ $invoice->status === 'paid' ? '#16a34a' : '#ca8a04' }};">: {{ $invoice->status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<hr class="divider">

<table>
    <tr>
        <td style="width: 52%; padding-right: 10px;">
            <p class="section-label">Ditagihkan Kepada</p>
            <div class="party-box">
                <p class="party-name">{{ $invoice->mitra->name }}</p>
                <span class="badge badge-type" style="margin-bottom: 4px; display: inline-block;">
                    {{ $invoice->mitra->type_label }}
                </span>
                @if($invoice->mitra->pic_name)
                    <p class="party-line">PIC: {{ $invoice->mitra->pic_name }}</p>
                @endif
                @if($invoice->mitra->phone)
                    <p class="party-line">Telepon: {{ $invoice->mitra->phone }}</p>
                @endif
                @if($invoice->mitra->address || $invoice->mitra->city)
                    <p class="party-line">{{ trim(($invoice->mitra->address ?? '') . ' ' . ($invoice->mitra->city ?? '')) }}</p>
                @endif
            </div>
        </td>
        <td style="width: 48%; padding-left: 10px;">
            <p class="section-label">Instruksi Pembayaran</p>
            <div class="payment-box">
                <table class="payment-table">
                    <tr>
                        <td class="pay-label">Bank</td>
                        <td>: Bank Central Asia (BCA)</td>
                    </tr>
                    <tr>
                        <td class="pay-label">No. Rekening</td>
                        <td>: 8900140747</td>
                    </tr>
                    <tr>
                        <td class="pay-label">Atas Nama</td>
                        <td>: Hendy Tanpratama</td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th>Nama Barang</th>
            <th class="num">Qty</th>
            <th>Satuan</th>
            <th class="num">Harga Satuan</th>
            <th class="num">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td class="num">{{ rtrim(rtrim(number_format($item->qty, 2, ',', '.'), '0'), ',') }}</td>
                <td>{{ $item->unit ?: '-' }}</td>
                <td class="num">{{ $idr($item->unit_price) }}</td>
                <td class="num">{{ $idr($item->total_price) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">Grand Total</td>
            <td class="num">{{ $idr($invoice->total_amount) }}</td>
        </tr>
    </tfoot>
</table>

@if($invoice->notes)
    <div class="notes-box">
        <strong>Catatan:</strong> {{ $invoice->notes }}
    </div>
@endif

<div class="footer">
    Dicetak {{ now()->format('d M Y H:i') }} oleh {{ $invoice->creator->name ?? 'sistem' }} &middot; Acegard Indonesia
</div>

</body>
</html>
