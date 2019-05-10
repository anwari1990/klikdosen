<?php

namespace App\Handlers;

use Mild\Handlers\ValidationHandler as BaseHandler;

class ValidationHandler extends BaseHandler
{
    /**
     * A list of the inputs that are never flashed.
     * 
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation'
    ];
}
