<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="ui-badge-dot" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
