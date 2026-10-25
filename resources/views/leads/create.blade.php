@extends('dashboard.index')

@section('title', 'Tambah Lead')
@section('route-breadcrumb', 'leads.index')
@section('breadcrumb', 'Tambah Lead')

@section('content')
<form action="{{ route('leads.store') }}" method="POST">
    @csrf
    @include('leads._form')
</form>
@endsection
