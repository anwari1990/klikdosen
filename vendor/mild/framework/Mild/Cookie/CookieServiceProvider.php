<?php

namespace Mild\Cookie;

use Mild\Supports\ServiceProvider;

class CookieServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->set('cookie', function ($app) {
           return new CookieJar;
        });
    }
}
