@extends('dashboard.index')

@section('title', 'Invoice Mitra')
@section('route-breadcrumb', 'mitra-invoices.index')
@section('breadcrumb', 'Invoice Mitra')

@section('content')

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <x-ui.stats-card label="Total Invoice" value="{{ $totals->total_count ?? 0 }}" color="blue"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M14 2H8a2 2 0 00-2 2v16a2 2 0 002 2h8a2 2 0 002-2V8l-6-6z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2v6h6M9 13h6m-6 4h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' />
    </div>
    <div class="col-md-4">
        <x-ui.stats-card label="Total Nilai" value="Rp{{ number_format($totals->total_amount ?? 0, 0, ',', '.') }}" color="green"
            icon='<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M15 9.5c0-1-1.3-1.8-3-1.8s-3 .8-3 1.8 1.3 1.8 3 1.8 3 .8 3 1.8-1.3 1.8-3 1.8-3-.8-3-1.8M12 6.5v1.2m0 8.6v-1.2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' />
    </div>
    <div class="col-md-4">
        <x-ui.stats-card label="Belum Lunas" value="Rp{{ number_format($totals->total_unpaid ?? 0, 0, ',', '.') }}" color="red"
            icon='<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M12 8v5m0 3h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' />
    </div>
</div>

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="ui-card-title">Daftar Invoice Mitra / Non-Franchise</div>
            <x-ui.button :href="route('mitra-invoices.create')" variant="primary" size="sm">+ Buat Invoice</x-ui.button>
        </div>
    @endslot

    {{-- Filters --}}
    <form method="GET" action="{{ route('mitra-invoices.index') }}" class="row g-2 mb-4 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="No. invoice atau nama mitra...">
        </div>

        <div class="col-md-3">
            <label class="form-label">Mitra</label>
            <select name="mitra_id" class="form-select">
                <option value="">Semua Mitra</option>
                @foreach($mitras as $m)
                    <option value="{{ $m->id }}" @selected(request('mitra_id') == $m->id)>{{ $m->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua</option>
                <option value="unpaid" @selected(request('status') == 'unpaid')>Belum Lunas</option>
                <option value="paid" @selected(request('status') == 'paid')>Lunas</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Dari</label>
            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
        </div>

        <div class="col-md-2">
            <label class="form-label">Sampai</label>
            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
        </div>

        <div class="col-auto d-flex gap-2">
            <x-ui.button type="submit" variant="outline">Filter</x-ui.button>
            @if(request()->anyFilled(['search', 'mitra_id', 'status', 'from', 'to']))
                <x-ui.button :href="route('mitra-invoices.index')" variant="secondary">Reset</x-ui.button>
            @endif
        </div>
    </form>

    <div class="table-responsive">
        <table class="table bordered-table mb-0 align-middle">
            <thead>
                <tr>
                    <th>No. Invoice</th>
                    <th>Mitra</th>
                    <th>Tanggal</th>
                    <th>Jatuh Tempo</th>
                    <th class="text-center">Item</th>
                    <th class="text-end">Total</th>
                    <th class="text-center">Status</th>
                    <th style="width:100px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td class="fw-semibold">{{ $invoice->invoice_number }}</td>
                        <td>
                            {{ $invoice->mitra->name ?? '-' }}
                            <x-ui.badge variant="{{ $invoice->mitra->type === 'mitra' ? 'info' : 'neutral' }}" size="sm">
                                {{ $invoice->mitra->type_label ?? '-' }}
                            </x-ui.badge>
                        </td>
                        <td>{{ $invoice->invoice_date?->format('d M Y') }}</td>
                        <td>{{ $invoice->due_date?->format('d M Y') ?? '-' }}</td>
                        <td class="text-center">{{ $invoice->items_count }}</td>
                        <td class="text-end fw-semibold">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <x-ui.badge variant="{{ $invoice->status === 'paid' ? 'success' : 'warning' }}" size="sm">
                                {{ $invoice->status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                            </x-ui.badge>
                        </td>
                        <td>
                            <a href="{{ route('mitra-invoices.show', $invoice->id) }}"
                               class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-inline-flex align-items-center justify-content-center"
                               title="Detail">
                                <iconify-icon icon="lucide:eye"></iconify-icon>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-5"><x-ui.empty-state title="Belum ada invoice mitra" /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $invoices->withQueryString()->links() }}@endslot
</x-ui.card>

@endsection
