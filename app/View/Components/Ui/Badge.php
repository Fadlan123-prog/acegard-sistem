<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;

class Badge extends Component
{
    public string $classes;

    public function __construct(
        public string $variant = 'neutral',
        public string $size    = 'default',
        public string $shape   = 'pill',
        public bool   $dot     = false,
    ) {
        $variantClass = match ($variant) {
            'success' => 'ui-badge-success',
            'warning' => 'ui-badge-warning',
            'danger'  => 'ui-badge-danger',
            'info'    => 'ui-badge-info',
            'primary' => 'ui-badge-primary',
            default   => 'ui-badge-neutral',
        };

        $sizeClass = match ($size) {
            'sm' => 'ui-badge-sm',
            'lg' => 'ui-badge-lg',
            default => '',
        };

        $shapeClass = $shape === 'square' ? 'ui-badge-square' : '';

        $this->classes = trim(implode(' ', array_filter([
            'ui-badge',
            $variantClass,
            $sizeClass,
            $shapeClass,
        ])));
    }

    public function render()
    {
        return view('components.ui.badge');
    }
}
