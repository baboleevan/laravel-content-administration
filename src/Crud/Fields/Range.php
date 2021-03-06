<?php

namespace Fjord\Crud\Fields;

use Fjord\Crud\BaseField;

class Range extends BaseField
{
    use Traits\FieldHasRules;

    /**
     * Field Vue component.
     *
     * @var string
     */
    protected $component = 'fj-field-range';

    /**
     * Required field attributes.
     *
     * @var array
     */
    public $required = ['min', 'max'];

    /**
     * Set default attributes.
     *
     * @return void
     */
    public function setDefaultAttributes()
    {
        $this->setAttribute('step', 1);
        $this->setAttribute('min', 1);
    }

    /**
     * Set step.
     *
     * @param int $step
     *
     * @return $this
     */
    public function step(int $step)
    {
        $this->setAttribute('step', $step);

        return $this;
    }

    /**
     * Set max.
     *
     * @param int $max
     *
     * @return $this
     */
    public function max(int $max)
    {
        $this->setAttribute('max', $max);

        return $this;
    }

    /**
     * Set min.
     *
     * @param int $min
     *
     * @return $this
     */
    public function min(int $min)
    {
        $this->setAttribute('min', $min);

        return $this;
    }

    /**
     * Cast field value.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function cast($value)
    {
        if (! is_numeric($value)) {
            return 0;
        }

        return is_float($value) ? (float) $value : (int) $value;
    }
}
