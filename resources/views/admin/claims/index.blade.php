@extends('dashboard.index')

@section('title', 'Warranty Claims')
@section('route-breadcrumb', 'admin.claims.index')
@section('breadcrumb', 'Warranty Claims')

@section('content')
<div class="card basic-data-table">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="card-title mb-0">Daftar Warranty Claim</h5>
  </div>

  <div class="card-body">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <form method="GET" action="{{ route('admin.claims.index') }}" class="d-flex flex-wrap gap-2 mb-3" id="filterForm">
      <input type="text" name="search" value="{{ request('search') }}" class="form-control"
             placeholder="Cari claim/card/kode..." style="max-width:260px;">

      <select name="status" class="form-select" style="max-width:200px;" onchange="filterForm.submit()">
        <option value="">-- Semua Status --</option>
        @foreach(['submitted','inspected','approved','rejected','in_progress','completed','closed'] as $st)
          <option value="{{ $st }}" {{ request('status')===$st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
        @endforeach
      </select>

      <select name="type" class="form-select" style="max-width:220px;" onchange="filterForm.submit()">
        <option value="">-- Semua Tipe --</option>
        <option value="car_film" {{ request('type')==='car_film'?'selected':'' }}>Kaca Film Mobil</option>
        <option value="building_film" {{ request('type')==='building_film'?'selected':'' }}>Kaca Film Gedung</option>
        <option value="ppf" {{ request('type')==='ppf'?'selected':'' }}>PPF</option>
      </select>

      @if(auth()->user()->hasRole('superadmin'))
      <select name="branch_id" class="form-select" style="max-width:220px;" onchange="filterForm.submit()">
        <option value="">-- Semua Cabang --</option>
        @foreach($branches as $b)
          <option value="{{ $b->id }}" {{ request('branch_id')==$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
        @endforeach
      </select>
      @endif

      <button class="btn btn-outline-primary">Search</button>

      @if(request('branch_id') || request('search') || request('status') || request('type'))
        <a href="{{ route('admin.claims.index') }}" class="btn btn-outline-secondary">Reset</a>
      @endif
    </form>

    <table class="table bordered-table mb-0">
      <thead>
        <tr>
          <th>No Claim</th>
          <th>Tipe</th>
          <th>Card Number</th>
          <th>Status</th>
          <th>Charge</th>
          <th>Invoice</th>
          <th>Tgl Claim</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse($claims as $c)
        <tr>
          <td>{{ $c->claim_number }}</td>
          <td>
            <span class="badge bg-light text-dark">
              {{ $c->warranty_type === 'car_film' ? 'MOBIL' : ($c->warranty_type === 'building_film' ? 'BUILDING' : 'PPF') }}
            </span>
          </td>
          <td>{{ $c->card_number }}</td>
          <td>
            @php
              $map = [
                'submitted'=>'secondary','inspected'=>'warning','approved'=>'primary',
                'rejected'=>'danger','in_progress'=>'info','completed'=>'success','closed'=>'dark'
              ];
              $cls = $map[$c->status] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $cls }}">{{ strtoupper($c->status) }}</span>
          </td>
          <td>
            @if($c->is_chargeable)
              <span class="badge bg-danger">Chargeable</span>
              <div class="small text-muted">Rp{{ number_format($c->quote_total,0,',','.') }}</div>
            @else
              <span class="badge bg-success">Free</span>
            @endif
          </td>
          <td>
            @if($c->invoice)
              <a href="{{ route('admin.claim-invoices.show', $c->invoice->id) }}" class="badge bg-success">Lihat</a>
            @else
              <span class="text-muted">-</span>
            @endif
          </td>
          <td>{{ $c->claim_date }}</td>
          <td>
            <a href="{{ route('admin.claims.show', $c->id) }}"
               class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
               title="Detail">
              <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
            </a>
          </td>
        </tr>
      @empty
        <tr><td colspan="8" class="text-center">Belum ada claim.</td></tr>
      @endforelse
      </tbody>
    </table>

    <div class="mt-3">{{ $claims->withQueryString()->links() }}</div>
  </div>
</div>
@endsection
