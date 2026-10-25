@extends('dashboard.index')

@section('title', 'Tambah Partner Internasional')
@section('route-breadcrumb', 'international-partners.create')
@section('breadcrumb', 'Tambah Partner')

@section('content')
<x-ui.card>
    <form method="POST" action="{{ route('international-partners.store') }}">
        @include('international-partners._form')
    </form>
</x-ui.card>
@endsection
