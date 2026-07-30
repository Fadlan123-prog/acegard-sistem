@extends('dashboard.index')

@section('title', 'Detail Payroll')
@section('route-breadcrumb', 'payrolls.index')
@section('breadcrumb', 'Detail Payroll')

@section('content')

@if(session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif

@if(session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

@php
    $totalGapok = $payroll->items->sum('monthly_salary');
    $totalProrate = $payroll->items->sum('prorate_salary');
    $totalMotor = $payroll->items->sum('transport_allowance');
    $totalMakan = $payroll->items->sum('meal_allowance');
    $totalPotonganRokok = $payroll->items->sum('main_deduction');
    $totalKasbon = $payroll->items->sum('cash_advance');
    $totalPotonganAbsen = $payroll->items->sum('attendance_deduction');
    $totalGaji = $payroll->items->sum('total_salary');
    $totalBulat = $payroll->items->sum('rounded_salary');
@endphp

<x-ui.card>

    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Detail Payroll</div>
                <small class="text-muted">
                    {{ $payroll->branch->name ?? '-' }} -
                    {{ \Carbon\Carbon::parse($payroll->month . '-01')->format('M Y') }}
                </small>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                {{-- Slip Gaji --}}
                <x-ui.button href="{{ route('payrolls.slip.all', $payroll->id) }}" target="_blank" variant="secondary" size="sm">
                    <iconify-icon icon="mdi:file-pdf-box" class="me-1"></iconify-icon>
                    Download Semua Slip
                </x-ui.button>

                @if($payroll->status === 'draft')
                    <x-ui.button href="{{ route('payrolls.edit', $payroll->id) }}" variant="success" size="sm">
                        Edit Payroll
                    </x-ui.button>

                    <form method="POST" action="{{ route('payrolls.approve', $payroll->id) }}"
                          onsubmit="return confirm('Approve payroll ini?')">
                        @csrf
                        <x-ui.button type="submit" variant="primary" size="sm">
                            Approve
                        </x-ui.button>
                    </form>
                @endif

                @if($payroll->status === 'approved')
                    <form method="POST" action="{{ route('payrolls.paid', $payroll->id) }}"
                          onsubmit="return confirm('Tandai payroll ini sebagai paid?')">
                        @csrf
                        <x-ui.button type="submit" variant="success" size="sm">
                            Mark Paid
                        </x-ui.button>
                    </form>
                @endif

                @if($payroll->status === 'paid')
                    <form method="POST" action="{{ route('payrolls.destroy', $payroll->id) }}"
                          onsubmit="return confirm('Hapus payroll ini?')">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger" size="sm">
                            Hapus
                        </x-ui.button>
                    </form>
                @endif

                @if($payroll->status === 'draft')
                    <form method="POST" action="{{ route('payrolls.refresh', $payroll->id) }}"
                        onsubmit="return confirm('Refresh payroll dari data absensi terbaru? Data manual seperti uang makan/kasbon bisa ikut reset jika tidak disimpan.')">
                        @csrf
                        <x-ui.button type="submit" variant="warning" size="sm">
                            Refresh Dari Absensi
                        </x-ui.button>
                    </form>
                @endif

                <x-ui.button href="{{ route('payrolls.index') }}" variant="secondary" size="sm">
                    Kembali
                </x-ui.button>
            </div>
        </div>
    @endslot

    <div class="row mb-4">

        <div class="col-md-3 mb-2">
            <x-ui.stats-card label="Bulan" :value="\Carbon\Carbon::parse($payroll->month . '-01')->format('M Y')" color="blue" />
        </div>

        <div class="col-md-3 mb-2">
            <x-ui.stats-card label="Cabang" :value="$payroll->branch->name ?? '-'" color="blue" />
        </div>

        <div class="col-md-3 mb-2">
            <x-ui.stats-card label="Hari Kerja" :value="$payroll->working_days . ' Hari'" color="blue" />
        </div>

        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">Status</small>
                <h6 class="mb-0">
                    @if($payroll->status === 'draft')
                        <x-ui.badge variant="warning">Draft</x-ui.badge>
                    @elseif($payroll->status === 'approved')
                        <x-ui.badge variant="primary">Approved</x-ui.badge>
                    @elseif($payroll->status === 'paid')
                        <x-ui.badge variant="success">Paid</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral">{{ ucfirst($payroll->status) }}</x-ui.badge>
                    @endif
                </h6>
            </div>
        </div>

    </div>

    <div class="row mb-4">

        <div class="col-md-3 mb-2">
            <x-ui.stats-card label="Total Gapok" :value="'Rp' . number_format($totalGapok, 0, ',', '.')" color="blue" />
        </div>

        <div class="col-md-3 mb-2">
            <x-ui.stats-card label="Total Prorate" :value="'Rp' . number_format($totalProrate, 0, ',', '.')" color="green" />
        </div>

        <div class="col-md-3 mb-2">
            <x-ui.stats-card label="Total Potongan" :value="'Rp' . number_format($totalPotonganRokok + $totalKasbon + $totalPotonganAbsen, 0, ',', '.')" color="red" />
        </div>

        <div class="col-md-3 mb-2">
            <x-ui.stats-card label="Total Gaji Bulat" :value="'Rp' . number_format($totalBulat, 0, ',', '.')" color="green" />
        </div>

    </div>

    @if($payroll->notes)
        <x-ui.alert type="info" class="mb-4">
            <strong>Catatan:</strong> {{ $payroll->notes }}
        </x-ui.alert>
    @endif

    <div class="table-responsive" style="max-height: 680px; overflow-y: auto; overflow-x: auto;">
        <table class="table bordered-table align-middle mb-0" style="min-width: 1900px; white-space: nowrap;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Masuk</th>
                    <th>Libur</th>
                    <th>Sisa Jatah Libur</th>
                    <th>Lebih</th>
                    <th>Gapok</th>
                    <th>Prorate</th>
                    <th>Uang Motor</th>
                    <th>Uang Makan</th>
                    <th>Potongan Rokok</th>
                    <th>Kasbon</th>
                    <th>Potongan Absen</th>
                    <th>Total</th>
                    <th>Pembulatan</th>
                    <th>Catatan</th>
                    <th>Slip</th>
                </tr>
            </thead>

            <tbody>
                @forelse($payroll->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $item->employee_name }}</td>
                        <td>{{ $item->job_position ?? '-' }}</td>

                        <td>{{ $item->present_days }}</td>
                        <td>{{ $item->off_days }}</td>
                        <td>
                            @php
                                $remainingOffQuota = max(0, $item->off_quota - $item->off_days);
                            @endphp

                            <span>
                                {{ $remainingOffQuota }}
                            </span>
                        </td>
                        <td>
                            @if($item->excess_off_days > 0)
                                <x-ui.badge variant="danger">{{ $item->excess_off_days }}</x-ui.badge>
                            @else
                                0
                            @endif
                        </td>

                        <td>Rp{{ number_format($item->monthly_salary, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($item->prorate_salary, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($item->transport_allowance, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($item->meal_allowance, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($item->main_deduction, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($item->cash_advance, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($item->attendance_deduction, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($item->total_salary, 0, ',', '.') }}</td>
                        <td class="fw-semibold">Rp{{ number_format($item->rounded_salary, 0, ',', '.') }}</td>
                        <td>{{ $item->notes ?? '-' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <x-ui.button href="{{ route('payrolls.slip', [$payroll->id, $item->id]) }}"
                                   target="_blank"
                                   variant="danger-outline"
                                   size="sm"
                                   title="Download Slip Gaji PDF">
                                    <iconify-icon icon="mdi:file-pdf-box"></iconify-icon>
                                </x-ui.button>
                                @if($item->employee?->phone_number)
                                @php
                                    $waMsg = urlencode("Halo {$item->employee_name}, berikut slip gaji Anda untuk periode " . \Carbon\Carbon::parse($payroll->month . '-01')->isoFormat('MMMM Y') . ". Mohon dikonfirmasi penerimaan.");
                                    $waLink = "https://wa.me/" . preg_replace('/[^0-9]/', '', $item->employee->phone_number) . "?text={$waMsg}";
                                @endphp
                                <x-ui.button href="{{ $waLink }}" target="_blank"
                                   variant="outline"
                                   size="sm"
                                   title="Kirim via WhatsApp">
                                    <iconify-icon icon="mdi:whatsapp"></iconify-icon>
                                </x-ui.button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="18" class="py-5"><x-ui.empty-state title="Tidak ada item payroll." /></td>
                    </tr>
                @endforelse
            </tbody>

            @if($payroll->items->count())
                <tfoot>
                    <tr>
                        <th colspan="7" class="text-end">Total</th>
                        <th>Rp{{ number_format($totalGapok, 0, ',', '.') }}</th>
                        <th>Rp{{ number_format($totalProrate, 0, ',', '.') }}</th>
                        <th>Rp{{ number_format($totalMotor, 0, ',', '.') }}</th>
                        <th>Rp{{ number_format($totalMakan, 0, ',', '.') }}</th>
                        <th>Rp{{ number_format($totalPotonganRokok, 0, ',', '.') }}</th>
                        <th>Rp{{ number_format($totalKasbon, 0, ',', '.') }}</th>
                        <th>Rp{{ number_format($totalPotonganAbsen, 0, ',', '.') }}</th>
                        <th>Rp{{ number_format($totalGaji, 0, ',', '.') }}</th>
                        <th>Rp{{ number_format($totalBulat, 0, ',', '.') }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

</x-ui.card>

@endsection
