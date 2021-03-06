<?php

namespace Fjord\Vue\Components;

use Fjord\Contracts\Vue\Resizable;
use Fjord\Vue\Component;
use Fjord\Vue\Traits\CanBeResized;

class FieldWrapperCardComponent extends Component implements Resizable
{
    use CanBeResized;

    public function beforeMount()
    {
        $this->class('mb-4');
        $this->width(12);
    }

    public function title($title)
    {
        return $this->prop('title', $title);
    }
}
