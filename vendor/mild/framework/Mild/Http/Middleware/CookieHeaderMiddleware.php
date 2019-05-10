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
use Mild\Encryption\EncryptionException;

class CookieHeaderMiddleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [];

    /**
     * @param \Mild\Http\Request $request
     * @param \Mild\Http\Response $response
     * @param callable $next
     * @return \Mild\Http\Response
     * @throws \ReflectionException
     */
    public function __invoke($request, $response, $next)
    {
        return $this->encrypt($next($this->decrypt($request), $response)->withCookie(app('cookie')->getQueued()));
    }

    /**
     * @param \Mild\HTtp\Response $response
     * @return \Mild\Http\Response
     * @throws \ReflectionException
     */
    protected function encrypt($response)
    {
        foreach (($cookie = $response->getHeader('set-cookie')) as $key => $value) {
            if ($this->disable($name = $value->getName())) {
                continue;
            }
            $cookie[$key] = new Cookie(
                $name, encrypt($value->getValue()), $value->getExpired(),
                $value->getPath(), $value->getDomain(), $value->isSecure(), $value->isHttpOnly(),
                $value->getSameSite()
            );
        }
        return $response->withCookie($cookie, false);
    }

    /**
     * @param \Mild\Http\Request $request
     * @return \Mild\Http\Request
     * @throws \ReflectionException
     */
    protected function decrypt($request)
    {
        foreach (($cookie = $request->getCookieParams()) as $key => $value) {
            if ($this->disable($key)) {
                continue;
            }
            try {
                $cookie[$key] = decrypt($value);
            } catch (EncryptionException $e) {
                $cookie[$key] = $value;
            }
        }
        return $request->withCookieParams($cookie);
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function disable($name)
    {
        return in_array($name, $this->except);
    }
}
