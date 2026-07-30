@extends('layout.index')

@section('title', 'Notifikasi Otomatis')
@section('route-breadcrumb', route('notification-config.index'))
@section('breadcrumb', 'Pengaturan Notifikasi Otomatis')

@section('content')

@php
    $typeIcons = [
        'hutang_due'      => ['icon'=>'mdi:calendar-star',   'color'=>'#ef4444', 'bg'=>'rgba(239,68,68,.1)'],
        'piutang_due'     => ['icon'=>'mdi:calendar-check-outline',    'color'=>'#f59e0b', 'bg'=>'rgba(245,158,11,.1)'],
        'warranty_expiry' => ['icon'=>'mdi:shield-key-outline',    'color'=>'#6366f1', 'bg'=>'rgba(99,102,241,.1)'],
        'low_film_stock'  => ['icon'=>'mdi:filmstrip',              'color'=>'#10b981', 'bg'=>'rgba(16,185,129,.1)'],
        'low_goods_stock' => ['icon'=>'mdi:archive-outline',           'color'=>'#8b5cf6', 'bg'=>'rgba(139,92,246,.1)'],
    ];
    $timeBased = \App\Models\NotificationConfig::$timeBased;
@endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        @if ($isSuperadmin)
        <form method="GET" class="d-inline-flex gap-2">
            <select name="branch_id" class="form-select form-select-sm" style="min-width:180px" onchange="this.form.submit()">
                <option value="">Global (Semua Cabang)</option>
                @foreach ($branches as $br)
                    <option value="{{ $br->id }}" {{ ($filters['branch_id'] ?? '') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                @endforeach
            </select>
        </form>
        @endif
    </div>
    <x-ui.button href="{{ route('notification-config.logs', $filters) }}" variant="outline" size="sm">
        <iconify-icon icon="mdi:history" class="me-1"></iconify-icon>Riwayat Kiriman
    </x-ui.button>
</div>

<x-ui.alert type="info" class="mb-4 d-flex gap-2" style="font-size:13px">
    <iconify-icon icon="mdi:information-outline" class="fs-5 flex-shrink-0"></iconify-icon>
    <div>
        Notifikasi dikirim otomatis setiap hari pukul <strong>07:00</strong> via <code>php artisan notify:scheduled</code>.
        Pastikan Laravel Scheduler sudah dikonfigurasi di cron server:
        <code class="ms-1">* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1</code>
    </div>
</x-ui.alert>

<form method="POST" action="{{ route('notification-config.update') }}">
    @csrf
    @if ($isSuperadmin && isset($filters['branch_id']))
        <input type="hidden" name="branch_id" value="{{ $filters['branch_id'] }}">
    @endif

    <div class="row g-4">
        @foreach ($configs as $type => $config)
        @php $meta = $typeIcons[$type]; @endphp
        <div class="col-12">
            <x-ui.card style="border:1px solid {{ $config->is_active ? '#e0e7ff' : '#f1f5f9' }}">
                @slot('header')
                    <div class="d-flex align-items-center gap-3 py-1">
                        <div style="width:36px;height:36px;border-radius:9px;background:{{ $meta['bg'] }};display:flex;align-items:center;justify-content:center;color:{{ $meta['color'] }};font-size:17px;flex-shrink:0">
                            <iconify-icon icon="{{ $meta['icon'] }}"></iconify-icon>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ \App\Models\NotificationConfig::$types[$type] }}</div>
                            @if (in_array($type, $timeBased))
                                <div class="text-muted" style="font-size:11px">Dikirim H-N sebelum jatuh tempo/berakhir</div>
                            @else
                                <div class="text-muted" style="font-size:11px">Dikirim setiap hari jika stok di bawah batas</div>
                            @endif
                        </div>
                        <div class="form-check form-switch ms-auto">
                            <input class="form-check-input" type="checkbox" name="configs[{{ $type }}][is_active]"
                                   id="active_{{ $type }}" value="1" {{ $config->is_active ? 'checked' : '' }}
                                   onchange="toggleCard('{{ $type }}', this.checked)">
                            <label class="form-check-label fw-medium" for="active_{{ $type }}">
                                {{ $config->is_active ? 'Aktif' : 'Nonaktif' }}
                            </label>
                        </div>
                    </div>
                @endslot

                <div id="card_{{ $type }}" class="{{ $config->is_active ? '' : 'd-none' }}">
                    <div class="row g-3">

                        {{-- Days before (time-based only) --}}
                        @if (in_array($type, $timeBased))
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-medium">Ingatkan H-</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="configs[{{ $type }}][days_before]"
                                       class="form-control" min="0" max="90"
                                       value="{{ old("configs.{$type}.days_before", $config->days_before ?? 3) }}">
                                <span class="input-group-text">hari</span>
                            </div>
                        </div>
                        @endif

                        {{-- Channels --}}
                        <div class="col-12 col-md-{{ in_array($type, $timeBased) ? '9' : '12' }}">
                            <label class="form-label small fw-medium">Kirim Via</label>
                            <div class="d-flex gap-3 flex-wrap mt-1">
                                @foreach (\App\Models\NotificationConfig::$channels as $ch => $chLabel)
                                <div class="form-check">
                                    <input class="form-check-input channel-check-{{ $type }}" type="checkbox"
                                           name="configs[{{ $type }}][channel_{{ $ch }}]" value="1"
                                           id="{{ $type }}_{{ $ch }}"
                                           {{ in_array($ch, $config->channels ?? ['in_app']) ? 'checked' : '' }}
                                           onchange="toggleChannelDetails('{{ $type }}')">
                                    <label class="form-check-label" for="{{ $type }}_{{ $ch }}">{{ $chLabel }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Email Recipients --}}
                        <div class="col-12 col-md-6 channel-detail-{{ $type }}-email {{ in_array('email', $config->channels ?? []) ? '' : 'd-none' }}">
                            <label class="form-label small fw-medium">Alamat Email Penerima</label>
                            <textarea name="configs[{{ $type }}][email_recipients]" rows="3"
                                      class="form-control form-control-sm"
                                      placeholder="Satu email per baris">{{ implode("\n", $config->email_recipients ?? []) }}</textarea>
                            <div class="form-text">Satu alamat email per baris.</div>
                        </div>

                        {{-- WhatsApp --}}
                        <div class="col-12 col-md-6 channel-detail-{{ $type }}-whatsapp {{ in_array('whatsapp', $config->channels ?? []) ? '' : 'd-none' }}">
                            <label class="form-label small fw-medium">Nomor WhatsApp Penerima</label>
                            <textarea name="configs[{{ $type }}][whatsapp_numbers]" rows="2"
                                      class="form-control form-control-sm"
                                      placeholder="628xxxxxxxxxx (satu per baris)">{{ implode("\n", $config->whatsapp_numbers ?? []) }}</textarea>
                            <div class="row g-2 mt-1">
                                <div class="col-7">
                                    <input type="url" name="configs[{{ $type }}][whatsapp_api_url]"
                                           class="form-control form-control-sm" placeholder="API URL (Fonnte, Wablas, dll)"
                                           value="{{ $config->whatsapp_api_url ?? '' }}">
                                </div>
                                <div class="col-5">
                                    <input type="text" name="configs[{{ $type }}][whatsapp_api_token]"
                                           class="form-control form-control-sm" placeholder="Token API"
                                           value="{{ $config->whatsapp_api_token ?? '' }}">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </x-ui.card>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        <x-ui.button type="submit" variant="primary">
            <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Simpan Pengaturan
        </x-ui.button>
    </div>
</form>

{{-- Recent Logs --}}
@if ($recentLogs->isNotEmpty())
<x-ui.card class="mt-5">
    @slot('header')
        <div class="d-flex align-items-center justify-content-between">
            <div class="ui-card-title">Kiriman Terakhir</div>
            <x-ui.button href="{{ route('notification-config.logs', $filters) }}" variant="outline" size="sm">Lihat Semua</x-ui.button>
        </div>
    @endslot
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:12px">
            <thead style="background:#fafafa">
                <tr>
                    <th class="ps-4 py-2 text-muted fw-medium border-bottom">Waktu</th>
                    <th class="py-2 text-muted fw-medium border-bottom">Tipe</th>
                    <th class="py-2 text-muted fw-medium border-bottom">Channel</th>
                    <th class="py-2 text-muted fw-medium border-bottom">Penerima</th>
                    <th class="py-2 text-muted fw-medium border-bottom pe-4">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentLogs as $log)
                <tr>
                    <td class="ps-4 py-2 text-muted">{{ $log->created_at->format('d/m H:i') }}</td>
                    <td class="py-2">{{ \App\Models\NotificationConfig::$types[$log->type] ?? $log->type }}</td>
                    <td class="py-2 text-muted">{{ \App\Models\NotificationConfig::$channels[$log->channel] ?? $log->channel }}</td>
                    <td class="py-2 text-muted" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $log->recipient ?? '—' }}</td>
                    <td class="py-2 pe-4">
                        <x-ui.badge :variant="$log->status === 'sent' ? 'success' : 'danger'" size="sm">
                            {{ $log->status === 'sent' ? 'Terkirim' : 'Gagal' }}
                        </x-ui.badge>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-ui.card>
@endif

@push('page-scripts')
<script>
function toggleCard(type, active) {
    const card = document.getElementById('card_' + type);
    const label = document.querySelector('#active_' + type).closest('.form-check').querySelector('.form-check-label');
    if (active) { card.classList.remove('d-none'); label.textContent = 'Aktif'; }
    else        { card.classList.add('d-none');    label.textContent = 'Nonaktif'; }
}

function toggleChannelDetails(type) {
    ['email', 'whatsapp'].forEach(ch => {
        const cb = document.getElementById(type + '_' + ch);
        document.querySelectorAll('.channel-detail-' + type + '-' + ch).forEach(el => {
            el.classList.toggle('d-none', !cb?.checked);
        });
    });
}
</script>
@endpush

@endsection
