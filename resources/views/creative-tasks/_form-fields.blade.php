        <x-ui.form-field label="Judul Tugas" name="title" :required="true" :error="$errors->first('title')">
            <input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" class="form-control @error('title') is-invalid @enderror" required>
        </x-ui.form-field>

        <x-ui.form-field label="Ditugaskan Ke" name="assigned_to" :required="true" :error="$errors->first('assigned_to')">
            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" required>
                <option value="">— Pilih Designer / Videographer —</option>
                @foreach($assignees as $assignee)
                    <option value="{{ $assignee->id }}" @selected(old('assigned_to', $task->assigned_to ?? null) == $assignee->id)>
                        {{ $assignee->name }} ({{ $assignee->getRoleNames()->first() }})
                    </option>
                @endforeach
            </select>
        </x-ui.form-field>

        <x-ui.form-field label="Brief" name="brief" :required="true" :error="$errors->first('brief')">
            <textarea name="brief" rows="5" class="form-control @error('brief') is-invalid @enderror" required>{{ old('brief', $task->brief ?? '') }}</textarea>
        </x-ui.form-field>

        <x-ui.form-field label="Kebutuhan Dari Departemen (opsional)" name="department_context" :error="$errors->first('department_context')">
            <input type="text" name="department_context" value="{{ old('department_context', $task->department_context ?? '') }}" class="form-control" placeholder="mis. Marketing, Export & Import, dsb.">
        </x-ui.form-field>

        <div class="row">
            <div class="col-md-6">
                <x-ui.form-field label="Prioritas" name="priority" :required="true" :error="$errors->first('priority')">
                    <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                        <option value="low" @selected(old('priority', $task->priority ?? 'normal') == 'low')>Rendah</option>
                        <option value="normal" @selected(old('priority', $task->priority ?? 'normal') == 'normal')>Normal</option>
                        <option value="high" @selected(old('priority', $task->priority ?? 'normal') == 'high')>Tinggi</option>
                    </select>
                </x-ui.form-field>
            </div>
            <div class="col-md-6">
                <x-ui.form-field label="Deadline (opsional)" name="due_date" :error="$errors->first('due_date')">
                    <input type="date" name="due_date" value="{{ old('due_date', isset($task) ? $task->due_date?->format('Y-m-d') : '') }}" class="form-control">
                </x-ui.form-field>
            </div>
        </div>

