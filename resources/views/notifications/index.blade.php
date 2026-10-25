@extends('dashboard.index')

@section('title', 'Semua Notifikasi')
@section('route-breadcrumb', 'notifications.index')
@section('breadcrumb', 'Notifikasi')

@section('content')
@php
$notifMeta = [
    'commission_period_due'    => ['icon' => 'mdi:calendar-clock',  'color' => '#d97706', 'label' => 'Komisi Jatuh Tempo'],
    'job_assigned'             => ['icon' => 'mdi:car-wrench',       'color' => '#2563eb', 'label' => 'Pekerjaan Baru'],
    'unreleased_commission'    => ['icon' => 'mdi:cash-remove',      'color' => '#dc2626', 'label' => 'Komisi Belum Dirilis'],
    'warranty_claim_submitted' => ['icon' => 'mdi:shield-alert',     'color' => '#7c3aed', 'label' => 'Klaim Garansi'],
    'automated_alert'          => ['icon' => 'lucide:bell-ring',     'color' => '#0891b2', 'label' => 'Notifikasi Otomatis'],
];
@endphp

<x-ui.card class="h-100">
    @slot('header')
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="ui-card-title">Semua Notifikasi</div>
                @if(auth()->user()->unreadNotifications()->count() > 0)
                <x-ui.badge variant="danger">{{ auth()->user()->unreadNotifications()->count() }} belum dibaca</x-ui.badge>
                @endif
            </div>
            <x-ui.button variant="outline" size="sm" href="{{ route('notifications.readAll') }}" class="d-flex align-items-center gap-1">
                <iconify-icon icon="mdi:check-all"></iconify-icon>
                Tandai semua sudah dibaca
            </x-ui.button>
        </div>
    @endslot

    @forelse($notifs as $n)
    @php
    $d    = $n->data;
    $type = $d['type'] ?? (isset($d['claim_id']) ? 'warranty_claim_submitted' : 'unknown');
    $meta = $notifMeta[$type] ?? ['icon' => 'mdi:bell', 'color' => '#6b7280', 'label' => 'Notifikasi'];
    $msg  = $d['message'] ?? ($d['complaint'] ?? '');
    $isRead = $n->read_at !== null;
    @endphp

    <a href="{{ route('notifications.open', $n->id, false) }}"
       class="d-flex align-items-start gap-3 px-24 py-16 border-bottom text-decoration-none {{ $isRead ? '' : 'bg-primary-50' }}"
       style="transition: background .15s;">
        {{-- Icon circle --}}
        <span class="flex-shrink-0 mt-1 d-flex align-items-center justify-content-center rounded-circle"
              style="width:42px;height:42px;min-width:42px;background:{{ $meta['color'] }}1a;">
            <iconify-icon icon="{{ $meta['icon'] }}" style="color:{{ $meta['color'] }};font-size:20px;"></iconify-icon>
        </span>

        <div class="flex-grow-1 min-w-0">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge rounded-pill fw-medium px-8 py-4 text-xs"
                          style="background:{{ $meta['color'] }}1a;color:{{ $meta['color'] }};">
                        {{ $meta['label'] }}
                    </span>
                    @if(!$isRead)
                    <span class="d-inline-block rounded-circle bg-danger" style="width:7px;height:7px;"></span>
                    @endif
                </div>
                <small class="text-muted text-nowrap" style="font-size:.75rem;">
                    {{ $n->created_at->diffForHumans() }}
                </small>
            </div>

            @if($msg)
            <p class="mb-1 text-secondary-light" style="font-size:.85rem;line-height:1.45;">{{ $msg }}</p>
            @endif

            {{-- Extra detail per type --}}
            @if($type === 'commission_period_due')
            <div class="d-flex gap-3 flex-wrap" style="font-size:.75rem;color:#6b7280;">
                @if(!empty($d['unreleased_count']) && $d['unreleased_count'] > 0)
                <span><iconify-icon icon="mdi:file-clock-outline" class="me-1"></iconify-icon>{{ $d['unreleased_count'] }} belum rilis</span>
                @endif
                @if(!empty($d['unpaid_count']) && $d['unpaid_count'] > 0)
                <span><iconify-icon icon="mdi:account-clock-outline" class="me-1"></iconify-icon>{{ $d['unpaid_count'] }} belum terbayar</span>
                @endif
                @if(!empty($d['days_until_due']))
                <span><iconify-icon icon="mdi:calendar-clock" class="me-1"></iconify-icon>{{ $d['days_until_due'] }} hari lagi</span>
                @endif
            </div>
            @elseif($type === 'job_assigned')
            <div class="d-flex gap-3 flex-wrap" style="font-size:.75rem;color:#6b7280;">
                @if(!empty($d['vehicle']))
                <span><iconify-icon icon="mdi:car-outline" class="me-1"></iconify-icon>{{ $d['vehicle'] }}</span>
                @endif
                @if(!empty($d['plat']))
                <span><iconify-icon icon="mdi:card-text-outline" class="me-1"></iconify-icon>{{ $d['plat'] }}</span>
                @endif
                @if(!empty($d['invoice_number']))
                <span><iconify-icon icon="mdi:receipt-outline" class="me-1"></iconify-icon>{{ $d['invoice_number'] }}</span>
                @endif
            </div>
            @elseif($type === 'unreleased_commission')
            <div class="d-flex gap-3 flex-wrap" style="font-size:.75rem;color:#6b7280;">
                @if(!empty($d['invoice_number']))
                <span><iconify-icon icon="mdi:receipt-outline" class="me-1"></iconify-icon>{{ $d['invoice_number'] }}</span>
                @endif
                @if(!empty($d['customer_name']))
                <span><iconify-icon icon="mdi:account-outline" class="me-1"></iconify-icon>{{ $d['customer_name'] }}</span>
                @endif
                @if(!empty($d['vehicle']))
                <span><iconify-icon icon="mdi:car-outline" class="me-1"></iconify-icon>{{ $d['vehicle'] }}</span>
                @endif
            </div>
            @elseif($type === 'warranty_claim_submitted')
            <div class="d-flex gap-3 flex-wrap" style="font-size:.75rem;color:#6b7280;">
                @if(!empty($d['claim_number']))
                <span><iconify-icon icon="mdi:pound" class="me-1"></iconify-icon>{{ $d['claim_number'] }}</span>
                @endif
                @if(!empty($d['warranty_type']))
                <span class="text-uppercase"><iconify-icon icon="mdi:tag-outline" class="me-1"></iconify-icon>{{ $d['warranty_type'] }}</span>
                @endif
                @if(!empty($d['status']))
                <span><iconify-icon icon="mdi:circle-small"></iconify-icon>{{ $d['status'] }}</span>
                @endif
            </div>
            @elseif($type === 'automated_alert')
            <div class="d-flex gap-3 flex-wrap" style="font-size:.75rem;color:#6b7280;">
                @if(!empty($d['label']))
                <span><iconify-icon icon="mdi:tag-outline" class="me-1"></iconify-icon>{{ $d['label'] }}</span>
                @endif
            </div>
            @endif

            <div class="mt-1" style="font-size:.72rem;color:#9ca3af;">
                {{ $n->created_at->format('d M Y, H:i') }}
                @if($isRead)
                · <span class="text-success-600">Sudah dibaca</span>
                @endif
            </div>
        </div>
    </a>
    @empty
    <x-ui.empty-state title="Belum ada notifikasi." />
    @endforelse

    @slot('footer')
        @if($notifs->hasPages()){{ $notifs->links() }}@endif
    @endslot
</x-ui.card>
@endsection
