<?php

namespace Mild\Handlers;

use Mild\Http\Response;
use Mild\Supports\Facades\View;

class RouterHandler
{
    /**
     * @param \Throwable $e
     * @param \Mild\Http\Request|null $request
     * @return Response
     */
    public function handle($e, $request = null)
    {
        return View::renderResponse(new Response($code = $e->getCode()), 'errors/'.$code);
    }
}