@extends('dashboard.index')

@section('title', 'History Transfer Roll')
@section('route-breadcrumb', 'film-stock-transfers.index')
@section('breadcrumb', 'History Transfer Roll')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">History Transfer Roll</div>
                <small class="text-muted">Riwayat pengiriman roll dari gudang/cabang ke cabang lain.</small>
            </div>

            <x-ui.button href="{{ route('film-stock-transfers.create') }}" variant="primary" size="sm">
                + Transfer Roll
            </x-ui.button>
        </div>
    @endslot

    @if(session('success'))
        <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
    @endif

    <form method="GET" action="{{ route('film-stock-transfers.index') }}" class="row g-2 mb-4 align-items-end">
        <div class="col-md-3">
            <x-ui.form-field label="Search" name="search">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control" placeholder="No transfer / cabang...">
            </x-ui.form-field>
        </div>

        <div class="col-md-2">
            <x-ui.form-field label="Dari" name="from">
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </x-ui.form-field>
        </div>

        <div class="col-md-2">
            <x-ui.form-field label="Sampai" name="to">
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </x-ui.form-field>
        </div>

        <div class="col-md-2">
            <x-ui.form-field label="Status" name="status">
                <select name="status" class="form-select">
                    <option value="">-- Semua --</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </x-ui.form-field>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <x-ui.button type="submit" variant="outline">Filter</x-ui.button>

            @if(request()->query())
                <x-ui.button href="{{ route('film-stock-transfers.index') }}" variant="ghost">Reset</x-ui.button>
            @endif
        </div>
    </form>

    <div class="table-responsive">
        <table class="table bordered-table align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Transfer</th>
                    <th>Tanggal</th>
                    <th>Dari</th>
                    <th>Tujuan</th>
                    <th>Jumlah Roll</th>
                    <th>Status</th>
                    <th>Dibuat Oleh</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($transfers as $transfer)
                    <tr>
                        <td>{{ $transfers->firstItem() + $loop->index }}</td>
                        <td class="fw-semibold">{{ $transfer->transfer_number }}</td>
                        <td>{{ $transfer->transfer_date ? $transfer->transfer_date->format('d-m-Y') : '-' }}</td>
                        <td>{{ $transfer->fromBranch->name ?? '-' }}</td>
                        <td>{{ $transfer->toBranch->name ?? '-' }}</td>
                        <td>
                            <x-ui.badge variant="primary">
                                {{ $transfer->items->count() }} Roll
                            </x-ui.badge>
                        </td>
                        <td>
                            @if($transfer->status === 'sent')
                                <x-ui.badge variant="warning">Sent</x-ui.badge>
                            @elseif($transfer->status === 'received')
                                <x-ui.badge variant="success">Received</x-ui.badge>
                            @elseif($transfer->status === 'cancelled')
                                <x-ui.badge variant="danger">Cancelled</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral">{{ ucfirst($transfer->status) }}</x-ui.badge>
                            @endif
                        </td>
                        <td>{{ $transfer->creator->name ?? '-' }}</td>
                        <td>
                            <x-ui.button href="{{ route('film-stock-transfers.show', $transfer->id) }}" variant="outline" size="sm">
                                Detail
                            </x-ui.button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-5">
                            <x-ui.empty-state title="Belum ada history transfer." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer')
        {{ $transfers->withQueryString()->links() }}
    @endslot

</x-ui.card>

@endsection
