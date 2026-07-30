@extends('dashboard.index')

@section('title', 'Customers Building')
@section('route-breadcrumb', 'customer.building.index')
@section('breadcrumb', 'Customer Buildings')

@section('content')
<x-ui.card>
    @slot('header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">Daftar Pelanggan</h5>
        <div class="d-flex gap-2">
            <x-ui.button href="#" variant="outline" size="sm">Export Excel</x-ui.button>
            <x-ui.button href="{{ route('customer.building.create') }}" variant="primary" size="sm">+ Add Customer</x-ui.button>
        </div>
    </div>
    @endslot

    {{-- Search & Bulk Delete --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-2">
        {{-- Search + Filter --}}
        <form id="filterForm" method="GET" action="{{ route('customer.building.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}"
                class="form-control" placeholder="Cari nama customer..." style="max-width: 250px;">
            <x-ui.button type="submit" variant="outline">Search</x-ui.button>

            @if(auth()->user()->hasRole('superadmin'))
            <div class="col-md-3">
                <select name="branch_id" class="form-select"
                        onchange="document.getElementById('filterForm').submit()">
                    <option value="">-- Semua Cabang --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(request('branch_id') || request('search'))
                <x-ui.button href="{{ route('customer.building.index') }}" variant="outline">Reset</x-ui.button>
            @endif
        </form>

        {{-- Bulk Delete --}}
        <form id="bulkDeleteForm" action="{{ route('customer.building.bulkDelete') }}" method="POST" class="d-none"
            onsubmit="return confirm('Yakin hapus data terpilih?')">
            @csrf
            @method('DELETE')
            <input type="hidden" name="selected_ids" id="selected_ids">
            <x-ui.button type="submit" variant="danger" size="sm">Hapus Terpilih</x-ui.button>
        </form>
    </div>

    <table class="table bordered-table mb-0">
        <thead>
            <tr>
                <th><input type="checkbox" id="checkAll" class="form-check-input"></th>
                <th>WSN</th>
                <th>Nomor Kartu</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customerBuildings as $customer)
            <tr>
                <td><input type="checkbox" class="check-item form-check-input" value="{{ $customer->id }}"></td>
                <td>{{ $customer->wsn }}</td>
                <td>{{ $customer->card_number }}</td>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->email }}</td>
                <td>
                    <div class="d-flex gap-1">
                        {{-- View --}}
                        <a href="javascript:void(0)"
                            class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                            data-bs-toggle="modal"
                            data-bs-target="#viewCustomerModal{{ $customer->id }}">
                            <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                        </a>

                        {{-- Edit --}}
                        <a href="{{ route('customer.building.edit', $customer->id) }}"
                            class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                            <iconify-icon icon="lucide:edit"></iconify-icon>
                        </a>

                        {{-- Delete --}}
                        <form action="{{ route('customer.building.destroy', $customer->id) }}" method="POST"
                            onsubmit="return confirm('Hapus data ini?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0">
                                <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                            </button>
                        </form>

                         @if ($customer->invoices->isNotEmpty())
                            {{-- Lihat Invoice --}}
                            <a href="{{ route('invoice.building.show', $customer->invoices->last()->id) }}"
                            class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center ms-1"
                            title="Lihat Invoice">
                                <iconify-icon icon="mdi:paper-outline"></iconify-icon>
                            </a>
                        @else
                            {{-- Buat Invoice --}}
                            <a href="{{ route('invoice.building.create', $customer->id) }}"
                            class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center ms-1"
                            title="Buat Invoice">
                                <iconify-icon icon="ic:baseline-add-circle-outline"></iconify-icon>
                            </a>
                        @endif

                    </div>
                </td>

            </tr>
            @empty
            <tr><td colspan="6" class="py-5"><x-ui.empty-state title="Data tidak ditemukan." /></td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">{{ $customerBuildings->withQueryString()->links() }}</div>
</x-ui.card>

@foreach ($customerBuildings as $customer)
<div class="modal fade" id="viewCustomerModal{{ $customer->id }}" tabindex="-1" aria-labelledby="viewCustomerModalLabel{{ $customer->id }}" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <dl class="row">
          <dt class="col-sm-4">Nama</dt>
          <dd class="col-sm-8">{{ $customer->name }}</dd>

          <dt class="col-sm-4">Email</dt>
          <dd class="col-sm-8">{{ $customer->email }}</dd>

          <dt class="col-sm-4">WSN</dt>
          <dd class="col-sm-8">{{ $customer->wsn }}</dd>

          <dt class="col-sm-4">Garansi</dt>
          <dd class="col-sm-8">{{ $customer->warantee_duration }} tahun (sampai {{ $customer->warantee_end }})</dd>

        </dl>
      </div>
    </div>
  </div>
</div>
@endforeach

{{-- Script --}}
@include('partials.bulk-delete-script')
@endsection
