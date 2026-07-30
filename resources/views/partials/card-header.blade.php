{{--
    Reusable card-header with title and optional action buttons.

    Props:
    - $title       : heading text (required)
    - $subtitle    : optional small text below title
    - $backRoute   : optional route name for "Kembali" button
    - $createRoute : optional route name for "+ Tambah" button
    - $createLabel : label for create button (default: '+ Tambah')
    - $slot        : optional extra buttons (use @include with $slot or put inline)
--}}

@php
    $subtitle    = $subtitle    ?? null;
    $backRoute   = $backRoute   ?? null;
    $createRoute = $createRoute ?? null;
    $createLabel = $createLabel ?? '+ Tambah';
@endphp

<div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h5 class="card-title mb-0">{{ $title }}</h5>
        @if ($subtitle)
            <small class="text-muted">{{ $subtitle }}</small>
        @endif
    </div>

    <div class="d-flex gap-2 align-items-center">
        {{ $slot ?? '' }}

        @if ($createRoute)
            <a href="{{ route($createRoute) }}" class="btn btn-primary btn-sm">
                {{ $createLabel }}
            </a>
        @endif

        @if ($backRoute)
            <a href="{{ route($backRoute) }}" class="btn btn-sm btn-secondary">
                Kembali
            </a>
        @endif
    </div>
</div>
