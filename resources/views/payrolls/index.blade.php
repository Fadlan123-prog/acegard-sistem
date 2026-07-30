@extends('dashboard.index')

@section('title', 'Payroll')
@section('route-breadcrumb', 'payrolls.index')
@section('breadcrumb', 'Payroll')

@section('content')

<x-ui.card>

    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Data Payroll</div>
                <small class="text-muted">
                    Daftar payroll bulanan berdasarkan cabang.
                </small>
            </div>
            <x-ui.button href="{{ route('payrolls.create') }}" variant="primary" size="sm">
                + Generate Payroll
            </x-ui.button>
        </div>
    @endslot

    @if(session('success'))
        <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
    @endif

    <form method="GET" action="{{ route('payrolls.index') }}" class="row g-2 mb-4 align-items-end">

        <div class="col-md-3">
            <label class="form-label">Bulan</label>
            <input type="month"
                   name="month"
                   value="{{ request('month') }}"
                   class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">Cabang</label>
            <select name="branch_id" class="form-select">
                <option value="">-- Semua Cabang --</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}"
                        {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }} - {{ $branch->city ?? '-' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">-- Semua --</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <x-ui.button type="submit" variant="outline">
                Filter
            </x-ui.button>

            @if(request()->query())
                <x-ui.button href="{{ route('payrolls.index') }}" variant="outline">
                    Reset
                </x-ui.button>
            @endif
        </div>

    </form>

    <div class="table-responsive" style="max-height: 600px; overflow-y: auto; overflow-x: auto;">
        <table class="table bordered-table align-middle mb-0" style="min-width: 1200px; white-space: nowrap;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Bulan</th>
                    <th>Cabang</th>
                    <th>Hari Kerja</th>
                    <th>Jumlah Karyawan</th>
                    <th>Total Gapok</th>
                    <th>Total Prorate</th>
                    <th>Total Gaji Bulat</th>
                    <th>Status</th>
                    <th style="width: 160px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($payrolls as $payroll)
                    <tr>
                        <td>{{ $payrolls->firstItem() + $loop->index }}</td>

                        <td class="fw-semibold">
                            {{ \Carbon\Carbon::parse($payroll->month . '-01')->format('M Y') }}
                        </td>

                        <td>{{ $payroll->branch->name ?? '-' }}</td>

                        <td>{{ $payroll->working_days }} Hari</td>

                        <td>
                            <x-ui.badge variant="neutral" size="sm">
                                {{ $payroll->items_count }} Karyawan
                            </x-ui.badge>
                        </td>

                        <td>
                            Rp{{ number_format($payroll->total_monthly_salary ?? 0, 0, ',', '.') }}
                        </td>

                        <td>
                            Rp{{ number_format($payroll->total_prorate_salary ?? 0, 0, ',', '.') }}
                        </td>

                        <td class="fw-semibold">
                            Rp{{ number_format($payroll->total_rounded_salary ?? 0, 0, ',', '.') }}
                        </td>

                        <td>
                            @if($payroll->status === 'draft')
                                <x-ui.badge variant="warning" size="sm">Draft</x-ui.badge>
                            @elseif($payroll->status === 'approved')
                                <x-ui.badge variant="primary" size="sm">Approved</x-ui.badge>
                            @elseif($payroll->status === 'paid')
                                <x-ui.badge variant="success" size="sm">Paid</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral" size="sm">{{ ucfirst($payroll->status) }}</x-ui.badge>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex gap-1">

                                <x-ui.button href="{{ route('payrolls.show', $payroll->id) }}" variant="outline" size="sm">
                                    Detail
                                </x-ui.button>

                                @if($payroll->status === 'draft')
                                    <x-ui.button href="{{ route('payrolls.edit', $payroll->id) }}" variant="success" size="sm">
                                        Edit
                                    </x-ui.button>
                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="py-5">
                            <x-ui.empty-state title="Belum ada data payroll." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $payrolls->withQueryString()->links() }}
    </div>

</x-ui.card>

@endsection
