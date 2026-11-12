@extends('dashboard.index')

@section('title', 'Status Pembayaran Customer')
@section('route-breadcrumb', 'finance.payment-status')
@section('breadcrumb', 'Status Pembayaran Customer')

@section('content')
@php
  $fmt   = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
  $get   = fn ($k, $f) => (float) ($summary[$k]->{$f} ?? 0);
  $count = fn ($k) => (int) ($summary[$k]->n ?? 0);

  $outstandingAmount = $get('unpaid', 'remaining') + $get('partial', 'remaining');
  $outstandingCount  = $count('unpaid') + $count('partial');
  $current = $filters['status'] ?? 'outstanding';

  $typeLabel = ['car' => 'Mobil', 'building' => 'Gedung', 'ppf' => 'PPF'];
  $typeIcon  = ['car' => 'mdi:car-outline', 'building' => 'mdi:office-building-outline', 'ppf' => 'mdi:shield-car'];
  $typeRoute = ['car' => 'invoice.show', 'building' => 'invoice.building.show', 'ppf' => 'ppf-invoices.show'];
  $statusMeta = [
    'paid'    => ['Lunas', 'success'],
    'partial' => ['Sebagian', 'warning'],
    'unpaid'  => ['Belum bayar', 'danger'],
  ];

  $tabs = [
    'outstanding' => ['Belum lunas', $outstandingCount],
    'unpaid'      => ['Belum bayar', $count('unpaid')],
    'partial'     => ['Sebagian', $count('partial')],
    'paid'        => ['Lunas', $count('paid')],
  ];
  $keep = array_filter(request()->only(['search', 'type', 'from', 'to']));
  $icon = fn ($name) => '<iconify-icon icon="' . $name . '"></iconify-icon>';
@endphp

<style>
  .ps-tabs { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1rem; }
  .ps-tab { display:inline-flex; align-items:center; gap:.4rem; padding:.45rem .85rem; border:1px solid var(--ui-border, #e5e7eb);
            border-radius:999px; font-size:13px; font-weight:600; color:var(--ui-text-2, #4b5563); text-decoration:none; background:var(--ui-surface, #fff); }
  .ps-tab:hover { border-color:#2563EB; color:#2563EB; }
  .ps-tab.is-active { background:#2563EB; border-color:#2563EB; color:#fff; }
  .ps-tab .n { font-size:11px; padding:1px 7px; border-radius:999px; background:rgba(0,0,0,.08); font-variant-numeric:tabular-nums; }
  .ps-tab.is-active .n { background:rgba(255,255,255,.25); }
  .ps-num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
  .ps-bar { height:5px; border-radius:999px; background:var(--ui-border, #e5e7eb); overflow:hidden; margin-top:5px; min-width:90px; }
  .ps-bar > span { display:block; height:100%; background:#16A34A; border-radius:999px; }
  .ps-sub { font-size:12px; color:var(--ui-text-2, #6b7280); }
  .ps-age-late { color:#DC2626; font-weight:700; }
  .ps-table th { font-size:12px; text-transform:uppercase; letter-spacing:.03em; white-space:nowrap; }
  .ps-card-link { text-decoration:none; display:block; color:inherit; }
  .ps-card-link:focus-visible, .ps-tab:focus-visible { outline:2px solid #2563EB; outline-offset:2px; }
  @media (max-width: 767.98px) {
    .ps-table thead { display:none; }
    .ps-table tr { display:block; border:1px solid var(--ui-border, #e5e7eb); border-radius:12px; padding:.75rem; margin-bottom:.75rem; }
    .ps-table td { display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:.3rem 0 !important; border:0 !important; text-align:right; }
    .ps-table td::before { content:attr(data-label); font-size:12px; color:var(--ui-text-2, #6b7280); text-align:left; }
    .ps-table td.ps-first { display:block; text-align:left; }
    .ps-table td.ps-first::before { display:none; }
  }
</style>

{{-- Ringkasan: klik kartu untuk memfilter status --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <a class="ps-card-link" href="{{ route('finance.payment-status', $keep + ['status' => 'outstanding']) }}">
      <x-ui.stats-card label="Total belum terbayar" :value="$fmt($outstandingAmount)" :icon="$icon('mdi:cash-clock')" color="purple" accent>
        <span class="ps-sub">{{ $outstandingCount }} invoice</span>
      </x-ui.stats-card>
    </a>
  </div>
  <div class="col-6 col-xl-3">
    <a class="ps-card-link" href="{{ route('finance.payment-status', $keep + ['status' => 'unpaid']) }}">
      <x-ui.stats-card label="Belum bayar" :value="$count('unpaid') . ' invoice'" :icon="$icon('mdi:alert-circle-outline')" color="red" accent>
        <span class="ps-sub">Sisa {{ $fmt($get('unpaid', 'remaining')) }}</span>
      </x-ui.stats-card>
    </a>
  </div>
  <div class="col-6 col-xl-3">
    <a class="ps-card-link" href="{{ route('finance.payment-status', $keep + ['status' => 'partial']) }}">
      <x-ui.stats-card label="Dibayar sebagian" :value="$count('partial') . ' invoice'" :icon="$icon('mdi:progress-clock')" color="amber" accent>
        <span class="ps-sub">Sisa {{ $fmt($get('partial', 'remaining')) }}</span>
      </x-ui.stats-card>
    </a>
  </div>
  <div class="col-6 col-xl-3">
    <a class="ps-card-link" href="{{ route('finance.payment-status', $keep + ['status' => 'paid']) }}">
      <x-ui.stats-card label="Lunas" :value="$count('paid') . ' invoice'" :icon="$icon('mdi:check-circle-outline')" color="green" accent>
        <span class="ps-sub">{{ $fmt($get('paid', 'total')) }}</span>
      </x-ui.stats-card>
    </a>
  </div>
</div>

<x-ui.card>
  {{-- Tab status (jumlah mengikuti filter lain) --}}
  <nav class="ps-tabs" aria-label="Filter status pembayaran">
    @foreach($tabs as $key => [$label, $n])
      <a href="{{ route('finance.payment-status', $keep + ['status' => $key]) }}"
         class="ps-tab {{ $current === $key ? 'is-active' : '' }}" @if($current === $key) aria-current="page" @endif>
        {{ $label }} <span class="n">{{ $n }}</span>
      </a>
    @endforeach
  </nav>

  <form method="GET" class="row g-2 align-items-end mb-3">
    <input type="hidden" name="status" value="{{ $current }}">
    <div class="col-12 col-md-4">
      <label class="ui-label" for="ps-search">Cari customer</label>
      <input id="ps-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control"
             placeholder="Nama, no. invoice, atau no. HP">
    </div>
    <div class="col-6 col-md-2">
      <label class="ui-label" for="ps-type">Jenis</label>
      <select id="ps-type" name="type" class="form-select">
        <option value="">Semua</option>
        @foreach($typeLabel as $v => $l)
          <option value="{{ $v }}" {{ ($filters['type'] ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="ui-label" for="ps-from">Dari tanggal</label>
      <input id="ps-from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
    </div>
    <div class="col-6 col-md-2">
      <label class="ui-label" for="ps-to">Sampai tanggal</label>
      <input id="ps-to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
    </div>
    <div class="col-6 col-md-2 d-flex gap-2">
      <x-ui.button type="submit" variant="primary" class="flex-fill">Terapkan</x-ui.button>
      @if($keep)
        <x-ui.button :href="route('finance.payment-status', ['status' => $current])" variant="outline">Reset</x-ui.button>
      @endif
    </div>
  </form>

  <div class="table-responsive">
    <table class="table ps-table align-middle mb-0">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Invoice</th>
          <th class="ps-num">Total</th>
          <th>Pembayaran</th>
          <th class="ps-num">Sisa</th>
          <th>Status</th>
          <th class="ps-num">Umur</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          @php
            $date = \Carbon\Carbon::parse($r->invoice_date);
            $age = (int) $date->diffInDays(now());
            $total = (float) $r->total_price;
            $remaining = (float) $r->remaining_payment;
            $paidAmount = max(0, $total - $remaining);
            $pct = $total > 0 ? min(100, (int) round($paidAmount / $total * 100)) : 0;
            [$label, $variant] = $statusMeta[$r->pay_status] ?? [$r->pay_status, 'neutral'];
          @endphp
          <tr>
            <td class="ps-first">
              <div class="fw-semibold">{{ $r->customer_name }}</div>
              @if($r->phone)<div class="ps-sub">{{ $r->phone }}</div>@endif
            </td>
            <td data-label="Invoice">
              <div>
                <a href="{{ route($typeRoute[$r->type], $r->id) }}" class="fw-semibold">{{ $r->invoice_number }}</a>
                <div class="ps-sub d-flex align-items-center gap-1 justify-content-md-start justify-content-end">
                  <iconify-icon icon="{{ $typeIcon[$r->type] }}" aria-hidden="true"></iconify-icon>
                  {{ $typeLabel[$r->type] }} &middot; {{ $date->translatedFormat('d M Y') }}
                </div>
              </div>
            </td>
            <td data-label="Total" class="ps-num">{{ $fmt($total) }}</td>
            <td data-label="Terbayar">
              <div class="w-100">
                <div class="ps-num">{{ $fmt($paidAmount) }}</div>
                <div class="ps-bar" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Terbayar {{ $pct }}%"><span style="width: {{ $pct }}%"></span></div>
              </div>
            </td>
            <td data-label="Sisa" class="ps-num fw-semibold">{{ $r->pay_status === 'paid' ? '—' : $fmt($remaining) }}</td>
            <td data-label="Status"><x-ui.badge :variant="$variant" dot>{{ $label }}</x-ui.badge></td>
            <td data-label="Umur" class="ps-num">
              @if($r->pay_status === 'paid')
                <span class="ps-sub">—</span>
              @else
                <span class="{{ $age > 30 ? 'ps-age-late' : 'ps-sub' }}">{{ $age }} hari</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="py-5">
              <x-ui.empty-state title="Tidak ada invoice yang cocok" description="Coba ubah status, jenis, atau rentang tanggal." />
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">{{ $rows->links() }}</div>
</x-ui.card>
@endsection
