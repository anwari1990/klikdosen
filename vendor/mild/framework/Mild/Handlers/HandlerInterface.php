<?php

namespace Mild\Handlers;

interface HandlerInterface
{
    /**
     * @return \Mild\App
     */
    public function getApp();

    /**
     * @param \Throwable $e
     * @return void
     */
    public function report($e);

    /**
     * @return array
     */
    public function getHandlers();

    /**
     * @return array
     */
    public function getDontReport();

    /**
     * @param $e
     * @param \Mild\Http\Request|null $request
     * @return mixed
     */
    public function handle($e, $request = null);
}