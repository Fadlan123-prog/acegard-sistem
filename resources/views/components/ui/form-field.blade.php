<div {{ $attributes->merge(['class' => 'ui-field']) }}>

    @if($label)
        <label
            class="ui-label"
            @if($name) for="{{ $name }}" @endif
        >
            {{ $label }}
            @if($required)
                <span class="ui-label-req" aria-label="wajib diisi">*</span>
            @elseif($optional)
                <span class="ui-label-opt">(opsional)</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if($error)
        <span class="ui-error" role="alert">{{ $error }}</span>
    @elseif($help)
        <span class="ui-help">{{ $help }}</span>
    @endif

</div>
