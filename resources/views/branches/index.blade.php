@extends('dashboard.index')

@section('title', 'Branches')
@section('route-breadcrumb', 'branches.index')
@section('breadcrumb', 'Branches')

@section('content')
<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Daftar Cabang</div>
            <x-ui.button href="{{ route('branches.create') }}" variant="primary" size="sm">
                <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Tambah Cabang
            </x-ui.button>
        </div>
    @endslot

    {{-- Search & Bulk Delete --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-2">

        <form method="GET" action="{{ route('branches.index') }}" id="filterForm"
              class="d-flex align-items-center gap-2 flex-wrap">
            <input type="text"
                   name="search"
                   value="{{ $search }}"
                   class="form-control"
                   placeholder="Cari nama / kode / kota..."
                   style="max-width: 260px;">
            <x-ui.button type="submit" variant="outline" size="sm">Cari</x-ui.button>
            @if($search)
                <x-ui.button href="{{ route('branches.index') }}" variant="secondary" size="sm">Reset</x-ui.button>
            @endif
        </form>

        <form id="bulkDeleteForm"
              action="{{ route('branches.bulkDelete') }}"
              method="POST"
              class="d-none"
              onsubmit="return confirm('Yakin hapus cabang terpilih?')">
            @csrf
            @method('DELETE')
            <input type="hidden" name="selected_ids" id="selected_ids">
            <x-ui.button type="submit" variant="danger" size="sm">Hapus Terpilih</x-ui.button>
        </form>

    </div>

    @if(session('success'))
        <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
    @endif
    @if(session('error'))
        <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
    @endif

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table bordered-table mb-0" style="min-width: 960px; white-space: nowrap;">
            <thead>
                <tr>
                    <th style="width:40px;">
                        <input type="checkbox" id="checkAll" class="form-check-input">
                    </th>
                    <th>Cabang</th>
                    <th>Kode</th>
                    <th>Tipe</th>
                    <th>Kota</th>
                    <th>Kontak</th>
                    <th>Customer</th>
                    <th>Karyawan</th>
                    <th>Status</th>
                    <th style="width:120px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($branches as $branch)
                    <tr>
                        <td>
                            <input type="checkbox" class="check-item form-check-input" value="{{ $branch->id }}">
                        </td>

                        <td>
                            <div class="fw-semibold" style="font-size:13px;">{{ $branch->name }}</div>
                            @if($branch->manager)
                                <div class="text-muted" style="font-size:11px;">
                                    <iconify-icon icon="mdi:account-star-outline" class="me-1"></iconify-icon>{{ $branch->manager->name }}
                                </div>
                            @endif
                        </td>

                        <td>
                            @if($branch->code)
                                <x-ui.badge variant="neutral" size="sm" style="font-weight:600; letter-spacing:.5px;">{{ $branch->code }}</x-ui.badge>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            @php
                                $typeStyle = match($branch->type) {
                                    'head_office'    => 'bg-primary-light text-primary-600',
                                    'warehouse'      => 'bg-warning-light text-warning-600',
                                    'service_center' => 'bg-info-light text-info-600',
                                    default          => 'bg-light text-secondary',
                                };
                            @endphp
                            <span class="badge {{ $typeStyle }}" style="font-size:11px;">
                                {{ $branch->type_label ?: 'Cabang' }}
                            </span>
                        </td>

                        <td>
                            <div>{{ $branch->city }}</div>
                            @if($branch->province)
                                <div class="text-muted" style="font-size:11px;">{{ $branch->province }}</div>
                            @endif
                        </td>

                        <td>
                            @if($branch->phone)
                                <div style="font-size:12px;"><iconify-icon icon="mdi:phone-outline" class="me-1 text-muted"></iconify-icon>{{ $branch->phone }}</div>
                            @endif
                            @if($branch->whatsapp)
                                <div style="font-size:12px;"><iconify-icon icon="mdi:whatsapp" class="me-1" style="color:#25d366;"></iconify-icon>{{ $branch->whatsapp }}</div>
                            @endif
                            @if(!$branch->phone && !$branch->whatsapp)
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td class="text-center">{{ $branch->customers_count }}</td>
                        <td class="text-center">{{ $branch->employees_count }}</td>

                        <td>
                            @if(($branch->status ?? 'active') === 'active')
                                <x-ui.badge variant="success" size="sm">Aktif</x-ui.badge>
                            @else
                                <x-ui.badge variant="danger" size="sm">Nonaktif</x-ui.badge>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex gap-1">
                                {{-- View --}}
                                <a href="javascript:void(0)"
                                   class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                                   data-bs-toggle="modal"
                                   data-bs-target="#viewBranchModal{{ $branch->id }}"
                                   title="Detail">
                                    <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                </a>

                                {{-- Edit --}}
                                <a href="{{ route('branches.edit', $branch->id) }}"
                                   class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                   title="Edit">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>

                                {{-- Delete --}}
                                <form action="{{ route('branches.destroy', $branch->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Hapus cabang {{ addslashes($branch->name) }}?')"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                                            title="Hapus">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="py-5">
                            <x-ui.empty-state title="Data cabang tidak ditemukan" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $branches->withQueryString()->links() }}@endslot
</x-ui.card>


{{-- ── Modal Detail per Cabang ── --}}
@foreach ($branches as $branch)
    <div class="modal fade"
         id="viewBranchModal{{ $branch->id }}"
         tabindex="-1"
         aria-labelledby="viewBranchModalLabel{{ $branch->id }}"
         aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1" id="viewBranchModalLabel{{ $branch->id }}">
                            {{ $branch->name }}
                            @if($branch->code)
                                <x-ui.badge variant="neutral" size="sm" class="ms-1" style="font-weight:600;">{{ $branch->code }}</x-ui.badge>
                            @endif
                        </h5>
                        <div class="d-flex gap-2 align-items-center">
                            @if(($branch->status ?? 'active') === 'active')
                                <x-ui.badge variant="success" size="sm">Aktif</x-ui.badge>
                            @else
                                <x-ui.badge variant="danger" size="sm">Nonaktif</x-ui.badge>
                            @endif
                            @if($branch->type)
                                <x-ui.badge variant="neutral" size="sm">{{ $branch->type_label }}</x-ui.badge>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-3 mb-4">
                        {{-- Informasi Dasar --}}
                        <div class="col-md-6">
                            <x-ui.card class="border h-100">
                                @slot('header')
                                    <small class="fw-semibold text-muted"><iconify-icon icon="mdi:office-building-outline" class="me-1"></iconify-icon>INFORMASI DASAR</small>
                                @endslot
                                <dl class="row mb-0" style="font-size:13px;">
                                    <dt class="col-5 fw-normal text-muted">Kota</dt>
                                    <dd class="col-7">{{ $branch->city }}{{ $branch->province ? ', '.$branch->province : '' }}</dd>

                                    @if($branch->district || $branch->postal_code)
                                        <dt class="col-5 fw-normal text-muted">Kecamatan</dt>
                                        <dd class="col-7">{{ $branch->district ?: '—' }}@if($branch->postal_code) · {{ $branch->postal_code }}@endif</dd>
                                    @endif

                                    <dt class="col-5 fw-normal text-muted">Alamat</dt>
                                    <dd class="col-7" style="white-space:normal;">{{ $branch->address }}</dd>

                                    @if($branch->opening_date)
                                        <dt class="col-5 fw-normal text-muted">Berdiri</dt>
                                        <dd class="col-7">{{ $branch->opening_date->format('d M Y') }}</dd>
                                    @endif
                                </dl>
                            </x-ui.card>
                        </div>

                        {{-- Kontak & Operasional --}}
                        <div class="col-md-6">
                            <x-ui.card class="border h-100">
                                @slot('header')
                                    <small class="fw-semibold text-muted"><iconify-icon icon="mdi:phone-outline" class="me-1"></iconify-icon>KONTAK & OPERASIONAL</small>
                                @endslot
                                <dl class="row mb-0" style="font-size:13px;">
                                    @if($branch->phone)
                                        <dt class="col-5 fw-normal text-muted">Telepon</dt>
                                        <dd class="col-7">{{ $branch->phone }}</dd>
                                    @endif
                                    @if($branch->whatsapp)
                                        <dt class="col-5 fw-normal text-muted">WhatsApp</dt>
                                        <dd class="col-7">{{ $branch->whatsapp }}</dd>
                                    @endif
                                    @if($branch->email)
                                        <dt class="col-5 fw-normal text-muted">Email</dt>
                                        <dd class="col-7">{{ $branch->email }}</dd>
                                    @endif
                                    @if($branch->opening_time || $branch->closing_time)
                                        <dt class="col-5 fw-normal text-muted">Jam Operasional</dt>
                                        <dd class="col-7">
                                            {{ $branch->opening_time ?? '—' }} – {{ $branch->closing_time ?? '—' }}
                                        </dd>
                                    @endif
                                    @if(!empty($branch->working_days))
                                        <dt class="col-5 fw-normal text-muted">Hari Kerja</dt>
                                        <dd class="col-7" style="white-space:normal;">
                                            {{ implode(', ', $branch->working_days) }}
                                        </dd>
                                    @endif
                                    @if($branch->manager)
                                        <dt class="col-5 fw-normal text-muted">Manager</dt>
                                        <dd class="col-7">{{ $branch->manager->name }}</dd>
                                    @endif
                                    @if($branch->pic)
                                        <dt class="col-5 fw-normal text-muted">PIC</dt>
                                        <dd class="col-7">{{ $branch->pic->name }}</dd>
                                    @endif
                                </dl>
                            </x-ui.card>
                        </div>

                        {{-- Stats --}}
                        <div class="col-12">
                            <div class="row g-2">
                                <div class="col-md-3 col-6">
                                    <x-ui.stats-card label="Customer Mobil" :value="$branch->customers_count" color="blue" />
                                </div>
                                <div class="col-md-3 col-6">
                                    <x-ui.stats-card label="Customer Gedung" :value="$branch->customer_buildings_count" color="purple" />
                                </div>
                                <div class="col-md-3 col-6">
                                    <x-ui.stats-card label="Karyawan" :value="$branch->employees_count" color="green" />
                                </div>
                            </div>
                        </div>

                        @if($branch->notes)
                            <div class="col-12">
                                <x-ui.alert type="info" class="mb-0">
                                    <strong class="d-block mb-1"><iconify-icon icon="mdi:note-text-outline" class="me-1"></iconify-icon>Catatan Internal</strong>
                                    {{ $branch->notes }}
                                </x-ui.alert>
                            </div>
                        @endif
                    </div>

                    <hr>

                    {{-- Data links — counts above are already loaded via withCount(); the actual rows are
                         only fetched when you follow the link, on the existing paginated list pages.
                         This modal used to eager-render every customer/building/employee row for every
                         branch on the page (even ones you never opened) — that's gone now. --}}
                    <h6 class="mb-3 fw-semibold">Data Terkait</h6>
                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <a href="{{ route('customer.index', ['branch_id' => $branch->id]) }}"
                               class="d-flex align-items-center justify-content-between border rounded p-3 text-decoration-none">
                                <span><iconify-icon icon="mdi:car-outline" class="me-2 text-primary"></iconify-icon>Data Customer</span>
                                <span>
                                    <x-ui.badge variant="primary" size="sm" class="me-2">{{ $branch->customers_count }}</x-ui.badge>
                                    <iconify-icon icon="mdi:chevron-right"></iconify-icon>
                                </span>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('customer.building.index', ['branch_id' => $branch->id]) }}"
                               class="d-flex align-items-center justify-content-between border rounded p-3 text-decoration-none">
                                <span><iconify-icon icon="mdi:office-building-outline" class="me-2 text-primary"></iconify-icon>Data Customer Gedung</span>
                                <span>
                                    <x-ui.badge variant="neutral" size="sm" class="me-2">{{ $branch->customer_buildings_count }}</x-ui.badge>
                                    <iconify-icon icon="mdi:chevron-right"></iconify-icon>
                                </span>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('employee.index', ['branch_id' => $branch->id]) }}"
                               class="d-flex align-items-center justify-content-between border rounded p-3 text-decoration-none">
                                <span><iconify-icon icon="mdi:account-multiple-outline" class="me-2 text-primary"></iconify-icon>Data Karyawan</span>
                                <span>
                                    <x-ui.badge variant="success" size="sm" class="me-2">{{ $branch->employees_count }}</x-ui.badge>
                                    <iconify-icon icon="mdi:chevron-right"></iconify-icon>
                                </span>
                            </a>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <x-ui.button href="{{ route('branches.edit', $branch->id) }}" variant="primary" size="sm">
                        <iconify-icon icon="mdi:pencil-outline" class="me-1"></iconify-icon> Edit Cabang
                    </x-ui.button>
                    <x-ui.button variant="secondary" size="sm" data-bs-dismiss="modal">Tutup</x-ui.button>
                </div>

            </div>
        </div>
    </div>
@endforeach


@include('partials.bulk-delete-script')
@endsection
