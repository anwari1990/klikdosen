<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Http\Middleware;

use Mild\Supports\ViewErrorBag;

class ShareViewErrorsMiddleware
{

    /**
     * @param \Mild\Http\Request $request
     * @param \Mild\Http\Response $response
     * @param callable $next
     * @return \Mild\Http\Response
     * @throws \ReflectionException
     */
    public function __invoke($request, $response, $next)
    {
        app('view')->share('errors', new ViewErrorBag(app('flash')->get('errors')));
        return $next($request, $response);
    }
}