<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SPK {{ $salesOrder->spk_number }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Helvetica Neue',Arial,sans-serif; font-size:10pt; color:#1a1a2e; background:#fff; }
.page { max-width:210mm; margin:0 auto; padding:16mm 18mm; }

/* Header */
.header { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:3pt solid #1a5276; padding-bottom:10pt; margin-bottom:14pt; }
.company-name { font-size:17pt; font-weight:800; color:#1a5276; letter-spacing:-.5pt; }
.company-sub  { font-size:7.5pt; color:#64748b; margin-top:2pt; line-height:1.5; }
.doc-block    { text-align:right; }
.doc-title    { font-size:16pt; font-weight:800; color:#1a5276; letter-spacing:2pt; text-transform:uppercase; }
.doc-subtitle { font-size:8pt; color:#64748b; margin-top:2pt; letter-spacing:1pt; }
.doc-number   { font-family:monospace; font-size:9pt; font-weight:700; color:#1a5276; margin-top:4pt; }
.doc-date     { font-size:8pt; color:#64748b; margin-top:2pt; }

/* Status strip */
.status-strip { display:flex; gap:8pt; margin-bottom:14pt; flex-wrap:wrap; }
.tag { display:inline-block; padding:2pt 8pt; border-radius:10pt; font-size:7.5pt; font-weight:700; text-transform:uppercase; letter-spacing:.5pt; }
.tag-status-confirmed   { background:#e0f2fe; color:#0369a1; }
.tag-status-in_progress { background:#fef3c7; color:#92400e; }
.tag-status-completed   { background:#dcfce7; color:#15803d; }
.tag-status-pending     { background:#f1f5f9; color:#64748b; }
.tag-category { background:#e0e7ff; color:#3730a3; }
.tag-so { background:#f1f5f9; color:#475569; font-size:7pt; }

/* Info grid */
.info-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12pt; margin-bottom:14pt; }
.info-box h4 { font-size:6.5pt; font-weight:700; text-transform:uppercase; letter-spacing:.7pt; color:#94a3b8; border-bottom:1pt solid #e2e8f0; padding-bottom:3pt; margin-bottom:5pt; }
.info-box p { font-size:8.5pt; line-height:1.6; }
.info-box .lbl { color:#64748b; font-size:7.5pt; }
.info-box .val { font-weight:600; color:#1a1a2e; }

/* Separator */
.sep { border:none; border-top:1pt solid #e2e8f0; margin:12pt 0; }

/* Items table */
.items-tbl { width:100%; border-collapse:collapse; margin-bottom:10pt; font-size:8.5pt; }
.items-tbl th { background:#1a5276; color:#fff; padding:5pt 7pt; text-align:left; font-size:7.5pt; font-weight:600; }
.items-tbl th:not(:first-child) { text-align:center; }
.items-tbl th:last-child { text-align:right; }
.items-tbl td { padding:5pt 7pt; border-bottom:1pt solid #f1f5f9; vertical-align:top; }
.items-tbl td:not(:first-child):not(:nth-child(2)) { text-align:center; }
.items-tbl td:last-child { text-align:right; font-weight:600; }
.items-tbl tr:nth-child(even) td { background:#f8fafc; }
.items-tbl .spec { font-size:7.5pt; color:#64748b; }
.items-tbl .desc { font-weight:600; }
.totals-row td { font-size:8pt; padding:3pt 7pt; border:none; }
.totals-row.grand td { font-size:9.5pt; font-weight:700; color:#1a5276; }

/* Footer: QR + signatures */
.footer-grid { display:grid; grid-template-columns:auto 1fr; gap:20pt; margin-top:16pt; }
.qr-block h4 { font-size:6.5pt; font-weight:700; text-transform:uppercase; letter-spacing:.6pt; color:#94a3b8; margin-bottom:5pt; text-align:center; }
.qr-block p  { font-size:6.5pt; color:#94a3b8; text-align:center; margin-top:3pt; }

.sig-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12pt; }
.sig-box  { text-align:center; }
.sig-box .sig-label { font-size:7pt; font-weight:700; text-transform:uppercase; letter-spacing:.5pt; color:#64748b; margin-bottom:3pt; }
.sig-box .sig-line  { border-bottom:1pt solid #cbd5e1; height:32pt; margin-bottom:4pt; }
.sig-box .sig-name  { font-size:7.5pt; font-weight:600; color:#1a1a2e; }
.sig-box .sig-role  { font-size:6.5pt; color:#94a3b8; }

/* Notes block */
.notes-block { background:#f8fafc; border-left:3pt solid #1a5276; padding:6pt 10pt; margin-bottom:10pt; }
.notes-block h4 { font-size:7pt; font-weight:700; text-transform:uppercase; letter-spacing:.5pt; color:#1a5276; margin-bottom:4pt; }
.notes-block p  { font-size:8.5pt; color:#374151; line-height:1.6; white-space:pre-line; }

/* Document footer */
.doc-footer { border-top:1pt solid #e2e8f0; padding-top:6pt; margin-top:12pt; display:flex; justify-content:space-between; font-size:7pt; color:#94a3b8; }

/* Print button */
.print-btn { position:fixed; top:16px; right:16px; background:#1a5276; color:#fff; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-size:13px; font-weight:600; box-shadow:0 2px 12px rgba(0,0,0,.2); z-index:999; }
@media print {
    .print-btn { display:none; }
    .page { padding:14mm 14mm; }
    body { print-color-adjust:exact; -webkit-print-color-adjust:exact; }
}
</style>
</head>
<body>

<button class="print-btn" onclick="window.print()">&#128438; Print / Simpan PDF</button>

<div class="page">

    {{-- Header --}}
    <div class="header">
        <div>
            <div class="company-name">{{ $salesOrder->branch?->name ?? config('app.name') }}</div>
            <div class="company-sub">
                {{ $salesOrder->branch?->address ?? '' }}<br>
                Spesialis Kaca Film &amp; PPF
            </div>
        </div>
        <div class="doc-block">
            <div class="doc-title">SPK Digital</div>
            <div class="doc-subtitle">Surat Perintah Kerja</div>
            <div class="doc-number">{{ $salesOrder->spk_number }}</div>
            <div class="doc-date">Terbit: {{ $salesOrder->spk_issued_at->format('d/m/Y') }}</div>
        </div>
    </div>

    {{-- Tags --}}
    <div class="status-strip">
        <span class="tag tag-status-{{ $salesOrder->status }}">{{ $salesOrder->status_label }}</span>
        <span class="tag tag-category">{{ $salesOrder->category_label }}</span>
        <span class="tag tag-so">SO: {{ $salesOrder->so_number }}</span>
        @if($salesOrder->scheduled_date)
            <span class="tag tag-so">Jadwal: {{ $salesOrder->scheduled_date->format('d M Y') }}</span>
        @endif
    </div>

    {{-- Info grid --}}
    <div class="info-grid">
        <div class="info-box">
            <h4>Data Customer / Prospek</h4>
            <p><span class="lbl">Nama</span><br><span class="val">{{ $salesOrder->prospect_name }}</span></p>
            @if($salesOrder->prospect_phone)
            <p style="margin-top:3pt"><span class="lbl">Telepon</span><br>{{ $salesOrder->prospect_phone }}</p>
            @endif
            @if($salesOrder->prospect_address)
            <p style="margin-top:3pt"><span class="lbl">Alamat</span><br>{{ $salesOrder->prospect_address }}</p>
            @endif
        </div>

        <div class="info-box">
            <h4>
                @if($salesOrder->category === 'building_film') Data Bangunan
                @else Data Kendaraan
                @endif
            </h4>
            @if($salesOrder->vehicle_brand)
            <p><span class="lbl">Merek / Model</span><br>
                <span class="val">{{ $salesOrder->vehicle_brand }} {{ $salesOrder->vehicle_model }}</span>
                @if($salesOrder->vehicle_year) ({{ $salesOrder->vehicle_year }}) @endif
            </p>
            @endif
            @if($salesOrder->vehicle_plate)
            <p style="margin-top:3pt"><span class="lbl">Plat Nomor</span><br>
                <span class="val" style="font-size:11pt;letter-spacing:1pt">{{ strtoupper($salesOrder->vehicle_plate) }}</span>
            </p>
            @endif
            @if($salesOrder->usesFilmSelection() && $salesOrder->film_label)
            <p style="margin-top:3pt"><span class="lbl">Tipe Film / Kegelapan</span><br>
                <span class="val">{{ $salesOrder->film_label }}</span>
            </p>
            @endif
        </div>

        <div class="info-box">
            <h4>Tim Pelaksana</h4>
            @if($salesOrder->assignsMultipleTechnicians())
                @if($salesOrder->technicians->isNotEmpty())
                <p><span class="lbl">Teknisi</span><br><span class="val">{{ $salesOrder->technicians->pluck('name')->join(', ') }}</span></p>
                @endif
            @else
                @if($salesOrder->tukang)
                <p><span class="lbl">Teknisi</span><br><span class="val">{{ $salesOrder->tukang->name }}</span></p>
                @endif
                @if($salesOrder->kenek)
                <p style="margin-top:3pt"><span class="lbl">Kenek</span><br>{{ $salesOrder->kenek->name }}</p>
                @endif
            @endif
            @if($salesOrder->marketing)
            <p style="margin-top:3pt"><span class="lbl">Marketing</span><br>{{ $salesOrder->marketing->name }}</p>
            @endif
            @if($salesOrder->assignsMultipleTechnicians() ? $salesOrder->technicians->isEmpty() && !$salesOrder->marketing : (!$salesOrder->tukang && !$salesOrder->kenek && !$salesOrder->marketing))
            <p style="color:#94a3b8;font-size:8pt">Belum ditugaskan</p>
            @endif
        </div>
    </div>

    <hr class="sep">

    {{-- Items table --}}
    <table class="items-tbl">
        <thead>
            <tr>
                <th style="width:35%">Deskripsi Pekerjaan / Produk</th>
                <th style="width:20%">Spesifikasi</th>
                <th style="width:8%">Sat.</th>
                <th style="width:7%">Qty</th>
                <th style="width:14%">Harga Satuan</th>
                <th style="width:6%">Disc</th>
                <th style="width:14%;text-align:right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesOrder->items as $item)
            <tr>
                <td>
                    <div class="desc">{{ $item->description }}</div>
                    @if($item->specifications)
                        <div class="spec">{{ $item->specifications }}</div>
                    @endif
                </td>
                <td class="spec" style="text-align:left">{{ $item->specifications ?? '—' }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                <td style="text-align:right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td>{{ $item->discount_percent > 0 ? $item->discount_percent.'%' : '—' }}</td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="6" style="text-align:right;color:#64748b">Subtotal</td>
                <td>Rp {{ number_format($salesOrder->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($salesOrder->discount_amount > 0)
            <tr class="totals-row">
                <td colspan="6" style="text-align:right;color:#64748b">Diskon</td>
                <td style="color:#dc2626">- Rp {{ number_format($salesOrder->discount_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="totals-row grand" style="background:#eef2ff">
                <td colspan="6" style="text-align:right">Total</td>
                <td>Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    @if($salesOrder->notes)
    <div class="notes-block">
        <h4>Catatan Pekerjaan</h4>
        <p>{{ $salesOrder->notes }}</p>
    </div>
    @endif

    {{-- Footer: QR + signatures --}}
    <div class="footer-grid">

        {{-- QR Code --}}
        <div class="qr-block">
            <h4>Verifikasi SPK</h4>
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(90)
                ->margin(1)
                ->generate($salesOrder->spk_public_url) !!}
            <p>Scan untuk verifikasi</p>
        </div>

        {{-- Signatures --}}
        <div class="sig-grid">
            <div class="sig-box">
                <div class="sig-label">Admin / PIC</div>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $salesOrder->creator?->name ?? '—' }}</div>
                <div class="sig-role">Penerbit SPK</div>
            </div>
            <div class="sig-box">
                <div class="sig-label">Teknisi</div>
                <div class="sig-line"></div>
                <div class="sig-name">
                    @if($salesOrder->assignsMultipleTechnicians())
                        {{ $salesOrder->technicians->pluck('name')->join(', ') ?: '—' }}
                    @else
                        {{ $salesOrder->tukang?->name ?? '—' }}
                    @endif
                </div>
                <div class="sig-role">Pelaksana</div>
            </div>
            <div class="sig-box">
                <div class="sig-label">Customer</div>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $salesOrder->prospect_name }}</div>
                <div class="sig-role">Penerima</div>
            </div>
        </div>

    </div>

    <div class="doc-footer">
        <span>{{ $salesOrder->spk_number }} — Diterbitkan {{ $salesOrder->spk_issued_at->format('d M Y, H:i') }}</span>
        <span>Dokumen ini sah tanpa tanda tangan basah jika QR Code valid</span>
    </div>

</div>
</body>
</html>
