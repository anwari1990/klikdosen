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

class CacheSessionHandler implements SessionHandlerInterface
{
    /**
     * @var \Mild\Cache\CacheManager
     */
    protected $cache;
    /**
     * @var string
     */
    protected $prefix;
    /**
     * @var int
     */
    protected $expired;

    /**
     * CacheSessionHandler constructor.
     * @param \Mild\Cache\CacheManager $cache
     * @param array $config
     */
    public function __construct($cache, $config = [])
    {
        $this->cache = $cache;
        if (isset($config['prefix'])) {
            $this->prefix = $config['prefix'];
        }
        if (isset($config['expired'])) {
            $this->expired = $config['expired'];
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
        $this->cache->put($this->prefix.$session_id);
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
        return $this->cache->get($this->prefix.$session_id);
    }

    /**
     * @param string $session_id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        $this->cache->set($this->prefix.$session_id, $session_data);
        return true;
    }
}