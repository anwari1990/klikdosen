<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Cookie;

class CookieJar
{
    /**
     * @var array
     */
    protected $queued = [];

    /**
     * @param $name
     * @param string $value
     * @param int $expired
     * @param string $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param null $sameSite
     * @return Cookie
     */
    public function make($name, $value = '', $expired = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true, $sameSite = null)
    {
        return new Cookie($name, $value, $expired, $path, $domain, $secure, $httpOnly, $sameSite);
    }

    /**
     * @param $name
     * @param string $value
     * @param int $expired
     * @param string $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param null $sameSite
     * @return void
     */
    public function queue($name, $value = '', $expired = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true, $sameSite = null)
    {
        $this->queued[$name] = $this->make($name, $value, $expired, $path, $domain, $secure, $httpOnly, $sameSite);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasQueue($name)
    {
        return isset($this->queued[$name]);
    }

    /**
     * @param string $name
     * @return void
     */
    public function unqueue($name)
    {
        unset($this->queued[$name]);
    }

    /**
     * @return array
     */
    public function getQueued()
    {
        return $this->queued;
    }
}
