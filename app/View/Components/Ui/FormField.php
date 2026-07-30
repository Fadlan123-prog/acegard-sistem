<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;

class FormField extends Component
{
    public function __construct(
        public string  $name     = '',
        public string  $label    = '',
        public bool    $required = false,
        public ?string $help     = null,
        public ?string $error    = null,
        public bool    $optional = false,
    ) {}

    public function render()
    {
        return view('components.ui.form-field');
    }
}
