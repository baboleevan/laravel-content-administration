<?php

namespace Fjord\Page;

use Fjord\Contracts\Page\ActionFactory;
use Fjord\Support\VueProp;
use Fjord\Vue\Component;
use Illuminate\Contracts\View\View;

class Slot extends VueProp
{
    /**
     * Component stack.
     *
     * @var array
     */
    protected $components = [];

    /**
     * View stack.
     *
     * @var array
     */
    protected $views = [];

    /**
     * Parent page.
     *
     * @var string
     */
    protected $page;

    /**
     * Action factory.
     *
     * @var string
     */
    protected $actionFactory;

    /**
     * Create new Slot instance.
     *
     * @param  BasePage      $page
     * @param  ActionFactory $actionFactory
     * @return void
     */
    public function __construct(BasePage $page, ActionFactory $actionFactory)
    {
        $this->page = $page;
        $this->actionFactory = $actionFactory;
    }

    /**
     * Add action.
     *
     * @param  string $title
     * @param  string $action
     * @return $this
     */
    public function action($title, $action)
    {
        $component = $this->component(
            $this->actionFactory->make($title, $action)
        );

        $wrapper = $component->getProp('wrapper');

        $this->page->resolveAction($component);

        return $wrapper;
    }

    /**
     * Add component to Slot.
     *
     * @return Component
     */
    public function component($component)
    {
        $component = component($component);

        $this->components[] = $component;

        return $component;
    }

    /**
     * Add Blade View to stack.
     *
     * @param  string|View $view
     * @return View
     */
    public function view($view)
    {
        if (! $view instanceof View) {
            $view = view($view);
        }

        $this->views[] = $view;

        $this->component('fj-blade')->prop('view', $view);

        return $view;
    }

    /**
     * Get views.
     *
     * @return void
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Render slot for Vue.
     *
     * @return array
     */
    public function render(): array
    {
        return [
            'components' => collect($this->components),
        ];
    }
}
