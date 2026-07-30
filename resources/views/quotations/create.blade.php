@extends('dashboard.index')

@section('title', 'Buat Quotation')
@section('route-breadcrumb', 'quotations.index')
@section('breadcrumb', 'Buat Quotation')

@section('content')
<form action="{{ route('quotations.store') }}" method="POST">
    @csrf
    @include('quotations._form')
</form>
@endsection
