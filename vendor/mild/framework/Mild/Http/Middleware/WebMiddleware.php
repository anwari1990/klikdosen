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

use Mild\Routing\RouterException;

class WebMiddleware
{
    /**
     * @var array
     */
    protected $excepts = [];

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
        if (is_null($token = $session->get('_token'))) {
            $session->set('_token', str_rand());
        }
        if ($this->isReading($request->getMethod()) || $this->isExcept() || $this->isMatchToken($request, $token)) {
            return $next($request, $response);
        }
        throw new RouterException(419);
    }

    /**
     * @param $method
     * @return bool
     */
    protected function isReading($method)
    {
        if ($method === 'HEAD' || $method === 'GET' || $method === 'OPTIONS') {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws \ReflectionException
     */
    protected function isExcept()
    {
        $uri = app('router')->getCurrentUrl();
        foreach ($this->excepts as $except) {
            if (($except = trim($except, '/')) !== '') {
                $except = '/'.$except;
            }
            if (preg_match('#^'.preg_quote($except, '#').'\z#u', $uri, $matches)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param \Mild\Http\Request $request
     * @param $token
     * @return bool
     */
    protected function isMatchToken($request, $token)
    {
        if (($input = $request->getParsedBodyParam('_token')) === null) {
            $input = $request->getHeaderLine('X-CSRF-TOKEN');
        }
        return $input === $token;
    }
}
