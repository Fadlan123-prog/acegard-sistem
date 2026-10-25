@props([
    'variant' => 'default', // default | success | warning
    'icon' => null,
])

@php
    $variantClass = match ($variant) {
        'success' => 'ace-pill-success',
        'warning' => 'ace-pill-warning',
        default => '',
    };
@endphp

<span {{ $attributes->class(['ace-pill', $variantClass]) }}>
    @if($icon)<iconify-icon icon="{{ $icon }}"></iconify-icon>@endif
    {{ $slot }}
</span>
