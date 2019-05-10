<?php

namespace Mild\Supports\Facades;

class Mail extends Facade
{
    /**
     * @return string
     */
    protected static function setFacadeRoot()
    {
        return 'mail';
    }
}