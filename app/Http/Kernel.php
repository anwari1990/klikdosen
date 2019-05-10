<?php

namespace App\Http;

use Mild\Http\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    /**
     * Register global middleware stack on the application
     * 
     * @var array
     */
    protected $middleware = [
        Middleware\CookieHeaderMiddleware::class,
        \Mild\Http\Middleware\StartSessionMiddleware::class,
        \Mild\Http\Middleware\ShareViewErrorsMiddleware::class,
        \Mild\Http\Middleware\ValidatePostSizeMiddleware::class
    ];
    /**
     * Set aliases on calling middleware
     *
     * @var array
     */
    protected $middlewareAliases = [
        'web' => Middleware\WebMiddleware::class,
        'auth' => Middleware\AuthMiddleware::class,
        'auth.admin' => Middleware\AuthAdminMiddleware::class,
        'authenticate' => Middleware\AuthenticateMiddleware::class
    ];
}
