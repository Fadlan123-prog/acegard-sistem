@extends('dashboard.index')

@section('title', 'Status Pembayaran Customer')
@section('route-breadcrumb', 'finance.payment-status')
@section('breadcrumb', 'Status Pembayaran Customer')

@section('content')
@php
  $fmt = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
  $get = fn ($k, $f) => (float) ($summary[$k]->{$f} ?? 0);
  $count = fn ($k) => (int) ($summary[$k]->n ?? 0);
  $outstanding = $get('unpaid', 'remaining') + $get('partial', 'remaining');
  $typeLabel = ['car' => 'Mobil', 'building' => 'Gedung', 'ppf' => 'PPF'];
  $typeRoute = ['car' => 'invoice.show', 'building' => 'invoice.building.show', 'ppf' => 'ppf-invoices.show'];
@endphp

<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3"><x-ui.stats-card label="Lunas" :value="$count('paid') . ' invoice'" icon="mdi:check-circle-outline" color="green" /></div>
  <div class="col-6 col-lg-3"><x-ui.stats-card label="Sebagian" :value="$count('partial') . ' invoice'" icon="mdi:progress-clock" color="amber" /></div>
  <div class="col-6 col-lg-3"><x-ui.stats-card label="Belum Bayar" :value="$count('unpaid') . ' invoice'" icon="mdi:alert-circle-outline" color="red" /></div>
  <div class="col-6 col-lg-3"><x-ui.stats-card label="Total Belum Terbayar" :value="$fmt($outstanding)" icon="mdi:cash-clock" color="purple" /></div>
</div>

<x-ui.card>
  <form method="GET" class="row g-2 align-items-end mb-3">
    <div class="col-sm-6 col-lg-3">
      <label class="form-label small">Cari (nama / no. invoice / HP)</label>
      <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control">
    </div>
    <div class="col-sm-6 col-lg-2">
      <label class="form-label small">Status</label>
      <select name="status" class="form-select">
        @foreach(['outstanding' => 'Belum lunas (semua)', 'unpaid' => 'Belum bayar', 'partial' => 'Sebagian', 'paid' => 'Lunas'] as $v => $l)
          <option value="{{ $v }}" {{ ($filters['status'] ?? 'outstanding') === $v ? 'selected' : '' }}>{{ $l }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-sm-6 col-lg-2">
      <label class="form-label small">Jenis</label>
      <select name="type" class="form-select">
        <option value="">Semua</option>
        @foreach($typeLabel as $v => $l)
          <option value="{{ $v }}" {{ ($filters['type'] ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-6 col-lg-2">
      <label class="form-label small">Dari</label>
      <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
    </div>
    <div class="col-6 col-lg-2">
      <label class="form-label small">Sampai</label>
      <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
    </div>
    <div class="col-12 col-lg-1"><x-ui.button type="submit" variant="primary" class="w-100">Cari</x-ui.button></div>
  </form>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Customer</th><th>Invoice</th><th>Jenis</th><th>Tanggal</th>
          <th class="text-end">Total</th><th class="text-end">Terbayar</th><th class="text-end">Sisa</th>
          <th>Status</th><th>Umur</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          @php
            $age = \Carbon\Carbon::parse($r->invoice_date)->diffInDays(now());
            $paidAmount = max(0, (float) $r->total_price - (float) $r->remaining_payment);
            $variant = ['paid' => 'success', 'partial' => 'warning', 'unpaid' => 'danger'][$r->pay_status] ?? 'neutral';
            $label = ['paid' => 'Lunas', 'partial' => 'Sebagian', 'unpaid' => 'Belum bayar'][$r->pay_status] ?? $r->pay_status;
          @endphp
          <tr>
            <td>
              <div class="fw-semibold">{{ $r->customer_name }}</div>
              <div class="text-muted small">{{ $r->phone }}</div>
            </td>
            <td><a href="{{ route($typeRoute[$r->type], $r->id) }}">{{ $r->invoice_number }}</a></td>
            <td>{{ $typeLabel[$r->type] }}</td>
            <td>{{ \Carbon\Carbon::parse($r->invoice_date)->format('d M Y') }}</td>
            <td class="text-end">{{ $fmt($r->total_price) }}</td>
            <td class="text-end">{{ $fmt($paidAmount) }}</td>
            <td class="text-end fw-semibold">{{ $r->pay_status === 'paid' ? '-' : $fmt($r->remaining_payment) }}</td>
            <td><x-ui.badge :variant="$variant">{{ $label }}</x-ui.badge></td>
            <td>
              @if($r->pay_status !== 'paid')
                <span class="{{ $age > 30 ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $age }} hari</span>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="9" class="py-5"><x-ui.empty-state title="Tidak ada invoice yang cocok dengan filter." /></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $rows->links() }}
</x-ui.card>
@endsection
