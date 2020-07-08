<?php

namespace Fjord\Page\Table;

use ErrorException;
use Fjord\Exceptions\MissingAttributeException;
use Fjord\Exceptions\Traceable\InvalidArgumentException;
use Fjord\Support\HasAttributes;
use Fjord\Support\VueProp;

class Column extends VueProp
{
    use HasAttributes;

    /**
     * Create new Col instance.
     *
     * @param  string $label
     * @return void
     */
    public function __construct($label = null)
    {
        if ($label) {
            $this->label($label);
            $this->value($label);
        }
    }

    /**
     * Set label.
     *
     * @param  string $label
     * @return $this
     */
    public function label(string $label)
    {
        $this->setAttribute('label', $label);

        return $this;
    }

    /**
     * Set value.
     *
     * @param  string $value
     * @return $this
     */
    public function value($value)
    {
        $this->setAttribute('value', $value);

        return $this;
    }

    /**
     * Reduce column to minimum width.
     *
     * @param  bool  $small
     * @return $this
     */
    public function small(bool $small = true)
    {
        $this->setAttribute('small', $small);

        return $this;
    }

    /**
     * Set column link.
     *
     * @param  string|bool $link
     * @return $this
     */
    public function link($link)
    {
        $this->setAttribute('link', $link);

        return $this;
    }

    /**
     * Set attribute by which the column should be sorted.
     *
     * @param  string $attribute
     * @return $this
     */
    public function sortBy($attribute)
    {
        $this->setAttribute('sort_by', $attribute);

        return $this;
    }

    /**
     * Set regular expression and replace for column value.
     *
     * @param  string $regex
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function regex($regex, string $replace = '')
    {
        $this->setAttribute('regex', $regex);
        $this->setAttribute('regex_replace', $replace);

        // Test regular expression:
        try {
            preg_match($regex, '');
        } catch (ErrorException $e) {
            throw new InvalidArgumentException($e->getMessage(), [
                'function' => 'regex',
                'class'    => self::class,
            ]);
        }

        return $this;
    }

    /**
     * Strip html from column value.
     *
     * @param  bool  $strip
     * @return $this
     */
    public function stripHtml(bool $strip = true)
    {
        $this->setAttribute('strip_html', $strip);

        return $this;
    }

    /**
     * Set a maxium of characters that should be displayed in the column.
     *
     * @param  int   $max
     * @return $this
     */
    public function maxChars(int $max = 100)
    {
        $this->attributes['max_chars'] = $max;

        return $this;
    }

    /**
     * Check if all required props have been set.
     *
     * @return void
     *
     * @throws MissingAttributeException
     */
    public function checkComplete()
    {
        if (array_key_exists('label', $this->attributes)) {
            return;
        }

        throw new MissingAttributeException(sprintf(
            'Missing required attributes [%s] for table column.',
            implode(', ', ['label']),
        ));
    }

    /**
     * Get attributes.
     *
     * @return array
     */
    public function render(): array
    {
        $this->checkComplete();

        return $this->attributes;
    }
}