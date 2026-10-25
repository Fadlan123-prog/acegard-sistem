@extends('dashboard.index')

@section('title', 'Edit Payroll')
@section('route-breadcrumb', 'payrolls.index')
@section('breadcrumb', 'Edit Payroll')

@section('content')

<x-ui.card>

    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Edit Payroll</div>
                <small class="text-muted">
                    {{ $payroll->branch->name ?? '-' }} -
                    {{ \Carbon\Carbon::parse($payroll->month . '-01')->format('M Y') }}
                </small>
            </div>
            <x-ui.button :href="route('payrolls.show', $payroll->id)" variant="secondary" size="sm">
                Kembali
            </x-ui.button>
        </div>
    @endslot

    @if ($errors->any())
        <x-ui.alert type="danger" class="mb-3">
            <strong>Terjadi kesalahan.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    

    <form method="POST" action="{{ route('payrolls.update', $payroll->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Catatan Payroll</label>
            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $payroll->notes) }}</textarea>
        </div>

        <x-ui.alert type="info" class="mb-3">
            Kolom Gapok, Prorate, dan data absensi berasal dari hasil generate.
            Kolom yang bisa diedit: Uang Motor, Uang Makan, Potongan Rokok, Kasbon, Potongan Absen, Potongan Lain, dan Catatan.
        </x-ui.alert>

        <div class="table-responsive" style="max-height: 680px; overflow-y: auto; overflow-x: auto;">
            <table class="table bordered-table align-middle mb-0" style="min-width: 2100px; white-space: nowrap;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Masuk</th>
                        <th>Libur</th>
                        <th>Jatah</th>
                        <th>Gapok</th>
                        <th>Prorate</th>
                        <th>Uang Motor</th>
                        <th>Uang Makan</th>
                        <th>Potongan Rokok</th>
                        <th>Kasbon</th>
                        <th>Potongan Absen</th>
                        <th>Potongan Lain</th>
                        <th>Catatan</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($payroll->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td class="fw-semibold">
                                {{ $item->employee_name }}
                                <input type="hidden"
                                       name="items[{{ $loop->index }}][id]"
                                       value="{{ $item->id }}">
                            </td>

                            <td>{{ $item->job_position ?? '-' }}</td>
                            <td>{{ $item->present_days }}</td>
                            <td>{{ $item->off_days }}</td>
                            <td>{{ $item->off_quota }}</td>

                            <td>
                                Rp{{ number_format($item->monthly_salary, 0, ',', '.') }}
                            </td>

                            <td>
                                Rp{{ number_format($item->prorate_salary, 0, ',', '.') }}
                            </td>

                            <td>
                                <input type="number"
                                       name="items[{{ $loop->index }}][transport_allowance]"
                                       class="form-control form-control-sm"
                                       value="{{ old("items.$loop->index.transport_allowance", $item->transport_allowance) }}"
                                       min="0">
                            </td>

                            <td>
                                <input type="number"
                                       name="items[{{ $loop->index }}][meal_allowance]"
                                       class="form-control form-control-sm"
                                       value="{{ old("items.$loop->index.meal_allowance", $item->meal_allowance) }}"
                                       min="0">
                            </td>

                            <td>
                                <input type="number"
                                       name="items[{{ $loop->index }}][main_deduction]"
                                       class="form-control form-control-sm"
                                       value="{{ old("items.$loop->index.main_deduction", $item->main_deduction) }}"
                                       min="0">
                            </td>

                            <td>
                                <input type="number"
                                       name="items[{{ $loop->index }}][cash_advance]"
                                       class="form-control form-control-sm"
                                       value="{{ old("items.$loop->index.cash_advance", $item->cash_advance) }}"
                                       min="0">
                            </td>

                            <td>
                                <input type="number"
                                       name="items[{{ $loop->index }}][attendance_deduction]"
                                       class="form-control form-control-sm"
                                       value="{{ old("items.$loop->index.attendance_deduction", $item->attendance_deduction) }}"
                                       min="0">
                            </td>

                            <td>
                                <input type="number"
                                       name="items[{{ $loop->index }}][other_deduction]"
                                       class="form-control form-control-sm"
                                       value="{{ old("items.$loop->index.other_deduction", $item->other_deduction) }}"
                                       min="0">
                            </td>

                            <td>
                                <input type="text"
                                       name="items[{{ $loop->index }}][notes]"
                                       class="form-control form-control-sm"
                                       value="{{ old("items.$loop->index.notes", $item->notes) }}"
                                       placeholder="Catatan">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-2 mt-4">
            <x-ui.button type="submit" variant="primary"
                onclick="return confirm('Update payroll ini?')">
                Simpan Perubahan
            </x-ui.button>

            <x-ui.button :href="route('payrolls.show', $payroll->id)" variant="outline">
                Batal
            </x-ui.button>
        </div>

    </form>

</x-ui.card>

@endsection
