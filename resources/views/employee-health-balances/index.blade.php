@extends('dashboard.index')

@section('title', 'Saldo Biaya Kesehatan')
@section('route-breadcrumb', 'employee-health-balances.index')
@section('breadcrumb', 'Saldo Biaya Kesehatan')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Saldo Biaya Kesehatan — Teknisi &amp; Kenek</div>
                <small class="text-muted">
                    Jatah tahunan untuk klaim biaya berobat. Direset otomatis tiap 1 Januari; alokasi bisa dioverride per karyawan di sini.
                </small>
            </div>
        </div>
    @endslot

    <form method="GET" action="{{ route('employee-health-balances.index') }}" class="row g-2 mb-4 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Tahun</label>
            <select name="year" class="form-select" onchange="this.form.submit()">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>

        @if($branches->count() > 1)
        <div class="col-md-3">
            <label class="form-label">Cabang</label>
            <select name="branch_id" class="form-select" onchange="this.form.submit()">
                <option value="">-- Semua Cabang --</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
    </form>

    @if($employees->isEmpty())
        <x-ui.empty-state title="Tidak ada teknisi/kenek aktif ditemukan." />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Cabang</th>
                        <th class="text-end">Alokasi Tahun {{ $year }}</th>
                        <th class="text-end">Terpakai</th>
                        <th class="text-end">Sisa</th>
                        <th style="width:220px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                        @php $balance = $balances->get($employee->id); @endphp
                        <tr>
                            <td>
                                {{ $employee->name }}
                                <div class="text-muted" style="font-size:12px">{{ $employee->job_position }}</div>
                            </td>
                            <td>{{ $employee->branch->name ?? '-' }}</td>
                            <td class="text-end tab-nums">Rp {{ number_format($balance->allocated_amount, 0, ',', '.') }}</td>
                            <td class="text-end tab-nums">Rp {{ number_format($balance->used_amount, 0, ',', '.') }}</td>
                            <td class="text-end tab-nums fw-semibold">Rp {{ number_format($balance->remaining(), 0, ',', '.') }}</td>
                            <td>
                                <form method="POST" action="{{ route('employee-health-balances.update', $balance) }}" class="d-flex gap-1">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" name="allocated_amount" min="0" step="1"
                                           value="{{ $balance->allocated_amount }}"
                                           class="form-control form-control-sm" style="max-width:150px">
                                    <x-ui.button type="submit" variant="outline" size="sm">Simpan</x-ui.button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-ui.card>

@endsection
