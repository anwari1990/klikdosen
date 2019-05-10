<?php

namespace App\Providers;

use Mild\Supports\Facades\Route;
use Mild\Routing\RouterServiceProvider;

class RouteServiceProvider extends RouterServiceProvider
{
    /**
     * Set a namespace on the router handler if the callback is a controller
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * @throws \ReflectionException
     */
    public function boot()
    {
        if (file_exists($cached = $this->app->getRouteCachePath())) {
            Route::setRouteStack(require $cached);
        } else {
            $this->api();
            $this->web();
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function api()
    {
        Route::namespace($this->namespace)
             ->prefix('api')
             ->group(path('routes/api.php'));
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function web()
    {
        Route::namespace($this->namespace)
             ->middleware('web')
             ->group(path('routes/web.php'));
    }
}

