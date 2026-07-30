@extends('dashboard.index')

@section('title', 'Edit Quotation')
@section('route-breadcrumb', 'quotations.index')
@section('breadcrumb', 'Edit Quotation')

@section('content')
<form action="{{ route('quotations.update', $quotation) }}" method="POST">
    @csrf @method('PUT')
    @include('quotations._form')
</form>
@endsection
