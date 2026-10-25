@extends('dashboard.index')

@section('title', 'Complain / Replacement')
@section('route-breadcrumb', 'film-complaints.index')
@section('breadcrumb', 'Complain / Replacement')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Data Complain / Replacement</div>
                <small class="text-muted">
                    Daftar complain pergantian kaca film yang mengurangi stok roll.
                </small>
            </div>

            <x-ui.button :href="route('film-complaints.create')" variant="primary" size="sm">
                + Tambah Complain
            </x-ui.button>
        </div>
    @endslot

    

    

    <form method="GET" action="{{ route('film-complaints.index') }}" class="row g-2 mb-4 align-items-end">

        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="form-control"
                   placeholder="No complain / customer / plat...">
        </div>

        <div class="col-md-2">
            <label class="form-label">Jenis</label>
            <select name="complaint_type" class="form-select">
                <option value="">-- Semua --</option>
                <option value="car" {{ request('complaint_type') == 'car' ? 'selected' : '' }}>
                    Mobil
                </option>
                <option value="building" {{ request('complaint_type') == 'building' ? 'selected' : '' }}>
                    Gedung
                </option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">-- Semua --</option>
                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                <option value="process" {{ request('status') == 'process' ? 'selected' : '' }}>Process</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Dari Tanggal</label>
            <input type="date"
                   name="from"
                   value="{{ request('from') }}"
                   class="form-control">
        </div>

        <div class="col-md-2">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date"
                   name="to"
                   value="{{ request('to') }}"
                   class="form-control">
        </div>

        @if(auth()->user()->hasRole('superadmin'))
            <div class="col-md-3">
                <label class="form-label">Cabang</label>
                <select name="branch_id" class="form-select">
                    <option value="">-- Semua Cabang --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-3 d-flex gap-2">
            <x-ui.button type="submit" variant="outline">
                Filter
            </x-ui.button>

            @if(request()->query())
                <x-ui.button :href="route('film-complaints.index')" variant="ghost">
                    Reset
                </x-ui.button>
            @endif
        </div>

    </form>

    <div class="table-responsive" style="max-height: 560px; overflow-y: auto; overflow-x: auto;">
        <table class="table bordered-table align-middle mb-0" style="min-width: 1100px; white-space: nowrap;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Complain</th>
                    <th>Tanggal</th>
                    <th>Cabang</th>
                    <th>Jenis</th>
                    <th>Customer</th>
                    <th>Alasan</th>
                    <th>Jumlah Item</th>
                    <th>Status</th>
                    <th>Dibuat Oleh</th>
                    <th style="width: 100px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($complaints as $complaint)
                    <tr>
                        <td>{{ $complaints->firstItem() + $loop->index }}</td>

                        <td class="fw-semibold">
                            {{ $complaint->complaint_number }}
                        </td>

                        <td>
                            {{ $complaint->complaint_date ? $complaint->complaint_date->format('d-m-Y') : '-' }}
                        </td>

                        <td>{{ $complaint->branch->name ?? '-' }}</td>

                        <td>
                            @if($complaint->complaint_type === 'car')
                                <x-ui.badge variant="primary">Mobil</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral">Gedung</x-ui.badge>
                            @endif
                        </td>

                        <td>
                            @if($complaint->complaint_type === 'car')
                                {{ $complaint->customer->name ?? '-' }}
                                <br>
                                <small class="text-muted">
                                    {{ $complaint->customer->vehicle_brand ?? '' }}
                                    {{ $complaint->customer->vehicle_model ?? '' }}
                                    -
                                    {{ $complaint->customer->plat_number ?? '' }}
                                </small>
                            @else
                                {{ $complaint->customerBuilding->name ?? '-' }}
                                <br>
                                <small class="text-muted">
                                    {{ $complaint->customerBuilding->address ?? '' }}
                                </small>
                            @endif
                        </td>

                        <td>{{ $complaint->reason ?? '-' }}</td>

                        <td>
                            <x-ui.badge variant="neutral">
                                {{ $complaint->items->count() }} Item
                            </x-ui.badge>
                        </td>

                        <td>
                            @if($complaint->status === 'completed')
                                <x-ui.badge variant="success">Completed</x-ui.badge>
                            @elseif($complaint->status === 'open')
                                <x-ui.badge variant="primary">Open</x-ui.badge>
                            @elseif($complaint->status === 'process')
                                <x-ui.badge variant="warning">Process</x-ui.badge>
                            @elseif($complaint->status === 'cancelled')
                                <x-ui.badge variant="danger">Cancelled</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral">{{ ucfirst($complaint->status) }}</x-ui.badge>
                            @endif
                        </td>

                        <td>{{ $complaint->creator->name ?? '-' }}</td>

                        <td>
                            <x-ui.button :href="route('film-complaints.show', $complaint->id)" variant="outline" size="sm">
                                Detail
                            </x-ui.button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="py-5"><x-ui.empty-state title="Belum ada data complain." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $complaints->withQueryString()->links() }}@endslot

</x-ui.card>

@endsection
