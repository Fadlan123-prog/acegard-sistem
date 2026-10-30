<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SPK {{ $salesOrder->spk_number }} — Verifikasi</title>
<style>
*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
body { font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background:#f0f4f8; min-height:100vh; display:flex; flex-direction:column; align-items:center; padding:24px 16px; }

.card { background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(0,0,0,.10); max-width:480px; width:100%; overflow:hidden; }

.card-header { background:#1a5276; color:#fff; padding:20px 24px; }
.valid-badge { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.15); border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; margin-bottom:10px; }
.valid-badge .dot { width:8px; height:8px; border-radius:50%; background:#4ade80; }
.spk-title { font-size:22px; font-weight:800; letter-spacing:.5px; }
.spk-number { font-family:monospace; font-size:14px; opacity:.8; margin-top:4px; }

.card-body { padding:20px 24px; }

.section { margin-bottom:18px; }
.section-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:#94a3b8; margin-bottom:8px; }
.info-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:14px; }
.info-row:last-child { border-bottom:none; }
.info-row .lbl { color:#64748b; }
.info-row .val { font-weight:600; color:#1e293b; text-align:right; max-width:60%; }

.status-chip { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:700; }
.chip-pending     { background:#f1f5f9; color:#64748b; }
.chip-confirmed   { background:#e0f2fe; color:#0369a1; }
.chip-in_progress { background:#fef3c7; color:#92400e; }
.chip-completed   { background:#dcfce7; color:#15803d; }
.chip-cancelled   { background:#fee2e2; color:#991b1b; }

.category-chip { background:#e0e7ff; color:#3730a3; display:inline-block; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600; }

.items-section { background:#f8fafc; border-radius:10px; padding:14px 16px; margin-bottom:18px; }
.item-row { display:flex; justify-content:space-between; align-items:baseline; padding:5px 0; border-bottom:1px dashed #e2e8f0; font-size:13px; }
.item-row:last-child { border-bottom:none; }
.item-name { font-weight:600; color:#1e293b; flex:1; }
.item-spec { font-size:11px; color:#94a3b8; display:block; }
.item-price { color:#64748b; margin-left:12px; white-space:nowrap; }

.total-row { display:flex; justify-content:space-between; padding:10px 16px; background:#eef2ff; border-radius:8px; font-size:16px; font-weight:700; color:#1e293b; margin-bottom:18px; }
.total-row .total-label { color:#3730a3; }

.staff-section { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.staff-box { background:#f8fafc; border-radius:8px; padding:10px 12px; }
.staff-role { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#94a3b8; margin-bottom:3px; }
.staff-name { font-size:13px; font-weight:600; color:#1e293b; }

.verified-footer { text-align:center; padding:16px 24px; border-top:1px solid #f1f5f9; background:#f8fafc; }
.verified-footer .checkmark { font-size:28px; color:#22c55e; }
.verified-footer p { font-size:11px; color:#94a3b8; margin-top:6px; }
.verified-footer strong { display:block; font-size:13px; color:#374151; margin-top:2px; }

@media(prefers-color-scheme:dark){
    body { background:#0f172a; }
    .card { background:#1e293b; box-shadow:0 4px 24px rgba(0,0,0,.4); }
    .info-row { border-bottom-color:#334155; }
    .info-row .lbl { color:#94a3b8; }
    .info-row .val { color:#e2e8f0; }
    .items-section { background:#0f172a; }
    .item-row { border-bottom-color:#1e293b; }
    .item-name { color:#e2e8f0; }
    .staff-box { background:#0f172a; }
    .staff-name { color:#e2e8f0; }
    .verified-footer { background:#1a2744; border-top-color:#334155; }
    .verified-footer p { color:#64748b; }
    .verified-footer strong { color:#cbd5e1; }
    .total-row { background:#1a2744; }
    .section-label { color:#64748b; }
}
</style>
</head>
<body>

<div class="card">

    <div class="card-header">
        <div class="valid-badge"><span class="dot"></span> SPK Terverifikasi</div>
        <div class="spk-title">Surat Perintah Kerja</div>
        <div class="spk-number">{{ $salesOrder->spk_number }}</div>
    </div>

    <div class="card-body">

        {{-- Status & Kategori --}}
        <div class="section">
            <div class="section-label">Status Pekerjaan</div>
            <div class="d-flex gap-2" style="display:flex;gap:8px;flex-wrap:wrap">
                <span class="status-chip chip-{{ $salesOrder->status }}">
                    ● {{ $salesOrder->status_label }}
                </span>
                <span class="category-chip">{{ $salesOrder->category_label }}</span>
            </div>
        </div>

        {{-- Info Customer --}}
        <div class="section">
            <div class="section-label">Data Customer</div>
            <div class="info-row">
                <span class="lbl">Nama</span>
                <span class="val">{{ $salesOrder->prospect_name }}</span>
            </div>
            @if($salesOrder->prospect_phone)
            <div class="info-row">
                <span class="lbl">Telepon</span>
                <span class="val">{{ $salesOrder->prospect_phone }}</span>
            </div>
            @endif
        </div>

        {{-- Kendaraan --}}
        @if($salesOrder->vehicle_brand || $salesOrder->vehicle_plate)
        <div class="section">
            <div class="section-label">
                {{ $salesOrder->category === 'building_film' ? 'Data Bangunan' : 'Data Kendaraan' }}
            </div>
            @if($salesOrder->vehicle_brand)
            <div class="info-row">
                <span class="lbl">Kendaraan</span>
                <span class="val">{{ $salesOrder->vehicle_brand }} {{ $salesOrder->vehicle_model }}
                    @if($salesOrder->vehicle_year) ({{ $salesOrder->vehicle_year }}) @endif
                </span>
            </div>
            @endif
            @if($salesOrder->vehicle_plate)
            <div class="info-row">
                <span class="lbl">Plat Nomor</span>
                <span class="val" style="font-size:16px;font-weight:800;letter-spacing:2px">
                    {{ strtoupper($salesOrder->vehicle_plate) }}
                </span>
            </div>
            @endif
            @if($salesOrder->usesFilmSelection() && $salesOrder->film_label)
            <div class="info-row">
                <span class="lbl">Tipe Film / Kegelapan</span>
                <span class="val">{{ $salesOrder->film_label }}</span>
            </div>
            @endif
        </div>
        @endif

        {{-- Jadwal --}}
        <div class="section">
            <div class="section-label">Jadwal</div>
            <div class="info-row">
                <span class="lbl">Tanggal SO</span>
                <span class="val">{{ $salesOrder->date->format('d M Y') }}</span>
            </div>
            @if($salesOrder->scheduled_date)
            <div class="info-row">
                <span class="lbl">Jadwal Pasang</span>
                <span class="val">{{ $salesOrder->scheduled_date->format('d M Y') }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="lbl">Cabang</span>
                <span class="val">{{ $salesOrder->branch?->name ?? '—' }}</span>
            </div>
        </div>

        {{-- Items --}}
        @if($salesOrder->items->isNotEmpty())
        <div class="section-label" style="margin-bottom:8px">Rincian Pekerjaan</div>
        <div class="items-section">
            @foreach($salesOrder->items as $item)
            <div class="item-row">
                <div>
                    <span class="item-name">{{ $item->description }}</span>
                    @if($item->specifications)
                        <span class="item-spec">{{ $item->specifications }}</span>
                    @endif
                    <span class="item-spec">{{ rtrim(rtrim(number_format($item->quantity,2),'0'),'.') }} {{ $item->unit }}</span>
                </div>
                <span class="item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>

        <div class="total-row">
            <span class="total-label">Total</span>
            <span>Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}</span>
        </div>
        @endif

        {{-- Tim --}}
        @if($salesOrder->category === 'building_film')
            @if($salesOrder->technicians->isNotEmpty())
            <div class="section">
                <div class="section-label">Tim Pelaksana</div>
                <div class="staff-section">
                    @foreach($salesOrder->technicians as $tech)
                    <div class="staff-box">
                        <div class="staff-role">Teknisi</div>
                        <div class="staff-name">{{ $tech->name }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @elseif($salesOrder->tukang || $salesOrder->kenek)
        <div class="section">
            <div class="section-label">Tim Pelaksana</div>
            <div class="staff-section">
                @if($salesOrder->tukang)
                <div class="staff-box">
                    <div class="staff-role">Teknisi</div>
                    <div class="staff-name">{{ $salesOrder->tukang->name }}</div>
                </div>
                @endif
                @if($salesOrder->kenek)
                <div class="staff-box">
                    <div class="staff-role">Kenek</div>
                    <div class="staff-name">{{ $salesOrder->kenek->name }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($salesOrder->notes)
        <div class="section">
            <div class="section-label">Catatan</div>
            <p style="font-size:13px;color:#374151;line-height:1.6;white-space:pre-line">{{ $salesOrder->notes }}</p>
        </div>
        @endif

    </div>

    <div class="verified-footer">
        <div class="checkmark">✓</div>
        <p>Dokumen ini valid dan dapat diverifikasi secara digital</p>
        <strong>Diterbitkan {{ $salesOrder->spk_issued_at->format('d M Y, H:i') }}</strong>
    </div>

</div>

</body>
</html>
