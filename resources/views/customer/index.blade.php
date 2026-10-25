@extends('dashboard.index')

@section('title', 'Customers')
@section('route-breadcrumb', 'customer.index')
@section('breadcrumb', 'Customers')

@section('content')

<x-ui.card>
    @slot('header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="ui-card-title">Daftar Pelanggan</div>
        <div class="d-flex gap-2">
            <x-ui.button :href="route('customer.export')" variant="outline" size="sm">Export Excel</x-ui.button>
            <x-ui.button :href="route('customer.create')" variant="primary" size="sm">+ Add Customer</x-ui.button>
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
            @if(auth()->user()->hasRole('superadmin'))
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
            @endif

            {{-- Tombol reset jika filter aktif --}}
            @if(request('branch_id') || request('search'))
                <x-ui.button :href="route('customer.index')" variant="outline">Reset</x-ui.button>
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

    <div class="table-responsive">
    <table class="table bordered-table mb-0">
        <thead>
            <tr>
                <th style="width:36px"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                <th>WSN</th>
                <th>Nama</th>
                <th>Kendaraan</th>
                <th>Plat</th>
                @if(auth()->user()->hasRole('superadmin'))
                <th>Cabang</th>
                @endif
                <th>Tgl Pasang</th>
                <th>Garansi</th>
                <th style="width:160px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customers as $customer)
            <tr>
                <td><input type="checkbox" class="check-item form-check-input" value="{{ $customer->id }}"></td>
                <td>
                    <div class="fw-semibold small">{{ $customer->wsn }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $customer->card_number }}</div>
                </td>
                <td>
                    <div class="fw-semibold">{{ $customer->name }}</div>
                    <div class="text-muted small">{{ $customer->email }}</div>
                </td>
                <td>
                    <div class="small">{{ $customer->vehicle_brand }}</div>
                    <div class="text-muted small">{{ $customer->vehicle_model }}</div>
                </td>
                <td>
                    <x-ui.badge variant="neutral">{{ $customer->plat_number ?? '-' }}</x-ui.badge>
                </td>
                @if(auth()->user()->hasRole('superadmin'))
                <td class="small text-muted">{{ $customer->branch?->name ?? '-' }}</td>
                @endif
                <td class="small">
                    @if($customer->install_date)
                        {{ \Carbon\Carbon::parse($customer->install_date)->format('d M Y') }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if ($customer->warantee_end >= now()->format('Y-m-d'))
                    <x-ui.badge variant="success">Aktif</x-ui.badge>
                    <div class="text-muted" style="font-size:11px">s/d {{ \Carbon\Carbon::parse($customer->warantee_end)->format('d M Y') }}</div>
                    @else
                    <x-ui.badge variant="danger">Kadaluarsa</x-ui.badge>
                    <div class="text-muted" style="font-size:11px">{{ \Carbon\Carbon::parse($customer->warantee_end)->format('d M Y') }}</div>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-1 flex-wrap">
                        {{-- Warranty Card --}}
                        <a href="javascript:void(0)"
                            class="w-32-px h-32-px bg-warning-focus text-warning-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                            data-bs-toggle="modal"
                            data-bs-target="#warrantyCardModal{{ $customer->id }}"
                            title="Download Kartu Garansi">
                            <iconify-icon icon="ion:card-outline"></iconify-icon>
                        </a>
                        {{-- View --}}
                        <button type="button"
                            class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                            data-bs-toggle="modal"
                            data-bs-target="#viewCustomerModal"
                            data-name="{{ $customer->name }}"
                            data-email="{{ $customer->email ?? '-' }}"
                            data-wsn="{{ $customer->wsn }}"
                            data-card="{{ $customer->card_number }}"
                            data-plat="{{ $customer->plat_number }}"
                            data-brand="{{ $customer->vehicle_brand }}"
                            data-model="{{ $customer->vehicle_model }}"
                            data-warranty="{{ $customer->warantee_duration }} tahun (s/d {{ $customer->warantee_end }})"
                            title="Lihat Detail">
                            <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                        </button>

                        {{-- Edit --}}
                        <a href="{{ route('customer.edit', $customer->id) }}"
                            class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                            title="Edit">
                            <iconify-icon icon="lucide:edit"></iconify-icon>
                        </a>

                        {{-- Delete --}}
                        <form action="{{ route('customer.destroy', $customer->id) }}" method="POST"
                            onsubmit="return confirm('Hapus data ini?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                                title="Hapus">
                                <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                            </button>
                        </form>

                        @if ($customer->invoice)
                            {{-- Lihat Invoice --}}
                            <a href="{{ route('invoice.show', $customer->invoice->id) }}"
                                class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                title="Lihat Invoice">
                                <iconify-icon icon="mdi:paper-outline"></iconify-icon>
                            </a>
                        @else
                            {{-- Buat Invoice --}}
                            <a href="{{ route('invoice.create', $customer->id) }}"
                                class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                                title="Buat Invoice">
                                <iconify-icon icon="ic:baseline-add-circle-outline"></iconify-icon>
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="99" class="py-5"><x-ui.empty-state title="Tidak ada data customer ditemukan." /></td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    @slot('footer'){{ $customers->withQueryString()->links() }}@endslot
</x-ui.card>

{{-- Shared View Customer Modal (single, populated by JS from data-* attributes) --}}
<div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-labelledby="viewCustomerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewCustomerModalLabel">Detail Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <dl class="row">
          <dt class="col-sm-4">Nama</dt>          <dd class="col-sm-8" id="cust-name"></dd>
          <dt class="col-sm-4">Email</dt>         <dd class="col-sm-8" id="cust-email"></dd>
          <dt class="col-sm-4">WSN</dt>           <dd class="col-sm-8" id="cust-wsn"></dd>
          <dt class="col-sm-4">No Kartu</dt>      <dd class="col-sm-8" id="cust-card"></dd>
          <dt class="col-sm-4">Plat Nomor</dt>    <dd class="col-sm-8" id="cust-plat"></dd>
          <dt class="col-sm-4">Merek Kendaraan</dt><dd class="col-sm-8" id="cust-brand"></dd>
          <dt class="col-sm-4">Model Kendaraan</dt><dd class="col-sm-8" id="cust-model"></dd>
          <dt class="col-sm-4">Garansi</dt>       <dd class="col-sm-8" id="cust-warranty"></dd>
        </dl>
      </div>
    </div>
  </div>
</div>
<script>
document.getElementById('viewCustomerModal').addEventListener('show.bs.modal', function (event) {
    var btn = event.relatedTarget;
    document.getElementById('cust-name').textContent    = btn.dataset.name    || '-';
    document.getElementById('cust-email').textContent   = btn.dataset.email   || '-';
    document.getElementById('cust-wsn').textContent     = btn.dataset.wsn     || '-';
    document.getElementById('cust-card').textContent    = btn.dataset.card    || '-';
    document.getElementById('cust-plat').textContent    = btn.dataset.plat    || '-';
    document.getElementById('cust-brand').textContent   = btn.dataset.brand   || '-';
    document.getElementById('cust-model').textContent   = btn.dataset.model   || '-';
    document.getElementById('cust-warranty').textContent = btn.dataset.warranty || '-';
});
</script>

{{-- Modal Warranty Card --}}
@foreach ($customers as $customer)
  @php
    // customerProducts.product / .categoryProduct already eager-loaded in CustomerController::index()
    $items = $customer->customerProducts;
  @endphp

  <div class="modal fade" id="warrantyCardModal{{ $customer->id }}" tabindex="-1" aria-labelledby="warrantyCardModalLabel{{ $customer->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Print Card</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <h6 class="text-center mb-3">Download Card</h6>

          @forelse ($items as $item)
            @php
              $prodName = strtoupper($item->product->name ?? 'PRODUCT');
              $catName  = strtoupper($item->categoryProduct->name ?? 'CAT');
              // Kalau punya field khusus (mis. shade/vlt), tampilkan; kalau tidak, hapus bagian ini.
              $shade    = $item->product->code ?? null;   // contoh kalau ada kolom code
              $vlt      = $item->product->vlt  ?? null;   // contoh kalau ada kolom vlt (%)
            @endphp

            <x-ui.button
              :href="route('customer.warranty.card.download.cp', ['cardNumber' => $customer->card_number, 'cp' => $item->id])"
              variant="success" class="w-100 mb-2"
            >
              download {{ $prodName }}
              @if($shade) | {{ strtoupper($shade) }} @endif
              @if(!is_null($vlt)) | {{ $vlt }}% @endif
            </x-ui.button>
          @empty
            <x-ui.alert type="warning">Belum ada item pada customer ini.</x-ui.alert>
          @endforelse
        </div>

        <div class="modal-footer">
          <x-ui.button variant="secondary" data-bs-dismiss="modal">Tutup</x-ui.button>
        </div>
      </div>
    </div>
  </div>
@endforeach
{{-- Modal Warranty Card End --}}

{{-- Script --}}
@include('partials.bulk-delete-script')
@endsection
