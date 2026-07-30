@if ($errors->any())
    <x-ui.alert type="danger" class="mb-3">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </x-ui.alert>
@endif

<x-ui.form-field label="Cabang" name="branch_id">
    <select name="branch_id" class="form-select">
        <option value="">-- Semua Cabang --</option>
        @foreach($branches as $branch)
            <option value="{{ $branch->id }}" {{ old('branch_id', $shift->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                {{ $branch->name }}
            </option>
        @endforeach
    </select>
    <div class="form-text">Kosongkan jika berlaku untuk semua cabang.</div>
</x-ui.form-field>

<x-ui.form-field label="Nama Shift" name="name">
    <input type="text" name="name" class="form-control" value="{{ old('name', $shift->name ?? '') }}" required>
</x-ui.form-field>

<div class="row">
    <div class="col-md-6">
        <x-ui.form-field label="Jam Mulai" name="start_time">
            <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $shift->start_time ?? '') }}" required>
        </x-ui.form-field>
    </div>
    <div class="col-md-6">
        <x-ui.form-field label="Jam Selesai" name="end_time">
            <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $shift->end_time ?? '') }}" required>
        </x-ui.form-field>
    </div>
</div>

<x-ui.form-field label="Lembur Setelah (menit)" name="overtime_after_minutes">
    <input type="number" name="overtime_after_minutes" class="form-control" min="0" max="300"
           value="{{ old('overtime_after_minutes', $shift->overtime_after_minutes ?? 30) }}" required>
    <div class="form-text">Berapa menit setelah jam selesai baru dihitung lembur.</div>
</x-ui.form-field>

<div class="mb-3 form-check">
    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1"
           {{ old('is_active', $shift->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">Aktif</label>
</div>
