@extends('dashboard.index')

@section('title', 'Laporan Komisi')
@section('breadcrumb', 'Commissions')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Laporan Komisi</h5>
    <a href="{{ route('commission.export',['employee_name'=>request('employee_name')]) }}" class="btn btn-outline-success btn-sm">
    Export {{ request('employee_name') ? 'untuk '.request('employee_name') : 'Semua' }}
  </a>

  </div>

  <div class="card-body">
    {{-- Filter --}}
    <form method="GET" action="{{ route('commission.index') }}" class="row g-2 mb-4 align-items-end">

      <div class="col-md-3">
        <label class="form-label">Karyawan</label>
        <select name="employee_id" class="form-select">
          <option value="">-- Semua --</option>
          @foreach(\App\Models\Employee::orderBy('name')->get() as $emp)
            <option value="{{ $emp->id }}" {{ ($filters['employee_id'] ?? '')==$emp->id ? 'selected' : '' }}>
              {{ $emp->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label">Dari</label>
        <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
      </div>

      <div class="col-md-2">
        <label class="form-label">Sampai</label>
        <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
      </div>

      <div class="col-md-2">
        <label class="form-label">Urutkan</label>
        <select name="sort" class="form-select">
          <option value="date_desc"  {{ ($filters['sort'] ?? '')==='date_desc' ? 'selected':'' }}>Tanggal ↓</option>
          <option value="date_asc"   {{ ($filters['sort'] ?? '')==='date_asc' ? 'selected':'' }}>Tanggal ↑</option>
          <option value="amount_desc"{{ ($filters['sort'] ?? '')==='amount_desc' ? 'selected':'' }}>Nominal ↓</option>
          <option value="amount_asc" {{ ($filters['sort'] ?? '')==='amount_asc' ? 'selected':'' }}>Nominal ↑</option>
        </select>
      </div>

      <div class="col-md-1">
        <label class="form-label">Per Hal.</label>
        <select name="per_page" class="form-select">
          @foreach([10,20,50,100] as $n)
            <option value="{{ $n }}" {{ ($filters['per_page'] ?? 20)==$n ? 'selected':'' }}>{{ $n }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Terapkan</button>
        <a href="{{ route('commission.index') }}" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>

    {{-- Info total --}}
    <div class="mb-3">
      <strong>Total Halaman:</strong> Rp{{ number_format($pageTotal,0,',','.') }} <br>
      <strong>Total Semua (hasil filter):</strong> Rp{{ number_format($totalFiltered,0,',','.') }}
    </div>

    {{-- Table --}}
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Tanggal</th>
            <th>Karyawan</th>
            <th>Customer</th>
            <th>Posisi</th>
            <th class="text-end">Nominal</th>
          </tr>
        </thead>
        <tbody>
          @forelse($commissions as $c)
          <tr>
            <td>{{ \Carbon\Carbon::parse($c->created_at)->format('d-m-Y H:i') }}</td>
            <td>{{ $c->employee_name }}</td>
            <td>{{ $c->customer_name ?? '-' }}</td>
            <td>{{ $c->employee_job ?? '-' }}</td>
            <td class="text-end">Rp{{ number_format($c->amount,0,',','.') }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center">Tidak ada data.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <small>
            Menampilkan {{ $commissions->firstItem() }} – {{ $commissions->lastItem() }}
            dari total {{ $commissions->total() }} data
            </small>
        </div>
        <div>
            {{ $commissions->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
        </div>
        </div>
    </div>
@endsection
