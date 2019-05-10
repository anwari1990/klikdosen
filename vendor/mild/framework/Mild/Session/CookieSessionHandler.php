<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Session;

use SessionHandlerInterface;

class CookieSessionHandler implements SessionHandlerInterface
{
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $prefix;
    /**
     * @var string
     */
    protected $domain;
    /**
     * @var \Mild\Cookie\CookieJar
     */
    protected $cookie;
    /**
     * @var \Mild\Http\Request
     */
    protected $request;
    /**
     * @var int
     */
    protected $expired;
    /**
     * @var string
     */
    protected $sameSite;
    /**
     * @var bool
     */
    protected $secure = false;
    /**
     * @var bool
     */
    protected $httpOnly = false;


    /**
     * CookieSessionHandler constructor.
     * @param \Mild\Cookie\CookieJar $cookie
     * @param \Mild\Http\Request $request
     * @param array $config
     */
    public function __construct($cookie, $request, $config = [])
    {
        $this->cookie = $cookie;
        $this->request = $request;
        if (isset($config['path'])) {
            $this->path = $config['path'];
        }
        if (isset($config['prefix'])) {
            $this->prefix = $config['prefix'];
        }
        if (isset($config['domain'])) {
            $this->domain = $config['domain'];
        }
        if (isset($config['expired'])) {
            $this->expired = $config['expired'];
        }
        if (isset($config['secure'])) {
            $this->secure = $config['secure'];
        }
        if (isset($config['httpOnly'])) {
            $this->httpOnly = $config['httpOnly'];
        }
        if (isset($config['sameSite'])) {
            $this->sameSite = $config['sameSite'];
        }
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @param string $session_id
     * @return bool
     */
    public function destroy($session_id)
    {
        $this->cookie->queue($this->prefix.$session_id);
        return true;
    }

    /**
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     * @param string $save_path
     * @param string $name
     * @return bool
     */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
     * @param string $session_id
     * @return string
     */
    public function read($session_id)
    {
        return $this->request->getCookieParam($this->prefix.$session_id) ?: '';
    }

    /**
     * @param string $session_id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        $this->cookie->queue($this->prefix.$session_id, $session_data, $this->expired, $this->path, $this->domain, $this->secure, $this->httpOnly, $this->sameSite);
        return true;
    }
}