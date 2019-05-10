<?php
/**
 * Register Application
 */
$app = new Mild\App(dirname(__DIR__));
/**
 * Set configuration path
 */
$app->setConfigPath(path('config'));
/**
 * Set cached route path
 */
$app->setRouteCachePath(path('bootstrap/cache/routes.php'));
/**
 * Set cached configuration path
 */
$app->setConfigCachePath(path('bootstrap/cache/config.php'));
/**
 * Register Http Kernel
 */
$app->set(App\Http\Kernel::class, function ($app) {
    return new App\Http\Kernel($app);
});
/**
 * Register Console Kernel
 */
$app->set(App\Console\Kernel::class, function ($app) {
    return new App\Console\Kernel($app);
});
/**
 * Register handler application
 */
$app->set(Mild\Handlers\HandlerInterface::class, function ($app) {
    return new App\Handlers\Handler($app);
});
/**
 * Return the application
 */
return $app;
