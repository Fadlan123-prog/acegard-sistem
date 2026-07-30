@extends('dashboard.index')

@section('title', 'Detail Transfer Roll')
@section('route-breadcrumb', 'film-stock-transfers.index')
@section('breadcrumb', 'Detail Transfer Roll')

@section('content')

@if(session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif

@if(session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Detail Transfer Roll</div>
                <small class="text-muted">
                    {{ $transfer->transfer_number }}
                </small>
            </div>

            <div class="d-flex gap-2">
                @if($transfer->status === 'sent')
                    <form method="POST" action="{{ route('film-stock-transfers.receive', $transfer->id) }}"
                          onsubmit="return confirm('Konfirmasi transfer ini sudah diterima?')">
                        @csrf
                        <x-ui.button type="submit" variant="success" size="sm">
                            Konfirmasi Diterima
                        </x-ui.button>
                    </form>
                @endif

                <x-ui.button href="{{ route('film-stock-transfers.index') }}" variant="secondary" size="sm">
                    Kembali
                </x-ui.button>
            </div>
        </div>
    @endslot

    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">No Transfer</small>
                <h6 class="mb-0">{{ $transfer->transfer_number }}</h6>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">Tanggal</small>
                <h6 class="mb-0">
                    {{ $transfer->transfer_date ? $transfer->transfer_date->format('d-m-Y') : '-' }}
                </h6>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">Jumlah Roll</small>
                <h6 class="mb-0">{{ $transfer->items->count() }} Roll</h6>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">Status</small>
                <h6 class="mb-0">
                    @if($transfer->status === 'sent')
                        <x-ui.badge variant="warning">Sent</x-ui.badge>
                    @elseif($transfer->status === 'received')
                        <x-ui.badge variant="success">Received</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral">{{ ucfirst($transfer->status) }}</x-ui.badge>
                    @endif
                </h6>
            </div>
        </div>
    </div>

    <dl class="row mb-4">
        <dt class="col-sm-3">Dari</dt>
        <dd class="col-sm-9">{{ $transfer->fromBranch->name ?? '-' }}</dd>

        <dt class="col-sm-3">Tujuan</dt>
        <dd class="col-sm-9">{{ $transfer->toBranch->name ?? '-' }}</dd>

        <dt class="col-sm-3">Dibuat Oleh</dt>
        <dd class="col-sm-9">{{ $transfer->creator->name ?? '-' }}</dd>

        <dt class="col-sm-3">Diterima Oleh</dt>
        <dd class="col-sm-9">{{ $transfer->receiver->name ?? '-' }}</dd>

        <dt class="col-sm-3">Catatan</dt>
        <dd class="col-sm-9">{{ $transfer->notes ?? '-' }}</dd>
    </dl>

    <h6 class="fw-bold mb-3">Daftar Roll Ditransfer</h6>

    <div class="table-responsive border rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode Roll</th>
                    <th>Type</th>
                    <th>Kode / Kegelapan</th>
                    <th>Lebar</th>
                    <th>Panjang</th>
                    <th>Area</th>
                </tr>
            </thead>

            <tbody>
                @forelse($transfer->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $item->roll_code }}</td>
                        <td>{{ $item->film_type ?? '-' }}</td>
                        <td>
                            <x-ui.badge variant="neutral">
                                {{ $item->darkness_code ?? '-' }}
                            </x-ui.badge>
                        </td>
                        <td>{{ number_format($item->width_meter, 2, ',', '.') }} m</td>
                        <td>{{ number_format($item->length_meter, 2, ',', '.') }} m</td>
                        <td>{{ number_format($item->area_meter, 2, ',', '.') }} m²</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-5"><x-ui.empty-state title="Tidak ada item roll." /></td></tr>
                @endforelse
            </tbody>

            @if($transfer->items->count())
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Total</th>
                        <th>{{ number_format($transfer->items->sum('length_meter'), 2, ',', '.') }} m</th>
                        <th>{{ number_format($transfer->items->sum('area_meter'), 2, ',', '.') }} m²</th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

</x-ui.card>

@endsection
