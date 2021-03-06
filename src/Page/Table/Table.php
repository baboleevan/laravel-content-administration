<?php

namespace Fjord\Page\Table;

use Closure;
use Fjord\Contracts\Page\Table as TableContract;
use Fjord\Exceptions\Traceable\MissingAttributeException;
use Fjord\Support\HasAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Table extends BaseTable implements TableContract
{
    use HasAttributes;

    /**
     * Table model class.
     *
     * @var string
     */
    protected $model;

    /**
     * Query modifier for eager loads and selections.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $queryModifier;

    /**
     * Vue component name.
     *
     * @var string
     */
    protected $componentName = 'fj-page-table';

    /**
     * Vue component instance.
     *
     * @var \Fjord\Vue\Component
     */
    protected $component;

    /**
     * Create new Table instance.
     *
     * @param string        $routePrefix
     * @param ColumnBuilder $builder
     */
    public function __construct($routePrefix, ColumnBuilder $builder)
    {
        $this->routePrefix($routePrefix);
        $this->component = component($this->componentName)
            ->prop('table', $this);

        parent::__construct($builder);
    }

    /**
     * Set defaults.
     *
     * @return void
     */
    public function setDefaults()
    {
        $this->setAttribute('controls', collect([]));
        $this->sortByDefault('id.desc');
        $this->perPage(10);
        $this->search(['title']);
        $this->sortBy([
            'id.desc' => __f('fj.sort_new_to_old'),
            'id.asc'  => __f('fj.sort_old_to_new'),
        ]);
    }

    /**
     * Set table route prefix.
     *
     * @param  string $routePrefix
     * @return $this
     */
    public function routePrefix(string $routePrefix)
    {
        $this->setAttribute('route_prefix', $routePrefix);

        return $this;
    }

    /**
     * Set table model.
     *
     * @param  string $model
     * @return $this
     */
    public function model(string $model)
    {
        $this->model = $model;

        if (! $this->hasAttribute('singularName')) {
            $this->singularName(class_basename($model));
        }

        if (! $this->hasAttribute('pluralName')) {
            $this->pluralName(Str::plural(class_basename($model)));
        }

        return $this;
    }

    /**
     * Get component instance.
     *
     * @return void
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Set query modifier.
     *
     * @param  Closure $closure
     * @return $this
     */
    public function query(Closure $closure)
    {
        $this->queryModifier = $closure;

        return $this;
    }

    /**
     * Get modified query.
     *
     * @param  Builder $query
     * @return Builder
     */
    public function getQuery(Builder $query)
    {
        if (! $this->queryModifier) {
            return $query;
        }

        $modifier = $this->queryModifier;
        $modifier($query);

        return $query;
    }

    /**
     * Render CrudIndexTable for Vue.
     *
     * @return array
     */
    public function render(): array
    {
        if (! $this->model) {
            throw new MissingAttributeException('Missing attribute [model] for '.static::class);
        }

        return parent::render();
    }
}
