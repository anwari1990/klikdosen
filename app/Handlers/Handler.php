<?php

namespace App\Handlers;

use Mild\Handlers\RouterHandler;
use Mild\Routing\RouterException;
use Mild\Validation\ValidationException;
use Mild\Handlers\Handler as BaseHandler;
use NunoMaduro\Collision\Handler as ConsoleHandler;

class Handler extends BaseHandler
{
    /**
     * Set if the exception is dont report to log
     *
     * @var array
     */
    protected $dontReport = [
        RouterException::class,
        ValidationException::class
    ];

    /**
     * Set the handler with the key is exception name and the value is class for handling
     *
     * @var array
     */
    protected $handlers = [
        RouterException::class => RouterHandler::class,
        ValidationException::class => ValidationHandler::class
    ];

    /**
     * @param $e
     * @param \Mild\Http\Request|null $request
     * @return mixed
     */
    public function handle($e, $request = null)
    {
        if ($request === null) {
            return $this->render($e, [
                'pushHandler' => new ConsoleHandler
            ]);
        }
        return parent::handle($e, $request);
    }
}
