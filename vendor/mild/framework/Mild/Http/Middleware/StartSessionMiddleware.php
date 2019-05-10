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

use Mild\Cookie\Cookie;

class StartSessionMiddleware
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
        $session = app('session');
        $session->setId($request->getCookieParam($name = $session->getName()));
        $session->start();
        $response = $next($request, $response)->withCookie(new Cookie($name, $session->getId()));
        $session->save();
        return $response;
    }
}
