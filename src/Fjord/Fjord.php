<?php

namespace Fjord\Fjord;

use Fjord\Application\Application;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Traits\ForwardsCalls;

class Fjord
{
    use ForwardsCalls;

    /**
     * Fjord Application.
     *
     * @var \Fjord\Application\Application
     */
    protected $app;

    /**
     * Laravel application instance.
     *
     * @var LaravelApplication
     */
    protected $laravel;

    /**
     * Create Fjord application.
     *
     * @param LaravelApplication $laravel
     */
    public function __construct(LaravelApplication $laravel)
    {
        $this->laravel = $laravel;
    }

    /**
     * Determines if the application is translatable.
     *
     * @return bool
     */
    public function isAppTranslatable()
    {
        return count(config('translatable.locales')) > 1;
    }

    /**
     * Bind Fjord Application instance when Fjord is installed.
     *
     * @param  \Fjord\Application\Application $app
     * @return void
     */
    public function bindApp(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get Fjord application.
     *
     * @return \Fjord\Application\Application $app
     */
    public function app()
    {
        return $this->app;
    }

    /**
     * Get Fjord url.
     *
     * @param  string $url
     * @return string
     */
    public function url(string $url)
    {
        return strip_slashes(
            '/'.config('fjord.route_prefix').'/'.$url
        );
    }

    /**
     * Get Fjord route by name.
     *
     * @param string $name
     *
     * @return string
     */
    public function route(string $name)
    {
        return route("fjord.{$name}");
    }

    /**
     * Get translation for Fjord application.
     *
     * @param  string $key
     * @param  array  $replace
     * @return string
     */
    public function trans(string $key = null, $replace = [])
    {
        if (is_null($key)) {
            return $key;
        }

        return $this->laravel['fjord.translator']->trans($key, $replace);
    }

    /**
     * Get choice translation for Fjord application.
     *
     * @param  string $key
     * @param  array  $replace
     * @return string
     */
    public function trans_choice(string $key = null, $number, $replace = [])
    {
        if (is_null($key)) {
            return $key;
        }

        return $this->laravel['fjord.translator']->choice($key, $number, $replace);
    }

    /**
     * Get translation for Fjord application.
     *
     * @param  string $key
     * @param  array  $replace
     * @return string
     */
    public function __(string $key = null, $replace = [])
    {
        return $this->trans($key, $replace);
    }

    /**
     * Load config.
     *
     * @param  string             $key
     * @param  array              $params
     * @return ConfigHandler|null
     */
    public function config($key, ...$params)
    {
        return $this->app->get('config.loader')->get($key, ...$params);
    }

    /**
     * Get authenticated Fjord user.
     *
     * @return \Fjord\User\Models\FjordUser $user
     */
    public function user()
    {
        return fjord_user();
    }

    /**
     * Get locale for Fjord application.
     *
     * @return void
     */
    public function getLocale()
    {
        return $this->laravel['fjord.translator']->getLocale();
    }

    /**
     * Checks if fjord has been installed.
     *
     * @return bool
     */
    public function installed()
    {
        if (! config()->has('fjord')) {
            return false;
        }

        if (! class_exists(\FjordApp\Kernel::class)) {
            return false;
        }

        if (! File::exists(base_path('bootstrap/cache/fjord.php'))) {
            return false;
        }

        return true;
    }

    /**
     * Determines wether composer dumpautoload needs to be called.
     *
     * @return void
     */
    public function needsDumpAutoload()
    {
        if ($this->installed()) {
            return false;
        }

        if (! class_exists(\FjordApp\Kernel::class)) {
            return false;
        }

        return ! File::exists(base_path('bootstrap/cache/fjord.php'));
    }

    /**
     * Forward call to Fjord Application.
     *
     * @param  string $method
     * @param  array  $params
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->app, $method, $parameters);
    }
}
