@extends('layout.index')

@section('title', $title)

@section('route-breadcrumb', 'branches.index')
@section('breadcrumb', 'Branches')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold mb-4">Tambah Cabang</h4>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('branches.store') }}" method="POST">
    @csrf

    <div class="mb-3">
      <label class="form-label">Nama Cabang</label>
      <input type="text" name="name" class="form-control"
             value="{{ old('name') }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Kota</label>
      <input type="text" name="city" class="form-control"
             value="{{ old('city') }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Alamat</label>
      <input type="text" name="address" class="form-control"
             value="{{ old('address') }}" required>
    </div>

    <button type="submit" class="btn btn-primary">
      Simpan &amp; Lanjut Tambah User
    </button>
  </form>
</div>
@endsection
