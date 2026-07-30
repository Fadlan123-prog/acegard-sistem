@extends('dashboard.index')

@section('title', 'Employees')
@section('route-breadcrumb', 'employee.index')
@section('breadcrumb', 'Employees')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Daftar Karyawan</div>
                <small class="text-muted">
                    Data karyawan digunakan untuk komisi, absensi, dan laporan gaji.
                </small>
            </div>
            <x-ui.button href="{{ route('employee.create') }}" variant="primary" size="sm">
                + Tambah Karyawan
            </x-ui.button>
        </div>
    @endslot

    <form method="GET" action="{{ route('employee.index') }}" class="row g-2 mb-4 align-items-end">

        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="form-control"
                   placeholder="Cari nama / posisi...">
        </div>

        <div class="col-md-3">
            <label class="form-label">Cabang</label>
            <select name="branch_id" class="form-select">
                <option value="">-- Semua Cabang --</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}"
                        {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-select">
                <option value="">-- Semua --</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <x-ui.button type="submit" variant="outline">Filter</x-ui.button>

            @if(request()->query())
                <x-ui.button href="{{ route('employee.index') }}" variant="secondary">Reset</x-ui.button>
            @endif
        </div>

    </form>

    <div class="table-responsive" style="max-height: 560px; overflow-y: auto; overflow-x: auto;">
        <table class="table bordered-table align-middle mb-0" style="min-width: 1000px; white-space: nowrap;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>No. HP</th>
                    <th>Posisi</th>
                    <th>Cabang</th>
                    <th>Gaji Bulanan</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td>{{ $employees->firstItem() + $loop->index }}</td>

                        <td class="fw-semibold">
                            {{ $employee->name }}
                        </td>

                        <td>
                            @if(!empty($employee->phone_number))
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $employee->phone_number) }}" target="_blank" rel="noopener" class="text-success small">
                                <iconify-icon icon="mdi:whatsapp"></iconify-icon> {{ $employee->phone_number }}
                            </a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            {{ $employee->job_position ?? '-' }}
                        </td>

                        <td>
                            {{ $employee->branch->name ?? '-' }}
                        </td>

                        <td>
                            Rp{{ number_format($employee->monthly_salary ?? 0, 0, ',', '.') }}
                        </td>

                        <td>
                            @if($employee->is_active)
                                <x-ui.badge variant="success">Aktif</x-ui.badge>
                            @else
                                <x-ui.badge variant="danger">Tidak Aktif</x-ui.badge>
                            @endif
                        </td>

                        <td>
                            {{ $employee->created_at ? $employee->created_at->format('d-m-Y') : '-' }}
                        </td>

                        <td>
                            <div class="d-flex gap-1">
                                <form method="POST" action="{{ route('employee.toggle', $employee->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.button type="submit"
                                        :variant="$employee->is_active ? 'warning' : 'success'"
                                        size="sm"
                                        :title="$employee->is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                        :onclick="'return confirm(\'' . ($employee->is_active ? 'Nonaktifkan' : 'Aktifkan') . ' karyawan ini?\'')"
                                    >
                                        <iconify-icon icon="{{ $employee->is_active ? 'lucide:user-x' : 'lucide:user-check' }}"></iconify-icon>
                                    </x-ui.button>
                                </form>
                                @include('partials.table-action-btn', [
                                    'href'  => route('employee.edit', $employee->id),
                                    'icon'  => 'lucide:edit',
                                    'color' => 'success',
                                ])
                                @include('partials.table-action-btn', [
                                    'type'    => 'submit',
                                    'href'    => route('employee.destroy', $employee->id),
                                    'icon'    => 'mingcute:delete-2-line',
                                    'color'   => 'danger',
                                    'confirm' => 'Hapus karyawan ini?',
                                ])
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-5">
                            <x-ui.empty-state title="Tidak ada karyawan ditemukan" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer')
        {{ $employees->withQueryString()->links() }}
    @endslot
</x-ui.card>

@endsection
