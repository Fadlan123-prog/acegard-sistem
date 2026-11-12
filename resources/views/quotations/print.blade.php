<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quotation {{ $quotation->quotation_number }}</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 10pt; color: #1a1a2e; background:#fff; }

    .page { max-width:210mm; margin:0 auto; padding:20mm 18mm; }

    /* Header */
    .header { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2.5pt solid #0f3460; padding-bottom:12pt; margin-bottom:16pt; }
    .header-left .company-name { font-size:18pt; font-weight:800; color:#0f3460; letter-spacing:-0.5pt; }
    .header-left .company-sub { font-size:8pt; color:#64748b; margin-top:2pt; }
    .header-right { text-align:right; }
    .doc-title { font-size:20pt; font-weight:800; color:#0f3460; letter-spacing:1pt; text-transform:uppercase; }
    .doc-number { font-size:9pt; color:#64748b; margin-top:3pt; font-family:monospace; }
    .doc-meta { font-size:8pt; color:#64748b; margin-top:6pt; }

    /* Status badge */
    .status-badge { display:inline-block; padding:2pt 8pt; border-radius:12pt; font-size:8pt; font-weight:700; text-transform:uppercase; letter-spacing:0.5pt; margin-top:4pt; }
    .status-draft    { background:#f1f5f9; color:#64748b; }
    .status-sent     { background:#e0f2fe; color:#0369a1; }
    .status-accepted { background:#dcfce7; color:#166534; }
    .status-rejected { background:#fee2e2; color:#991b1b; }
    .status-expired  { background:#fef3c7; color:#92400e; }

    /* Two-column info */
    .info-row { display:flex; gap:20pt; margin-bottom:14pt; }
    .info-box { flex:1; }
    .info-box h3 { font-size:7pt; font-weight:700; text-transform:uppercase; letter-spacing:0.6pt; color:#94a3b8; border-bottom:1pt solid #e2e8f0; padding-bottom:3pt; margin-bottom:6pt; }
    .info-box p { font-size:9pt; line-height:1.6; }
    .info-box .label { color:#64748b; font-size:8pt; }
    .info-box .value { font-weight:600; color:#1a1a2e; }

    /* Items table */
    .items-table { width:100%; border-collapse:collapse; margin-bottom:12pt; }
    .items-table th { background:#0f3460; color:#fff; padding:6pt 8pt; text-align:left; font-size:8pt; font-weight:600; }
    .items-table th.right { text-align:right; }
    .items-table th.center { text-align:center; }
    .items-table td { padding:6pt 8pt; border-bottom:0.5pt solid #e2e8f0; font-size:9pt; vertical-align:top; }
    .items-table td.right { text-align:right; }
    .items-table td.center { text-align:center; }
    .items-table tr:nth-child(even) td { background:#f8fafc; }
    .item-desc { font-weight:600; }
    .item-spec { font-size:8pt; color:#64748b; margin-top:2pt; }

    /* Totals */
    .totals { display:flex; justify-content:flex-end; margin-bottom:14pt; }
    .totals-table { width:220pt; border-collapse:collapse; }
    .totals-table td { padding:4pt 8pt; font-size:9pt; }
    .totals-table td.label { color:#64748b; }
    .totals-table td.amount { text-align:right; font-weight:600; }
    .totals-table .total-row td { border-top:1.5pt solid #0f3460; font-weight:800; font-size:11pt; color:#0f3460; padding-top:6pt; }

    /* Notes & terms */
    .notes-section { display:flex; gap:20pt; margin-bottom:14pt; }
    .notes-box { flex:1; }
    .notes-box h3 { font-size:7pt; font-weight:700; text-transform:uppercase; letter-spacing:0.6pt; color:#94a3b8; border-bottom:1pt solid #e2e8f0; padding-bottom:3pt; margin-bottom:5pt; }
    .notes-box p { font-size:8.5pt; line-height:1.55; color:#374151; white-space:pre-line; }

    /* Signature */
    .signature-row { display:flex; justify-content:space-between; gap:20pt; margin-top:20pt; border-top:0.5pt solid #e2e8f0; padding-top:14pt; }
    .signature-box { text-align:center; width:33%; }
    .signature-box .sig-title { font-size:8pt; color:#64748b; margin-bottom:28pt; }
    .signature-box .sig-line { border-top:1pt solid #1a1a2e; padding-top:4pt; font-size:8pt; font-weight:600; }

    /* Footer */
    .footer { margin-top:16pt; text-align:center; font-size:7.5pt; color:#94a3b8; border-top:0.5pt solid #e2e8f0; padding-top:8pt; }

    @media print {
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .no-print { display:none !important; }
        .page { padding:12mm 14mm; }
    }
</style>
</head>
<body>

<div class="no-print" style="background:#f1f5f9;padding:12px;text-align:center;">
    <button onclick="window.print()" style="background:#0f3460;color:#fff;border:none;padding:8px 24px;border-radius:6px;cursor:pointer;font-size:13px;">
        🖨️ Print / Save PDF
    </button>
    <a href="{{ route('quotations.show', $quotation) }}"
       style="margin-left:12px;font-size:13px;color:#0f3460;">← Kembali</a>
</div>

<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="company-name">ACEGARD</div>
            <div class="company-sub">
                {{ $quotation->branch?->name ?? '' }}<br>
                {{ $quotation->branch?->address ?? '' }}<br>
                {{ $quotation->branch?->phone ? 'Telp: ' . $quotation->branch->phone : '' }}
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title">Penawaran Harga</div>
            <div class="doc-number">{{ $quotation->quotation_number }}</div>
            <div class="doc-meta">
                Tanggal: {{ $quotation->date->format('d M Y') }}<br>
                Berlaku s/d: {{ $quotation->valid_until->format('d M Y') }}
            </div>
            @php $rs = $quotation->resolvedStatus(); @endphp
            <div class="status-badge status-{{ $rs }}">
                {{ \App\Models\Quotation::STATUSES[$rs] }}
            </div>
        </div>
    </div>

    {{-- Info Row --}}
    <div class="info-row">
        <div class="info-box">
            <h3>Kepada Yth.</h3>
            <p>
                <span class="value">{{ $quotation->prospect_name }}</span><br>
                @if($quotation->prospect_phone)<span class="label">Telp:</span> {{ $quotation->prospect_phone }}<br>@endif
                @if($quotation->prospect_email)<span class="label">Email:</span> {{ $quotation->prospect_email }}<br>@endif
                @if($quotation->prospect_address){{ $quotation->prospect_address }}@endif
            </p>
        </div>
        @if($quotation->category !== 'building_film')
        <div class="info-box">
            <h3>Data Kendaraan</h3>
            <p>
                <span class="label">Merek/Model:</span> <span class="value">{{ $quotation->vehicle_brand }} {{ $quotation->vehicle_model }}</span><br>
                <span class="label">Tahun:</span> {{ $quotation->vehicle_year ?? '—' }}<br>
                @if($quotation->vehicle_plate)<span class="label">Plat:</span> {{ $quotation->vehicle_plate }}@endif
            </p>
        </div>
        @endif
        <div class="info-box">
            <h3>Kategori Layanan</h3>
            <p>
                <span class="value">{{ $quotation->category_label }}</span><br>
                @if($quotation->assignee)
                    <span class="label">Marketing:</span> {{ $quotation->assignee->name }}
                @endif
            </p>
        </div>
    </div>

    {{-- Items --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:30pt">No</th>
                <th>Deskripsi / Produk</th>
                <th class="center" style="width:55pt">Satuan</th>
                <th class="center" style="width:40pt">Qty</th>
                <th class="right" style="width:90pt">Harga Satuan</th>
                <th class="center" style="width:40pt">Disc%</th>
                <th class="right" style="width:90pt">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $i => $item)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>
                    @if($item->vehicle_label || $item->vehicle_plate)
                        <div class="item-vehicle" style="font-weight:bold">Kendaraan: {{ trim($item->vehicle_label . ($item->vehicle_plate ? ' · ' . $item->vehicle_plate : ''), ' ·') }}</div>
                    @endif
                    <div class="item-desc">{{ $item->description }}</div>
                    @if($item->specifications)
                        <div class="item-spec">{{ $item->specifications }}</div>
                    @endif
                </td>
                <td class="center">{{ $item->unit }}</td>
                <td class="center">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                <td class="right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="center">{{ $item->discount_percent > 0 ? $item->discount_percent . '%' : '—' }}</td>
                <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal</td>
                <td class="amount">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($quotation->discount_amount > 0)
            <tr>
                <td class="label">Diskon</td>
                <td class="amount" style="color:#dc2626;">- Rp {{ number_format($quotation->discount_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="amount">Rp {{ number_format($quotation->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    {{-- Notes & Terms --}}
    @if($quotation->notes || $quotation->terms)
    <div class="notes-section">
        @if($quotation->notes)
        <div class="notes-box">
            <h3>Catatan</h3>
            <p>{{ $quotation->notes }}</p>
        </div>
        @endif
        @if($quotation->terms)
        <div class="notes-box">
            <h3>Syarat & Ketentuan</h3>
            <p>{{ $quotation->terms }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Signatures --}}
    <div class="signature-row">
        <div class="signature-box">
            <div class="sig-title">Dibuat oleh</div>
            <div class="sig-line">{{ $quotation->assignee?->name ?? $quotation->creator?->name ?? 'Marketing' }}</div>
        </div>
        <div class="signature-box">
            <div class="sig-title">Mengetahui</div>
            <div class="sig-line">Manager</div>
        </div>
        <div class="signature-box">
            <div class="sig-title">Menyetujui / Customer</div>
            <div class="sig-line">{{ $quotation->prospect_name }}</div>
        </div>
    </div>

    <div class="footer">
        Dokumen ini dibuat secara otomatis oleh sistem ACEGARD •
        {{ $quotation->quotation_number }} • {{ $quotation->date->format('d M Y') }}
    </div>

</div>

</body>
</html>
