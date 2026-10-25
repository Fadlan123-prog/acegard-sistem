@extends('dashboard.index')

@section('title', 'Ajukan Kasbon')
@section('route-breadcrumb', 'my-kasbon.create')
@section('breadcrumb', 'Ajukan Kasbon')

@section('content')

<x-ui.card>
    @if($employee->monthly_salary)
        <x-ui.alert type="info" class="mb-3">
            Gaji bulanan Anda saat ini: <strong>Rp {{ number_format($employee->monthly_salary, 0, ',', '.') }}</strong>.
            Potongan per cicilan tidak boleh lebih dari 50% dari nilai itu
            (maks. <strong>Rp {{ number_format(floor($employee->monthly_salary / 2), 0, ',', '.') }}</strong>/cicilan) —
            perbesar jumlah cicilan kalau nominal kasbon besar.
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('my-kasbon.store') }}">
        @csrf

        <x-ui.form-field label="Nominal Kasbon (Rp)" name="amount" :required="true" :error="$errors->first('amount')">
            <input type="number" name="amount" min="1" value="{{ old('amount') }}"
                   class="form-control @error('amount') is-invalid @enderror" required>
        </x-ui.form-field>

        <x-ui.form-field label="Jumlah Cicilan" name="installments" :required="true" :error="$errors->first('installments')">
            <input type="number" name="installments" min="1" max="24" value="{{ old('installments', 1) }}"
                   class="form-control @error('installments') is-invalid @enderror" required>
            <div class="form-text">Berapa kali potongan dibagi — maksimal 24 kali.</div>
        </x-ui.form-field>

        <x-ui.form-field label="Sumber Potongan" name="deduction_source" :required="true" :error="$errors->first('deduction_source')">
            <div class="d-flex flex-column gap-2">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="deduction_source" id="src_payroll" value="payroll"
                           {{ old('deduction_source') == 'payroll' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="src_payroll">Potong dari Gajian</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="deduction_source" id="src_commission" value="commission"
                           {{ old('deduction_source') == 'commission' ? 'checked' : '' }}>
                    <label class="form-check-label" for="src_commission">Potong dari Komisi</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="deduction_source" id="src_both" value="both"
                           {{ old('deduction_source') == 'both' ? 'checked' : '' }}>
                    <label class="form-check-label" for="src_both">Potong dari Gajian & Komisi</label>
                </div>
            </div>
        </x-ui.form-field>

        <x-ui.form-field label="Alasan" name="reason" :required="true" :error="$errors->first('reason')">
            <textarea name="reason" rows="3" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason') }}</textarea>
        </x-ui.form-field>

        <div class="d-flex gap-2 mt-3">
            <x-ui.button type="submit" variant="primary">Ajukan Kasbon</x-ui.button>
            <x-ui.button :href="route('my-kasbon.index')" variant="secondary">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
