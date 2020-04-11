<?php

namespace Fjord\Form;

use Form;
use Closure;
use Illuminate\Support\Str;
use Fjord\Form\Database\FormField;
use Illuminate\Support\Collection;
use Fjord\Support\Facades\FormLoader;
use Illuminate\Support\Facades\Request;
use Fjord\Form\Requests\CrudUpdateRequest;
use Fjord\Form\Requests\FormUpdateRequest;
use Fjord\Form\Requests\Traits\CrudHasPermission;
use Fjord\Form\Requests\Traits\FormHasPermission;
use Fjord\Form\Requests\Traits\AuthorizeController;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CrudForm
{
    use AuthorizeController;

    const DEFAULTS = [
        'form_fields' => [],
        'layout' => [],
        'preview_route' => null,
        'names' => [],
        'back_route' => null,
        'back_text' => null,
        'index' => [],
        'sort_by' => null,
        'readonly' => false,
    ];

    protected $path;

    protected $model;

    protected $modelInstance;

    protected $originals = [];

    protected $attributes = [];

    protected $form_fields;

    public function __construct($path, $model)
    {
        $attributes = require $path;
        $this->originals = $attributes;
        $this->attributes = $attributes;
        $this->path = $path;

        $this->model = $this->getModelClassName($model);

        $this->modelInstance = with(new $this->model);

        $this->setDefaults();
    }

    protected function getModelClassName($model)
    {
        if (is_string($model)) {
            return $model;
        }

        if (get_class($model) == EloquentCollection::class) {
            return get_class($model->first());
        }

        return get_class($model);
    }

    protected function setDefaults()
    {
        foreach (self::DEFAULTS as $key => $default) {
            if (!array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $default;
            }
        }

        $this->setModel();
        $this->setLayout();
        $this->setFormFields();
        $this->setNames();
        $this->setBackRoute();
        $this->setIndex();
        $this->setPermissions();
        $this->setRoute();
    }

    public function setRoute()
    {
        $route = str_replace(config('fjord.route_prefix') . "/", "", Request::route()->uri());
        if ($this->attributes['model'] != FormField::class) {
            $this->attributes['route'] = explode('/', $route)[0];
        } else {
            $this->attributes['route'] = $route;
        }
    }

    public function setPermissions()
    {
        $permissions = [
            'create' => false,
            'read' => false,
            'update' => false,
            'delete' => false,
        ];

        $request = app()->get('request');

        $permissions['read'] = $this->authorizeController($request, 'read');
        $permissions['update'] = $this->authorizeController($request, 'update');
        if ($this->attributes['model'] != FormField::class) {
            $permissions['create'] = $this->authorizeController($request, 'create');
            $permissions['delete'] = $this->authorizeController($request, 'delete');
        }
        $this->attributes['permissions'] = $permissions;
    }

    protected function setIndex()
    {
        $index = $this->attributes['index'];

        $index['search'] = $this->getSearch($index);
        if ($this->attributes['model'] != FormField::class) {
            $index['preview'] = $this->getCols($index['preview']);
        }

        $this->attributes['index'] = $index;
    }

    public function getCols($cols)
    {
        foreach ($cols as $key => $col) {
            if (!array_key_exists('link', $col)) {
                $col['link'] = $this->modelInstance->getTable() . '/{id}/edit';
            }

            $cols[$key] = $col;
        }

        return $cols;
    }

    /**
     * Get keys that sould be searched for.
     *
     * @param array $index
     * @return array $keys
     */
    protected function getSearch($index)
    {
        if (!array_key_exists('search', $index)) {
            return $this->modelInstance->getFillable();
        }

        if (!is_array($index['search'])) {
            return $this->compileSearchKey($index['search']);
        }

        $keys = [];
        foreach ($index['search'] as $key) {
            $keys[] = $this->compileSearchKey($key);
        }

        return $keys;
    }

    protected function compileSearchKey($key)
    {
        if (!is_translatable($this->modelInstance)) {
            return $key;
        }

        if (in_array($key, $this->modelInstance->translatedAttributes)) {
            return 'translations.' . $key;
        } else {
            return $key;
        }
    }

    protected function setNames()
    {
        $names = [
            'title' => [
                'singular' => $this->attributes['names']['singular'] ?? $this->attributes['title'] ?? '',
                'plural' => $this->attributes['names']['plural'] ?? ''
            ]
        ];


        if ($names['title']['singular'] == '') {
            $singular = Str::singular(Str::snake($this->getName()));
            $words = explode('_', $singular);
            foreach ($words as $key => $word) {
                $names['title']['singular'] .= ucfirst($word);
            }
        }

        if ($names['title']['plural'] == '') {
            $plural = Str::plural($singular ?? $names['title']['singular']);
            $words = explode('_', $plural);
            foreach ($words as $key => $word) {
                $names['title']['plural'] .= ucfirst($word);
            }
        }

        $table = $this->modelInstance->getTable();
        $names['table'] = $table;

        $this->attributes['names'] = $names;
    }

    protected function getName()
    {
        return str_replace('.php', '', last(explode('/', $this->path)));
    }

    protected function setModel()
    {
        $this->attributes['model'] = $this->model;
    }

    protected function setFormFields()
    {
        $this->form_fields = FormLoader::getFields(
            $this->attributes['form_fields'],
            $this->modelInstance
        );

        unset($this->attributes['form_fields']);
    }

    protected function setLayout()
    {
        if (count($this->attributes['form_fields']) < 1) {
            return;
        }

        $formFields = [];

        foreach ($this->attributes['form_fields'] as $array) {
            if ($this->isArrayFormField($array)) {
                $formFields[] = $array;
                $this->attributes['layout'][] = $this->getFormLayoutIds([$array]);
            } else {
                $formFields = array_merge($array, $formFields);
                $this->attributes['layout'][] = $this->getFormLayoutIds($array);
            }
        }

        $this->attributes['form_fields'] = $formFields;
    }

    protected function setBackRoute()
    {
        if ($this->isFjordModel()) {
            return;
        }

        $this->attributes['back_route'] = $this->modelInstance->getTable();

        $this->setBackText();
    }

    protected function setBackText()
    {
        if ($this->attributes['back_text']) {
            return;
        }

        $this->attributes['back_text'] = $this->attributes['names']['title']['plural'];
    }

    public function setPreviewRoute($model)
    {
        $route = $this->attributes['preview_route'];

        if (is_callable($route) && !is_array($route)) {
            $route = call_user_func($route, $model);
        }

        if (is_array($route)) {

            $class = $route[0];
            $method = $route[1];

            $params = [];

            if ($class != $this->model) {
                $params = [$model];
            }

            if (method_exists($model, $method)) {
                $class = $model;

                $methodRef = new \ReflectionMethod(get_class($class), $method);
                if ($methodRef->isStatic()) {
                    $params = [$model];
                }
            }

            $route = call_user_func_array([$class, $method], $params);
        }

        $this->attributes['preview_route'] = $route;
    }

    protected function isFjordModel()
    {
        return $this->model == Database\FormField::class;
    }

    protected function getFormLayoutIds($formFields)
    {
        return collect($formFields)->pluck('id')->toArray();
    }

    protected function isArrayFormField($array)
    {
        return array_key_exists('type', $array) ? true : false;
    }

    public function getAttribute($key)
    {
        if ($key == 'form_fields') {
            return $this->form_fields;
        }

        return $this->attributes[$key] ?? null;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function toArray()
    {
        return $this->getAttributes();
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }
}
