<?php

namespace App\Http\Middleware;

use Mild\Http\Middleware\CookieHeaderMiddleware as Middleware;

class CookieHeaderMiddleware extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [];
}