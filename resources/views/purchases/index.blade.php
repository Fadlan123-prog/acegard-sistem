@extends('dashboard.index')

@section('title', 'Purchasing')
@section('route-breadcrumb', 'purchases.index')
@section('breadcrumb', 'Purchasing')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Daftar Purchasing</div>
            <div class="d-flex gap-2">
                <x-ui.button href="{{ route('purchase.reports.monthly') }}" variant="outline" size="sm">Report Bulanan</x-ui.button>
                <x-ui.button href="{{ route('purchases.create') }}" variant="primary" size="sm">+ Add Purchasing</x-ui.button>
            </div>
        </div>
    @endslot

    {{-- Alert --}}
    @if(session('success'))
        <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
    @endif


    {{-- Filter --}}
    <form method="GET"
          action="{{ route('purchases.index') }}"
          id="filterForm"
          class="row g-2 mb-4 align-items-end">

        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="form-control"
                   placeholder="No purchasing / supplier...">
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

        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">-- Semua Status --</option>
                <option value="draft"      {{ request('status') === 'draft'      ? 'selected' : '' }}>Draft</option>
                <option value="confirmed"  {{ request('status') === 'confirmed'  ? 'selected' : '' }}>Confirmed</option>
                <option value="cancelled"  {{ request('status') === 'cancelled'  ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        @if(auth()->user()->hasRole('superadmin'))
            <div class="col-md-2">
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
        @endif

        <div class="col-md-2">
            <label class="form-label">Supplier</label>
            <select name="supplier_id" class="form-select">
                <option value="">-- Semua Supplier --</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}"
                        {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 d-flex gap-2">
            <x-ui.button type="submit" variant="outline" size="sm">Filter</x-ui.button>

            @if(request('search') || request('from') || request('to') || request('branch_id') || request('status') || request('supplier_id'))
                <x-ui.button href="{{ route('purchases.index') }}" variant="secondary" size="sm">Reset</x-ui.button>
            @endif
        </div>
    </form>


    {{-- Summary (filtered totals — not page-only) --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-2">
            <x-ui.stats-card label="Total Purchasing (Filter)" :value="number_format($totals->total_count ?? 0)" color="blue">
                <span>PO</span>
            </x-ui.stats-card>
        </div>

        <div class="col-md-4 mb-2">
            <x-ui.stats-card label="Total Item (Filter)" :value="number_format($totals->total_items ?? 0)" color="amber">
                <span>Item</span>
            </x-ui.stats-card>
        </div>

        <div class="col-md-4 mb-2">
            <x-ui.stats-card label="Total Nilai (Filter)" :value="'Rp' . number_format($totals->total_amount ?? 0, 0, ',', '.')" color="green" />
        </div>
    </div>


    {{-- Table --}}
    <div class="table-responsive" style="max-height: 520px; overflow-y: auto; overflow-x: auto;">
        <table class="table bordered-table mb-0 align-middle" style="min-width: 1100px; white-space: nowrap;">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>No Purchasing</th>
                    <th>Tanggal</th>
                    <th>Cabang</th>
                    <th>Supplier</th>
                    <th>Jumlah Item</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Input By</th>
                    <th style="width: 130px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td>
                            {{ $purchases->firstItem() + $loop->index }}
                        </td>

                        <td>
                            <span class="fw-semibold">
                                {{ $purchase->purchase_number }}
                            </span>
                        </td>

                        <td>
                            {{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') : '-' }}
                        </td>

                        <td>
                            {{ $purchase->branch->name ?? '-' }}
                        </td>

                        <td>
                            {{ $purchase->supplier->name ?? '-' }}
                        </td>

                        <td>
                            <x-ui.badge variant="neutral" size="sm">{{ $purchase->items_count }} Item</x-ui.badge>
                        </td>

                        <td>
                            Rp{{ number_format($purchase->total_amount ?? 0, 0, ',', '.') }}
                        </td>

                        <td>
                            @if($purchase->status === 'confirmed')
                                <x-ui.badge variant="success" size="sm">Confirmed</x-ui.badge>
                            @elseif($purchase->status === 'draft')
                                <x-ui.badge variant="warning" size="sm">Draft</x-ui.badge>
                            @elseif($purchase->status === 'cancelled')
                                <x-ui.badge variant="danger" size="sm">Cancelled</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral" size="sm">{{ $purchase->status }}</x-ui.badge>
                            @endif
                        </td>

                        <td>
                            {{ $purchase->user->name ?? '-' }}
                        </td>

                        <td>
                            <div class="d-flex gap-1">

                                {{-- View --}}
                                <a href="{{ route('purchases.show', $purchase->id) }}"
                                   class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                                   title="View">
                                    <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                </a>

                                {{-- Edit --}}
                                <a href="{{ route('purchases.edit', $purchase->id) }}"
                                   class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                   title="Edit">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>

                                {{-- Delete --}}
                                <form action="{{ route('purchases.destroy', $purchase->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus data purchasing ini?')"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                                            title="Delete">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="py-5"><x-ui.empty-state title="Data purchasing tidak ditemukan." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $purchases->withQueryString()->links() }}@endslot
</x-ui.card>

@endsection
