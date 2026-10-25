{{-- Penugasan Sales Order Masuk: nama Anda sudah dicatat di SO (lewat
     SalesOrderController::assign()), tapi admin belum membuat Customer &
     Invoice-nya — sekadar pratinjau, tidak ada tombol terima/selesai karena
     belum ada invoice untuk ditindaklanjuti. Dipakai di dashboard teknisi
     maupun halaman "Semua Orderan" supaya tidak ada satu pun tempat yang
     terasa kosong begitu ada penugasan baru. --}}
@if ($incomingSalesOrders->isNotEmpty())
    <x-ui.card class="mb-3">
        <div class="d-flex align-items-center gap-2 mb-3">
            <iconify-icon icon="mdi:clipboard-clock-outline" class="fs-5 text-info"></iconify-icon>
            <div>
                <div class="fw-semibold" style="font-size:14px">Penugasan Sales Order Masuk</div>
                <div class="ui-text-muted" style="font-size:11px">
                    Nama Anda sudah tercatat di Sales Order berikut. Belum jadi order aktif —
                    menunggu diproses admin.
                </div>
            </div>
        </div>
        <div class="d-flex flex-column gap-2">
            @foreach ($incomingSalesOrders as $so)
                <div class="d-flex align-items-center justify-content-between gap-3 p-2 rounded"
                     style="background:var(--neutral-50)">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold" style="font-size:13px">{{ $so->prospect_name }}</span>
                            <x-ui.badge variant="info" size="sm">{{ $so->category_label }}</x-ui.badge>
                        </div>
                        <div class="ui-text-muted" style="font-size:11px">
                            {{ $so->so_number }}
                            @if ($so->scheduled_date)
                                &bull; Jadwal {{ $so->scheduled_date->translatedFormat('d M Y') }}
                            @else
                                &bull; Belum dijadwalkan
                            @endif
                        </div>
                    </div>
                    <x-ui.badge :variant="$so->status_color" size="sm">{{ $so->status_label }}</x-ui.badge>
                </div>
            @endforeach
        </div>
    </x-ui.card>
@endif
