@if($href)
<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => $classes]) }}
    @if($disabled || $loading) aria-disabled="true" @endif
    @if($loading) aria-busy="true" @endif
    role="button"
>
@else
<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
    @if($disabled || $loading) disabled aria-disabled="true" @endif
    @if($loading) aria-busy="true" @endif
>
@endif

    @if($loading)
        <span class="btn-spinner" aria-hidden="true"></span>
    @endif

    @if($icon && !$loading)
        <span class="btn-icon" aria-hidden="true">{!! $icon !!}</span>
    @endif

    <span class="btn-text">{{ $slot }}</span>

@if($href)
</a>
@else
</button>
@endif
