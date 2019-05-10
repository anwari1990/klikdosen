<?php

namespace Mild\Handlers;

use Whoops\Run;
use Mild\Http\Response;
use Whoops\Handler\PrettyPageHandler;

class Handler implements HandlerInterface
{
    /**
     * @var \Mild\App
     */
    protected $app;
    /**
     * @var array
     */
    protected $handlers = [];
    /**
     * @var array
     */
    protected $dontReport = [];

    /**
     * Handler constructor.
     * @param \Mild\App $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @param \Throwable $e
     * @return void
     * @throws \ReflectionException
     */
    public function report($e)
    {
        if ($this->shouldReport($class = get_class($e))) {
            $this->app->get('logger')->error($e->getMessage(), [$class => $e]);
        }
    }

    /**
     * @param $e
     * @param null $request
     * @return Response|mixed
     */
    public function handle($e, $request = null)
    {
        if (isset($this->handlers[$class = get_class($e)])) {
            return (new $this->handlers[$class])->handle($e, $request);
        }
        $response = new Response(500);
        if ($request->isXhr() || $request->isJson()) {
            return $response->json([
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        $response->getBody()->write($this->render($e, [
            'allowQuit' => false,
            'writeToOutput' => false,
            'pushHandler' => new PrettyPageHandler,
        ]));
        return $response;
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
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @return array
     */
    public function getDontReport()
    {
        return $this->dontReport;
    }

    /**
     * @param \Throwable $e
     * @param array $options
     * @return string
     */
    protected function render($e, $options = [])
    {
        $whoops = new Run;
        foreach ($options as $key => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }
            $whoops->$key(...$value);
        }
        return $whoops->handleException($e);
    }

    /**
     * @param $class
     * @return bool
     */
    protected function shouldReport($class)
    {
        return in_array($class, $this->dontReport) === false;
    }
}
