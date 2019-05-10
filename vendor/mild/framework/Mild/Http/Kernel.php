<?php

namespace Mild\Http;

use Throwable;
use Mild\Bootstrap\HandleError;
use Mild\Bootstrap\RegisterConfig;
use Mild\Bootstrap\RegisterFacade;
use Mild\Handlers\HandlerInterface;
use Mild\Bootstrap\RegisterProvider;

class Kernel
{
    /**
     * @var \Mild\App
     */
    protected $app;
    /**
     * Register global middleware stack on the application
     *
     * @var array
     */
    protected $middleware = [];
    /**
     * Set aliases on calling middleware
     *
     * @var array
     */
    protected $middlewareAliases = [];

    /**
     * Kernel constructor.
     * @param $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @param $request
     * @return mixed
     * @throws \ReflectionException
     */
    public function handle($request)
    {
        try {
            $this->bootstrap();
            $router = $this->app->get('router');
            $router->middlewareStack = $this->middleware;
            $router->middlewareAliases = $this->middlewareAliases;
            return $router->run($request);
        } catch (Throwable $e) {
            $handler = $this->app->get(HandlerInterface::class);
            $handler->report($e);
            return $this->app->get(HandlerInterface::class)->handle($e, $request);
        }
    }

    /**
     * @return \Mild\App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * @return array
     */
    public function getMiddlewareAliases()
    {
        return $this->middlewareAliases;
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function bootstrap()
    {
        (new HandleError)->bootstrap($this->app);
        (new RegisterConfig)->bootstrap($this->app);
        (new RegisterFacade)->bootstrap($this->app);
        (new RegisterProvider)->bootstrap($this->app);
    }
}
