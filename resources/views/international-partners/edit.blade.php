@extends('dashboard.index')

@section('title', 'Edit Partner Internasional')
@section('route-breadcrumb', 'international-partners.edit')
@section('breadcrumb', 'Edit Partner')

@section('content')
<x-ui.card>
    <form method="POST" action="{{ route('international-partners.update', $partner) }}">
        @include('international-partners._form')
    </form>
</x-ui.card>
@endsection
