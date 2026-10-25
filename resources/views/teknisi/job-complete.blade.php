@extends('layout.index')

@section('title', 'Selesaikan Pekerjaan')
@section('route-breadcrumb', route('teknisi.dashboard'))
@section('breadcrumb', 'Pesanan Saya')

@section('content')

<x-ui.card class="mb-4">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Konfirmasi Penyelesaian Pekerjaan</div>
            <x-ui.button variant="outline" size="sm" href="{{ route('teknisi.dashboard') }}">
                <iconify-icon icon="mdi:arrow-left" class="me-1"></iconify-icon>Kembali
            </x-ui.button>
        </div>
    @endslot

    {{-- Error Validation --}}
    @if ($errors->any())
        <x-ui.alert type="danger" class="mb-4">
            <strong>Terjadi kesalahan.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    {{-- Job Summary --}}
    <div class="bg-light rounded p-3 mb-4">
        <h6 class="fw-semibold mb-3">Detail Order</h6>
        <div class="row gy-2">
            <div class="col-6">
                <div class="text-muted small">Nama Customer</div>
                <div class="fw-medium">{{ $customer->name }}</div>
            </div>
            <div class="col-6">
                <div class="text-muted small">Kendaraan</div>
                <div class="fw-medium small">
                    {{ $customer->vehicle_brand }} {{ $customer->vehicle_model }}
                    &bull; {{ $customer->plat_number }}
                </div>
            </div>
            <div class="col-6">
                <div class="text-muted small">Jenis Pemasangan</div>
                <div class="fw-medium">{{ strtoupper($customer->install_type ?? '-') }}</div>
            </div>
            <div class="col-6">
                <div class="text-muted small">Cabang</div>
                <div class="fw-medium">{{ $customer->branch->name ?? '-' }}</div>
            </div>
            @if ($invoice)
                <div class="col-6">
                    <div class="text-muted small">No. Invoice</div>
                    <div class="fw-medium small">{{ $invoice->invoice_number ?? '-' }}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted small">Status Pembayaran</div>
                    <div class="fw-medium">
                        @if ($invoice->status === 'paid')
                            <x-ui.badge variant="success">Lunas</x-ui.badge>
                        @elseif ($invoice->status === 'partial')
                            <x-ui.badge variant="warning">DP</x-ui.badge>
                        @else
                            <x-ui.badge variant="neutral">Belum Lunas</x-ui.badge>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Completion Form --}}
    <form action="{{ route('teknisi.job.complete', $customer->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row gy-3">

            {{-- Job Photo --}}
            <div class="col-12 col-md-6">
                <label class="form-label fw-medium">
                    Foto Hasil Pekerjaan <span class="text-danger">*</span>
                </label>
                <input type="file"
                       name="job_photo"
                       id="job_photo"
                       class="form-control @error('job_photo') is-invalid @enderror"
                       accept="image/*"
                       capture="environment"
                       required>
                @error('job_photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">JPG, PNG, WEBP. Maks. 5MB.</small>
                <div id="job_photo_preview" class="mt-2 d-none">
                    <img id="job_photo_img" src="#"
                         class="rounded border w-100"
                         style="max-height:220px;object-fit:cover"
                         alt="Preview">
                </div>
            </div>

            {{-- Checklist Photo --}}
            <div class="col-12 col-md-6">
                <label class="form-label fw-medium">
                    Foto Checklist <span class="text-danger">*</span>
                </label>
                <input type="file"
                       name="checklist_photo"
                       id="checklist_photo"
                       class="form-control @error('checklist_photo') is-invalid @enderror"
                       accept="image/*"
                       capture="environment"
                       required>
                @error('checklist_photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">JPG, PNG, WEBP. Maks. 5MB.</small>
                <div id="checklist_photo_preview" class="mt-2 d-none">
                    <img id="checklist_photo_img" src="#"
                         class="rounded border w-100"
                         style="max-height:220px;object-fit:cover"
                         alt="Preview">
                </div>
            </div>

            {{-- Notes --}}
            <div class="col-12">
                <label class="form-label fw-medium">Catatan (opsional)</label>
                <textarea name="notes"
                          class="form-control @error('notes') is-invalid @enderror"
                          rows="3"
                          placeholder="Catatan tambahan mengenai pekerjaan...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>

        <hr class="my-4">

        <x-ui.alert type="warning" class="mb-4">
            <iconify-icon icon="mdi:information-outline" class="me-1"></iconify-icon>
            Setelah menekan <strong>Konfirmasi Selesai</strong>, status pekerjaan berubah menjadi
            <strong>Selesai</strong> dan tidak dapat dibatalkan. Pastikan foto sudah benar.
        </x-ui.alert>

        <div class="d-grid d-sm-flex gap-2">
            <x-ui.button variant="success" type="submit" class="px-4"
                    onclick="return confirm('Yakin pekerjaan ini sudah selesai? Tindakan ini tidak dapat dibatalkan.')">
                <iconify-icon icon="mdi:check-all" class="me-1"></iconify-icon> Konfirmasi Selesai
            </x-ui.button>
            <x-ui.button variant="outline" href="{{ route('teknisi.dashboard') }}" class="px-4">Batal</x-ui.button>
        </div>

    </form>

</x-ui.card>

<script>
    function setupPreview(inputId, previewWrapperId, previewImgId) {
        document.getElementById(inputId).addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (ev) {
                document.getElementById(previewImgId).src = ev.target.result;
                document.getElementById(previewWrapperId).classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }

    setupPreview('job_photo', 'job_photo_preview', 'job_photo_img');
    setupPreview('checklist_photo', 'checklist_photo_preview', 'checklist_photo_img');
</script>

@endsection
