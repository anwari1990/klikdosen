<?php

namespace Mild\Supports\Facades;

use RuntimeException;

abstract class Facade
{
    /**
     * @var \Mild\App
     */
    protected static $app;
    /**
     * @var array
     */
    protected static $root = [];

    /**
     * @return string
     */
    protected static function setFacadeRoot()
    {
        throw new RuntimeException('Facade does not implement setFacadeRoot method.');
    }

    /**
     * @param \Mild\App $app
     * @return void
     */
    public static function setApp($app)
    {
        static::$app = $app;
    }

    /**
     * @return \Mild\App
     */
    public static function getApp()
    {
        return static::$app;
    }

    /**
     * @return object
     * @throws \ReflectionException
     */
    public static function getFacadeRoot()
    {
        $root = static::setFacadeRoot();
        if (isset(static::$root[$root])) {
            return static::$root[$root];
        }
        return static::$root[$root] = static::$app->get($root);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \ReflectionException
     */
    public static function __callStatic($name, $arguments)
    {
        return static::getFacadeRoot()->$name(...$arguments);
    }
}
