<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;

class Button extends Component
{
    public string $tag;
    public string $classes;

    public function __construct(
        public string  $variant  = 'primary',
        public string  $size     = 'md',
        public bool    $loading  = false,
        public bool    $disabled = false,
        public ?string $href     = null,
        public string  $type     = 'button',
        public ?string $icon     = null,
    ) {
        $this->tag = $href ? 'a' : 'button';

        $base = 'ui-btn';

        $variantClass = match ($variant) {
            'secondary'     => 'ui-btn-secondary',
            'danger'        => 'ui-btn-danger',
            'outline'       => 'ui-btn-outline',
            'ghost'         => 'ui-btn-ghost',
            'success'       => 'ui-btn-success',
            'warning'       => 'ui-btn-warning',
            'danger-outline'=> 'ui-btn-danger-outline',
            default         => 'ui-btn-primary',
        };

        $sizeClass = match ($size) {
            'sm' => 'ui-btn-sm',
            'lg' => 'ui-btn-lg',
            default => '',
        };

        $this->classes = trim(implode(' ', array_filter([
            $base,
            $variantClass,
            $sizeClass,
            $loading ? 'ui-btn-loading' : '',
        ])));
    }

    public function render()
    {
        return view('components.ui.button');
    }
}
