@extends('layout.index')

@section('title', 'Spend Iklan')
@section('route-breadcrumb', route('advertising.spend.index'))
@section('breadcrumb', 'Spend Iklan')

@section('content')

@if (session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
    <div class="d-flex justify-content-between align-items-center">
        <div class="ui-card-title">Catatan Spend Iklan</div>
        <x-ui.button :href="route('advertising.spend.create')" variant="primary" size="sm">
            <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Tambah Spend
        </x-ui.button>
    </div>
    @endslot

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:12px">
            <thead>
                <tr>
                    <th class="ps-0 py-2 text-muted fw-medium border-bottom">Tanggal</th>
                    <th class="py-2 text-muted fw-medium border-bottom">Channel</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Jumlah</th>
                    <th class="py-2 text-muted fw-medium border-bottom">Catatan</th>
                    <th class="py-2 text-muted fw-medium border-bottom">Dicatat oleh</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entries as $entry)
                <tr>
                    <td class="ps-0 py-2">{{ $entry->spend_date->format('d/m/Y') }}</td>
                    <td class="py-2"><x-ui.badge variant="neutral" size="sm">{{ $entry->channel_label }}</x-ui.badge></td>
                    <td class="py-2 text-end fw-semibold">Rp {{ number_format($entry->amount, 0, ',', '.') }}</td>
                    <td class="py-2 text-muted" style="font-size:11px">{{ $entry->notes ?? '—' }}</td>
                    <td class="py-2 text-muted" style="font-size:11px">{{ $entry->creator->name ?? '—' }}</td>
                    <td class="py-2 text-end">
                        <form action="{{ route('advertising.spend.destroy', $entry) }}" method="POST" onsubmit="return confirm('Hapus entri spend ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ui-btn ui-btn-ghost ui-btn-sm" title="Hapus">
                                <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-4 text-center">
                        <x-ui.empty-state title="Belum ada spend tercatat bulan ini" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $entries->links() }}
    </div>
</x-ui.card>

@endsection
