@extends('dashboard.index')

@section('title', 'Meter Teknisi Gedung')
@section('route-breadcrumb', 'commission.building-meters')
@section('breadcrumb', 'Meter Teknisi Gedung')

@section('content')
<x-ui.card>
  <form method="GET" class="row g-2 align-items-end mb-3">
    <div class="col-sm-4 col-md-3">
      <label class="form-label small">Dari</label>
      <input type="date" name="from" value="{{ $from }}" class="form-control">
    </div>
    <div class="col-sm-4 col-md-3">
      <label class="form-label small">Sampai</label>
      <input type="date" name="to" value="{{ $to }}" class="form-control">
    </div>
    <div class="col-sm-4 col-md-3">
      <x-ui.button type="submit" variant="primary">Tampilkan</x-ui.button>
    </div>
  </form>

  <p class="text-muted small mb-3">Hanya pekerjaan yang invoice gedungnya sudah lunas pada rentang tanggal ini.</p>

  @if($report->isEmpty())
    <x-ui.empty-state title="Belum ada meter teknisi gedung pada rentang ini." />
  @else
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr><th>Teknisi</th><th>Kategori Film</th><th class="text-end">Meter</th><th class="text-end">Tarif/m</th><th class="text-end">Komisi</th></tr>
      </thead>
      <tbody>
        @foreach($report as $r)
          @foreach($r['lines'] as $line)
            <tr>
              @if($loop->first)<td rowspan="{{ $r['lines']->count() + 1 }}" class="fw-semibold">{{ $r['employee'] }}</td>@endif
              <td>{{ $line['category'] }}</td>
              <td class="text-end">{{ number_format($line['meters'], 2, ',', '.') }}</td>
              <td class="text-end">Rp {{ number_format($line['rate'], 0, ',', '.') }}</td>
              <td class="text-end">Rp {{ number_format($line['amount'], 0, ',', '.') }}</td>
            </tr>
          @endforeach
          <tr class="table-light fw-semibold">
            <td>Total</td>
            <td class="text-end">{{ number_format($r['meters'], 2, ',', '.') }} m</td>
            <td></td>
            <td class="text-end">Rp {{ number_format($r['amount'], 0, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</x-ui.card>
@endsection
