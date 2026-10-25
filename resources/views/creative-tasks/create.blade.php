@extends('dashboard.index')

@section('title', 'Buat Tugas Baru')
@section('route-breadcrumb', 'creative-tasks.create')
@section('breadcrumb', 'Buat Tugas Baru')

@section('content')

<x-ui.card>
    <form method="POST" action="{{ route('creative-tasks.store') }}">
        @csrf

        <x-ui.form-field label="Judul Tugas" name="title" :required="true" :error="$errors->first('title')">
            <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" required>
        </x-ui.form-field>

        <x-ui.form-field label="Ditugaskan Ke" name="assigned_to" :required="true" :error="$errors->first('assigned_to')">
            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" required>
                <option value="">— Pilih Designer / Videographer —</option>
                @foreach($assignees as $assignee)
                    <option value="{{ $assignee->id }}" @selected(old('assigned_to') == $assignee->id)>
                        {{ $assignee->name }} ({{ $assignee->getRoleNames()->first() }})
                    </option>
                @endforeach
            </select>
        </x-ui.form-field>

        <x-ui.form-field label="Brief" name="brief" :required="true" :error="$errors->first('brief')">
            <textarea name="brief" rows="5" class="form-control @error('brief') is-invalid @enderror" required>{{ old('brief') }}</textarea>
        </x-ui.form-field>

        <x-ui.form-field label="Kebutuhan Dari Departemen (opsional)" name="department_context" :error="$errors->first('department_context')">
            <input type="text" name="department_context" value="{{ old('department_context') }}" class="form-control" placeholder="mis. Marketing, Export & Import, dsb.">
        </x-ui.form-field>

        <div class="row">
            <div class="col-md-6">
                <x-ui.form-field label="Prioritas" name="priority" :required="true" :error="$errors->first('priority')">
                    <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                        <option value="low" @selected(old('priority') == 'low')>Rendah</option>
                        <option value="normal" @selected(old('priority', 'normal') == 'normal')>Normal</option>
                        <option value="high" @selected(old('priority') == 'high')>Tinggi</option>
                    </select>
                </x-ui.form-field>
            </div>
            <div class="col-md-6">
                <x-ui.form-field label="Deadline (opsional)" name="due_date" :error="$errors->first('due_date')">
                    <input type="date" name="due_date" value="{{ old('due_date') }}" class="form-control">
                </x-ui.form-field>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <x-ui.button type="submit" variant="primary">Buat & Tugaskan</x-ui.button>
            <x-ui.button :href="route('creative-tasks.index')" variant="secondary">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
