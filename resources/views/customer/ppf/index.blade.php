@extends('dashboard.index')

@section('title', 'Customers PPF')
@section('route-breadcrumb', 'ppf-customers.index')
@section('breadcrumb', 'Customers PPF')

@section('content')
<div class="card basic-data-table">

  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="card-title mb-0">Daftar Customer PPF</h5>
    <div class="d-flex gap-2">
      <a href="{{ route('ppf-customers.create') }}" class="btn btn-primary btn-sm">+ Add Customer PPF</a>
    </div>
  </div>

  <div class="card-body">

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-2">
      <form method="GET" action="{{ route('ppf-customers.index') }}" id="filterForm"
            class="d-flex align-items-center gap-2 flex-wrap">

        <input type="text" name="search" value="{{ request('search') }}"
               class="form-control" placeholder="Cari nama / WSN / card..." style="max-width: 250px;">

        <button type="submit" class="btn btn-outline-primary">Search</button>

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
          <a href="{{ route('ppf-customers.index') }}" class="btn btn-outline-secondary">Reset</a>
        @endif
      </form>
    </div>

    <div class="table-responsive ppf-table-responsive">
        <table class="table bordered-table mb-0">
        <thead>
            <tr>
            <th>WSN</th>
            <th>Nomor Kartu</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Garansi</th>
            <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($customers as $customer)
            @php
                $installs = ($customer->installations ?? collect())->sortByDesc('install_date')->values();
                $latest = $installs->first();
                $today = now()->toDateString();

                $hasActive = $installs->contains(function($i) use ($today){
                return ($i->warranty_status ?? 'active') === 'active'
                    && !empty($i->warranty_end)
                    && $i->warranty_end >= $today;
                });
            @endphp

            <tr>
                <td>{{ $customer->wsn }}</td>
                <td>{{ $customer->card_number }}</td>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->email }}</td>
                <td>
                @if($hasActive)
                    <span class="badge bg-success">Aktif</span>
                @else
                    <span class="badge bg-danger">Kadaluarsa</span>
                @endif

                @if($latest && $latest->warranty_end)
                    <div class="text-muted small">s/d {{ $latest->warranty_end }}</div>
                @endif
                </td>

                <td>
                <div class="d-flex gap-1">

                    {{-- Print Card --}}
                    <a href="javascript:void(0)"
                    class="w-32-px h-32-px bg-warning-focus text-warning-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                    data-bs-toggle="modal"
                    data-bs-target="#warrantyCardModal{{ $customer->id }}"
                    title="Print Card">
                    <iconify-icon icon="ion:card-outline"></iconify-icon>
                    </a>

                    {{-- View --}}
                    <a href="javascript:void(0)"
                    class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                    data-bs-toggle="modal"
                    data-bs-target="#viewCustomerModal{{ $customer->id }}"
                    title="View">
                    <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                    </a>

                    {{-- Edit --}}
                    <a href="{{ route('ppf-customers.edit', $customer->id) }}"
                        class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                        <iconify-icon icon="lucide:edit"></iconify-icon>
                    </a>

                    {{-- Delete --}}
                    <form action="{{ route('ppf-customers.destroy', $customer->id) }}" method="POST"
                        onsubmit="return confirm('Hapus data ini? Semua instalasi akan ikut terhapus.')"
                        class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                            title="Delete">
                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                    </button>
                    </form>

                    {{-- Invoice (gunakan instalasi terbaru saja agar tidak spam tombol) --}}
                    @if($latest)
                    @if (optional($latest)->invoice)
                        <a href="{{ route('ppf-invoices.show', $latest->invoice->id) }}"
                        class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center ms-1"
                        title="Lihat Invoice">
                        <iconify-icon icon="mdi:paper-outline"></iconify-icon>
                        </a>
                    @else
                        <a href="{{ route('ppf-invoices.create', $latest->id) }}"
                        class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center ms-1"
                        title="Buat Invoice">
                        <iconify-icon icon="ic:baseline-add-circle-outline"></iconify-icon>
                        </a>
                    @endif
                    @endif

                </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center">Data tidak ditemukan.</td></tr>
            @endforelse
        </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $customers->withQueryString()->links() }}</div>
  </div>
</div>

{{-- MODAL VIEW + MODAL CARD (biar singkat, tetap pakai punyamu yang sudah bagus) --}}
{{-- Pastikan di controller index() kamu eager load: installations.ppfProduct dan installations.technicians --}}
{{-- RENDER MODAL (WAJIB) --}}
@foreach($customers as $customer)
  {{-- Modal View Customer --}}
  <div class="modal fade" id="viewCustomerModal{{ $customer->id }}" tabindex="-1"
       aria-labelledby="viewCustomerModalLabel{{ $customer->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewCustomerModalLabel{{ $customer->id }}">
            Detail Customer PPF
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="border rounded p-3">
                <div class="fw-semibold mb-2">Customer</div>
                <div class="small text-muted">WSN</div>
                <div class="mb-2">{{ $customer->wsn }}</div>

                <div class="small text-muted">Card Number</div>
                <div class="mb-2">{{ $customer->card_number }}</div>

                <div class="small text-muted">Nama</div>
                <div class="mb-2">{{ $customer->name }}</div>

                <div class="small text-muted">Email</div>
                <div class="mb-0">{{ $customer->email ?: '-' }}</div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="border rounded p-3">
                <div class="fw-semibold mb-2">Kendaraan</div>
                <div class="small text-muted">Brand / Model</div>
                <div class="mb-2">{{ $customer->vehicle_brand }} / {{ $customer->vehicle_model }}</div>

                <div class="small text-muted">Tahun</div>
                <div class="mb-2">{{ $customer->vehicle_year }}</div>

                <div class="small text-muted">Plat</div>
                <div class="mb-2">{{ $customer->vehicle_plate }}</div>

                <div class="small text-muted">Warna</div>
                <div class="mb-0">{{ $customer->vehicle_color }}</div>
              </div>
            </div>

            <div class="col-12">
              <div class="border rounded p-3">
                <div class="fw-semibold mb-2">Riwayat Instalasi</div>

                @php
                  $installs = ($customer->installations ?? collect())->sortByDesc('install_date')->values();
                @endphp

                @forelse($installs as $ins)
                  <div class="d-flex justify-content-between align-items-start border rounded p-2 mb-2">
                    <div>
                      <div class="fw-semibold">{{ $ins->ppfProduct->name ?? '-' }}</div>
                      <div class="small text-muted">
                        Tanggal: {{ $ins->install_date }} • End: {{ $ins->warranty_end ?? '-' }}
                      </div>
                    </div>
                    <span class="badge {{ ($ins->warranty_status ?? 'active') === 'active' ? 'bg-success' : 'bg-danger' }}">
                      {{ strtoupper($ins->warranty_status ?? '-') }}
                    </span>
                  </div>
                @empty
                  <div class="text-muted small">Belum ada instalasi.</div>
                @endforelse

              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal Print Card (kalau tombol print card juga dipakai) --}}
<div class="modal fade" id="warrantyCardModal{{ $customer->id }}" tabindex="-1"
     aria-labelledby="warrantyCardModalLabel{{ $customer->id }}" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="warrantyCardModalLabel{{ $customer->id }}">PPF Warranty Card</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        @php $insId = optional($latest)->id; @endphp

        @if(!$insId)
          <div class="alert alert-warning mb-0">
            No installation found for this customer.
          </div>
        @else
          <div class="text-muted small mb-3">
            Preview the warranty card (Front/Back) and download if needed.
          </div>

          <ul class="nav nav-pills mb-3" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" data-bs-toggle="pill"
                      data-bs-target="#ppfFront{{ $customer->id }}" type="button" role="tab">
                Front
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="pill"
                      data-bs-target="#ppfBack{{ $customer->id }}" type="button" role="tab">
                Back
              </button>
            </li>
          </ul>

          <div class="tab-content">
            <div class="tab-pane fade show active" id="ppfFront{{ $customer->id }}" role="tabpanel">
              <img class="img-fluid rounded border"
                   src="{{ route('public.warranty.ppf.preview', $insId) }}?side=front"
                   alt="PPF Card Front">
            </div>

            <div class="tab-pane fade" id="ppfBack{{ $customer->id }}" role="tabpanel">
              <img class="img-fluid rounded border"
                   src="{{ route('public.warranty.ppf.preview', $insId) }}?side=back"
                   alt="PPF Card Back">
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2 mt-3">
            <a href="{{ route('public.warranty.ppf.download', $insId) }}?side=front"
               class="btn btn-primary btn-sm">
              Download Front
            </a>

            <a href="{{ route('public.warranty.ppf.download', $insId) }}?side=back"
               class="btn btn-outline-primary btn-sm">
              Download Back
            </a>

            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
              Close
            </button>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@endforeach

@endsection
