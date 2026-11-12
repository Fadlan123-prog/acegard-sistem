@php /** @var array $breakdown  @var string $variant  web|print */ $variant = $variant ?? 'web'; @endphp
@if($variant === 'print')
    @foreach($breakdown as $pack)
        <div style="margin-top:3pt">
            @if($pack['vehicle'] || $pack['package'])
                <div style="font-weight:bold">{{ trim(($pack['vehicle'] ? $pack['vehicle'] . ' — ' : '') . $pack['package'], ' —') }}</div>
            @endif
            <table style="width:100%;border-collapse:collapse;font-size:9pt">
                @foreach($pack['parts'] as $part)
                    <tr>
                        <td style="padding:1pt 4pt 1pt 0;width:38%;color:#555">{{ $part['label'] }}</td>
                        <td style="padding:1pt 0">{{ $part['film'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach
@else
    <style>
        .fb-card { border:1px solid var(--border-color, #e5e7eb); border-radius:12px; padding:10px 12px; margin-bottom:10px; background:var(--white, #fff); }
        .fb-head { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:6px; }
        .fb-title { font-size:13px; font-weight:700; display:flex; align-items:center; gap:6px; min-width:0; }
        .fb-count { font-size:11.5px; color:var(--neutral-500, #6b7280); white-space:nowrap; }
        .fb-row { display:grid; grid-template-columns:minmax(110px, 34%) 1fr; gap:8px; align-items:center; padding:6px 0; border-top:1px solid var(--border-color, #f1f5f9); font-size:13px; }
        .fb-row:first-of-type { border-top:0; }
        .fb-part { display:flex; align-items:center; gap:6px; }
        .fb-part iconify-icon { flex:none; font-size:16px; }
        .fb-on .fb-part iconify-icon { color:#16A34A; }
        .fb-off { opacity:.55; }
        .fb-off .fb-part iconify-icon { color:#9ca3af; }
        .fb-film { font-weight:600; }
        .fb-film small { display:block; font-weight:500; color:var(--neutral-500, #6b7280); }
        @media (max-width: 575.98px) { .fb-row { grid-template-columns:1fr; gap:2px; } }
    </style>
    @foreach($breakdown as $pack)
        @php $installed = collect($pack['parts'])->filter(fn ($x) => $x['film'])->count(); @endphp
        <div class="fb-card">
            <div class="fb-head">
                <div class="fb-title">
                    <iconify-icon icon="mdi:car-outline" aria-hidden="true"></iconify-icon>
                    <span class="text-truncate">{{ trim(($pack['vehicle'] ? $pack['vehicle'] . ' — ' : '') . $pack['package'], ' —') ?: 'Paket kaca film' }}</span>
                </div>
                <span class="fb-count">{{ $installed }} dari {{ count($pack['parts']) }} bagian</span>
            </div>
            @foreach($pack['parts'] as $part)
                @php
                    $film = $part['film'];
                    // "NOTCH UV 400 NV70 (kegelapan 20%)" -> tipe + kode di baris atas, kegelapan di baris bawah
                    $main = $film ? trim(preg_replace('/\s*\(kegelapan[^)]*\)/i', '', $film)) : null;
                    $dark = $film && preg_match('/\(kegelapan\s*([^)]*)\)/i', $film, $m) ? 'Kegelapan ' . $m[1] : null;
                @endphp
                <div class="fb-row {{ $film ? 'fb-on' : 'fb-off' }}">
                    <div class="fb-part">
                        <iconify-icon icon="{{ $film ? 'mdi:check-circle' : 'mdi:minus-circle-outline' }}" aria-hidden="true"></iconify-icon>
                        <span>{{ $part['label'] }}</span>
                        <span class="visually-hidden">{{ $film ? 'dipasang' : 'tidak dipasang' }}</span>
                    </div>
                    <div class="fb-film">
                        @if($film)
                            {{ $main }}@if($dark)<small>{{ $dark }}</small>@endif
                        @else
                            <span class="text-muted fw-normal">Tidak dipasang</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
@endif
