<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;

class Card extends Component
{
    public function __construct(
        public bool    $loading  = false,
        public ?string $title    = null,
        public ?string $subtitle = null,
    ) {}

    public function render()
    {
        return view('components.ui.card');
    }
}
