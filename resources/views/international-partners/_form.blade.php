@csrf
@isset($partner) @method('PUT') @endisset

<x-ui.form-field label="Tipe" name="type" :required="true" :error="$errors->first('type')">
    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
        <option value="supplier" @selected(old('type', $partner->type ?? '') == 'supplier')>Supplier (Import)</option>
        <option value="buyer" @selected(old('type', $partner->type ?? '') == 'buyer')>Buyer (Export)</option>
    </select>
</x-ui.form-field>

<x-ui.form-field label="Nama Perusahaan" name="name" :required="true" :error="$errors->first('name')">
    <input type="text" name="name" value="{{ old('name', $partner->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
</x-ui.form-field>

<div class="row">
    <div class="col-md-6">
        <x-ui.form-field label="Negara" name="country" :required="true" :error="$errors->first('country')">
            <input type="text" name="country" value="{{ old('country', $partner->country ?? '') }}" class="form-control @error('country') is-invalid @enderror" required>
        </x-ui.form-field>
    </div>
    <div class="col-md-6">
        <x-ui.form-field label="Mata Uang Default" name="default_currency" :required="true" :error="$errors->first('default_currency')">
            <input type="text" name="default_currency" maxlength="3" value="{{ old('default_currency', $partner->default_currency ?? 'USD') }}"
                   class="form-control text-uppercase @error('default_currency') is-invalid @enderror" placeholder="USD" required>
        </x-ui.form-field>
    </div>
</div>

<x-ui.form-field label="Contact Person" name="contact_person" :error="$errors->first('contact_person')">
    <input type="text" name="contact_person" value="{{ old('contact_person', $partner->contact_person ?? '') }}" class="form-control">
</x-ui.form-field>

<div class="row">
    <div class="col-md-6">
        <x-ui.form-field label="Telepon" name="phone" :error="$errors->first('phone')">
            <input type="text" name="phone" value="{{ old('phone', $partner->phone ?? '') }}" class="form-control">
        </x-ui.form-field>
    </div>
    <div class="col-md-6">
        <x-ui.form-field label="Email" name="email" :error="$errors->first('email')">
            <input type="email" name="email" value="{{ old('email', $partner->email ?? '') }}" class="form-control">
        </x-ui.form-field>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <x-ui.button type="submit" variant="primary">Simpan</x-ui.button>
    <x-ui.button :href="route('international-partners.index')" variant="secondary">Batal</x-ui.button>
</div>
