<div {{ $attributes->merge(['class' => 'ui-card']) }}>

    @if(isset($header) || $title)
        <div class="ui-card-header">
            @if($title)
                <div>
                    <div class="ui-card-title">{{ $title }}</div>
                    @if($subtitle)
                        <div class="ui-card-subtitle">{{ $subtitle }}</div>
                    @endif
                </div>
            @else
                {{ $header }}
            @endif
        </div>
    @endif

    <div class="ui-card-body">
        @if($loading)
            <div class="sk sk-title"></div>
            <div class="sk sk-text w-full"></div>
            <div class="sk sk-text w-3q"></div>
            <div class="sk sk-text w-h"></div>
        @else
            {{ $slot }}
        @endif
    </div>

    @if(isset($footer) && !$loading)
        <div class="ui-card-footer">
            {{ $footer }}
        </div>
    @endif

</div>
