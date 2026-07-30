@extends('layout.index')

@section('title','Gallery')

@section('breadcrumb', 'Gallery')

@section('route-breadcrumb', 'home')

@section('content')
<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Daftar Gambar</div>
            <div class="d-flex gap-2">
                <x-ui.button href="{{ route('customer.export') }}" variant="outline" size="sm">
                    Export Excel
                </x-ui.button>
                <x-ui.button href="{{ route('customer.create') }}" variant="primary" size="sm">+ Add Gambar</x-ui.button>
            </div>
        </div>
    @endslot

    {{-- Search & Bulk Delete --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-2">

        {{-- Search + Filter Cabang --}}
        <form method="GET" action="{{ route('customer.index') }}" id="filterForm"
            class="d-flex align-items-center gap-2 flex-wrap">

            {{-- Input pencarian --}}
            <input type="text" name="search" value="{{ request('search') }}"
                class="form-control" placeholder="Cari nama customer..." style="max-width: 250px;">

            {{-- Tombol cari --}}
            <x-ui.button type="submit" variant="outline">Search</x-ui.button>

            {{-- Dropdown filter cabang --}}
            <div class="col-md-3">
                <select name="branch_id" class="form-select"
                        onchange="document.getElementById('filterForm').submit()">
                    <option value="">-- Semua Cabang --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                                {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol reset jika filter aktif --}}
            @if(request('branch_id') || request('search'))
                <x-ui.button href="{{ route('customer.index') }}" variant="outline">Reset</x-ui.button>
            @endif
        </form>

        {{-- Bulk Delete --}}
        <form id="bulkDeleteForm" action="{{ route('customer.bulkDelete') }}" method="POST" class="d-none"
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
                <th>Garansi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($gallery as $gallery)
            <tr>
                <td><input type="checkbox" class="check-item form-check-input" value="{{ $customer->id }}"></td>
                <td>{{ $gallery->wsn }}</td>
                <td>{{ $gallery->card_number }}</td>
                <td>{{ $gallery->name }}</td>
                <td>{{ $gallery->email }}</td>
                <td>
                    @if ($gallery->warantee_end >= now()->format('Y-m-d'))
                    <x-ui.badge variant="success">Aktif</x-ui.badge>
                    @else
                    <x-ui.badge variant="danger">Kadaluarsa</x-ui.badge>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-1">
                        {{-- View --}}
                        <a href="javascript:void(0)"
                            class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                            data-bs-toggle="modal"
                            data-bs-target="#viewCustomerModal{{ $gallery->id }}">
                            <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                        </a>

                        {{-- Edit --}}
                        <a href="{{ route('customer.edit', $gallery->id) }}"
                            class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                            <iconify-icon icon="lucide:edit"></iconify-icon>
                        </a>

                        {{-- Delete --}}
                        <form action="{{ route('customer.destroy', $gallery->id) }}" method="POST"
                            onsubmit="return confirm('Hapus data ini?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0">
                                <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                            </button>
                        </form>

                         @if ($gallery->invoice)
                            {{-- Lihat Invoice --}}
                            <a href="{{ route('invoice.show', $gallery->invoice->id) }}"
                                class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center ms-1"
                                title="Lihat Invoice">
                                <iconify-icon icon="mdi:paper-outline"></iconify-icon>
                            </a>
                        @else
                            {{-- Buat Invoice --}}
                            <a href="{{ route('invoice.create', $gallery->id) }}"
                                class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center ms-1"
                                title="Buat Invoice">
                                <iconify-icon icon="ic:baseline-add-circle-outline"></iconify-icon>
                            </a>
                        @endif
                    </div>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="6" class="py-4">
                    <x-ui.empty-state title="Data tidak ditemukan." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">{{ $gallery->withQueryString()->links() }}</div>
</x-ui.card>

@foreach ($gallery as $gallery)
<div class="modal fade" id="viewCustomerModal{{ $gallery->id }}" tabindex="-1" aria-labelledby="viewCustomerModalLabel{{ $gallery->id }}" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <dl class="row">
          <dt class="col-sm-4">Nama</dt>
          <dd class="col-sm-8">{{ $gallery->name }}</dd>

          <dt class="col-sm-4">Email</dt>
          <dd class="col-sm-8">{{ $gallery->email }}</dd>

          <dt class="col-sm-4">WSN</dt>
          <dd class="col-sm-8">{{ $gallery->wsn }}</dd>

          <dt class="col-sm-4">No Kartu</dt>
          <dd class="col-sm-8">{{ $gallery->card_number }}</dd>

          <dt class="col-sm-4">Plat Nomor</dt>
          <dd class="col-sm-8">{{ $gallery->plat_number }}</dd>

          <dt class="col-sm-4">Merek Kendaraan</dt>
          <dd class="col-sm-8">{{ $gallery->vehicle_brand }}</dd>

          <dt class="col-sm-4">Model Kendaraan</dt>
          <dd class="col-sm-8">{{ $gallery->vehicle_model }}</dd>

          <dt class="col-sm-4">Garansi</dt>
          <dd class="col-sm-8">{{ $gallery->warantee_duration }} tahun (sampai {{ $gallery->warantee_end }})</dd>

        </dl>
      </div>
    </div>
  </div>
</div>
@endforeach

{{-- Script --}}
@include('partials.bulk-delete-script')

@endsection
