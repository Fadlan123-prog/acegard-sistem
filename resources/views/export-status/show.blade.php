@extends('dashboard.index')

@section('title', 'Memproses...')
@section('route-breadcrumb', 'dashboard')
@section('breadcrumb', 'Memproses')

@section('content')
<x-ui.card style="max-width:560px;margin:60px auto;">
    <div class="text-center p-4">

        <div id="state-pending" class="{{ in_array($exportRequest->status, ['pending','processing']) ? '' : 'd-none' }}">
            <div class="mb-4">
                <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
            </div>
            <h5 class="fw-semibold mb-1">Sedang memproses…</h5>
            <p class="text-muted small mb-0">Halaman ini akan otomatis lanjut ketika selesai.</p>
        </div>

        <div id="state-done" class="d-none">
            <div class="mb-3">
                <iconify-icon icon="mdi:check-circle" style="font-size:3rem;color:#16a34a;"></iconify-icon>
            </div>
            <h5 class="fw-semibold mb-3">Selesai!</h5>
            <x-ui.button id="done-action" href="#" variant="primary" class="px-4">Lihat Hasil</x-ui.button>
        </div>

        <div id="state-failed" class="{{ $exportRequest->isFailed() ? '' : 'd-none' }}">
            <div class="mb-3">
                <iconify-icon icon="mdi:alert-circle" style="font-size:3rem;color:#dc2626;"></iconify-icon>
            </div>
            <h5 class="fw-semibold mb-2">Gagal memproses</h5>
            <p id="error-message" class="text-muted small mb-3">{{ $exportRequest->error_message }}</p>
            <x-ui.button href="javascript:history.back()" variant="outline">Kembali</x-ui.button>
        </div>

    </div>
</x-ui.card>

@if(in_array($exportRequest->status, ['pending', 'processing']))
<script>
(function () {
    const checkUrl  = @json(route('export-status.check', $exportRequest->id));
    const interval  = 2000;
    let   timer     = null;

    function check() {
        fetch(checkUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'done') {
                    clearInterval(timer);
                    document.getElementById('state-pending').classList.add('d-none');
                    document.getElementById('state-done').classList.remove('d-none');

                    const btn = document.getElementById('done-action');
                    if (data.redirect_url) {
                        btn.href        = data.redirect_url;
                        btn.textContent = 'Lihat Payroll';
                    } else if (data.download_url) {
                        btn.href        = data.download_url;
                        btn.textContent = 'Download File';
                        btn.setAttribute('download', '');
                    }
                } else if (data.status === 'failed') {
                    clearInterval(timer);
                    document.getElementById('state-pending').classList.add('d-none');
                    document.getElementById('state-failed').classList.remove('d-none');
                    document.getElementById('error-message').textContent = data.error ?? 'Terjadi kesalahan.';
                }
            })
            .catch(() => {}); // silent — retry on next tick
    }

    timer = setInterval(check, interval);
    check(); // immediate first check
})();
</script>
@endif
@endsection
