@extends('dashboard.index')

@section('title', 'Approval Rekrutan Sales')
@section('route-breadcrumb', 'sales-network.pending')
@section('breadcrumb', 'Approval Rekrutan Sales')

@section('content')

@php
    $minLen = config('password_setup.min_length', 10);
    // Setelah validasi gagal, buka lagi modal milik pendaftar yang sedang diproses.
    $reopenId = $errors->any() ? (int) old('_profile') : 0;
@endphp

<x-ui.card class="basic-data-table">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Menunggu Approval</div>
            <x-ui.button :href="route('sales-network.admin-index')" variant="outline" size="sm">Lihat Semua Anggota</x-ui.button>
        </div>
    @endslot

    <div class="table-responsive">
        <table class="table bordered-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Nama Calon</th>
                    <th>Cabang</th>
                    <th>Direkrut Oleh</th>
                    <th>Tanggal</th>
                    <th style="width:180px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profiles as $profile)
                <tr>
                    <td>{{ $profiles->firstItem() + $loop->index }}</td>
                    <td class="fw-semibold">{{ $profile->employee?->name ?? '-' }}</td>
                    <td class="small text-muted">{{ $profile->employee?->branch?->name ?? '-' }}</td>
                    <td class="small">{{ $profile->sponsor?->name ?? '-' }}</td>
                    <td class="small text-muted">{{ $profile->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <x-ui.button type="button" variant="success" size="sm"
                                         data-bs-toggle="modal" data-bs-target="#approveModal{{ $profile->id }}">
                                <iconify-icon icon="mdi:check"></iconify-icon> Setujui
                            </x-ui.button>
                            <form method="POST" action="{{ route('sales-network.reject', $profile) }}" onsubmit="return confirm('Tolak dan hapus pendaftaran ini?')">
                                @csrf
                                <x-ui.button type="submit" variant="danger-outline" size="sm">
                                    <iconify-icon icon="mdi:close"></iconify-icon> Tolak
                                </x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-5"><x-ui.empty-state title="Tidak ada pendaftaran yang menunggu." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $profiles->links() }}@endslot
</x-ui.card>

{{-- Modal approve: satu per pendaftar --}}
@foreach($profiles as $profile)
@php
    $isThis = $reopenId === $profile->id;
    $mode   = $isThis ? old('password_mode', 'invite') : 'invite';
    $email  = $profile->employee?->user?->email;
@endphp
<div class="modal fade" id="approveModal{{ $profile->id }}" tabindex="-1" aria-labelledby="approveLabel{{ $profile->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('sales-network.approve', $profile) }}" class="modal-content" autocomplete="off">
            @csrf
            <input type="hidden" name="_profile" value="{{ $profile->id }}">

            <div class="modal-header">
                <h6 class="modal-title" id="approveLabel{{ $profile->id }}">Setujui {{ $profile->employee?->name }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                @if($isThis && $errors->any())
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3 small">
                            @foreach($errors->all() as $msg)<li>{{ $msg }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <p class="small text-muted mb-3">Pilih cara sales ini mendapatkan password.</p>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="password_mode" value="invite"
                           id="modeInvite{{ $profile->id }}" @checked($mode === 'invite')
                           onchange="document.getElementById('manualBox{{ $profile->id }}').classList.add('d-none')">
                    <label class="form-check-label" for="modeInvite{{ $profile->id }}">
                        <span class="fw-semibold">Kirim tautan buat-password ke email</span>
                        <span class="badge bg-success ms-1">Disarankan</span>
                        <span class="d-block small text-muted">
                            Sales membuat passwordnya sendiri lewat tautan sekali pakai (berlaku 48 jam).
                            Admin tidak pernah tahu passwordnya.
                            @if($email) Dikirim ke <strong>{{ $email }}</strong>.@endif
                        </span>
                    </label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="password_mode" value="manual"
                           id="modeManual{{ $profile->id }}" @checked($mode === 'manual')
                           onchange="document.getElementById('manualBox{{ $profile->id }}').classList.remove('d-none')">
                    <label class="form-check-label" for="modeManual{{ $profile->id }}">
                        <span class="fw-semibold">Tentukan password sementara sendiri</span>
                        <span class="d-block small text-muted">
                            Sales wajib menggantinya saat login pertama. Sampaikan langsung, bukan lewat grup.
                        </span>
                    </label>
                </div>

                <div id="manualBox{{ $profile->id }}" class="border rounded p-3 {{ $mode === 'manual' ? '' : 'd-none' }}">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password sementara</label>
                        <input type="password" name="password" class="form-control" autocomplete="new-password">
                        <div class="form-text">Minimal {{ $minLen }} karakter, huruf besar + kecil + angka.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ulangi password sementara</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Password ANDA (konfirmasi admin)</label>
                        <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                        <div class="form-text">Untuk memastikan ini benar-benar Anda, bukan sesi yang dipinjam.</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success btn-sm">Setujui</button>
            </div>
        </form>
    </div>
</div>
@endforeach

@if($reopenId)
@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('approveModal{{ $reopenId }}');
        if (el && window.bootstrap) { new bootstrap.Modal(el).show(); }
    });
</script>
@endpush
@endif

@endsection
