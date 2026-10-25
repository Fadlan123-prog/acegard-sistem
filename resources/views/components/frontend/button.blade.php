@props([
    'variant' => 'gradient', // gradient | outline
    'href' => null,
    'type' => null,
    'icon' => null,
])

@php
    $variantClass = $variant === 'outline' ? 'btn-ace-outline' : 'btn-ace-gradient';
    $tag = $type ? 'button' : 'a';
@endphp

<{{ $tag }}
    {{ $attributes->class([$variantClass]) }}
    @if($tag === 'a') href="{{ $href ?? '#' }}" @else type="{{ $type }}" @endif
>
    @if($icon)<iconify-icon icon="{{ $icon }}"></iconify-icon>@endif
    {{ $slot }}
</{{ $tag }}>
