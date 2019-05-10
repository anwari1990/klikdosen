<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Cache;

use Memcache;

class MemcacheHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $prefix;
    /**
     * @var Memcache
     */
    protected $memcache;

    /**
     * MemcacheHandler constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->memcache = new Memcache();
        if (isset($config['prefix'])) {
            $this->prefix = $config['prefix'];
        }
        $this->memcache->addServer($config['host'], $config['port'], $config['persistent'], $config['weight'], $config['timeout'], $config['retry_interval']);
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return $this->get($key) !== false;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->memcache->get($this->prefix.$key);
    }

    /**
     * @param $key
     * @param $value
     * @param int $expired
     * @return bool|mixed
     */
    public function set($key, $value, $expired = 0)
    {
        return $this->memcache->set($this->prefix.$key, $value, 0, $expired);
    }

    /**
     * @param $key
     * @param int $value
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        return $this->memcache->increment($this->prefix.$key, $value);
    }

    /**
     * @param $key
     * @param int $value
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return $this->memcache->decrement($this->prefix.$key, $value);
    }

    /**
     * @param $key
     * @return void|bool
     */
    public function put($key)
    {
        return $this->memcache->delete($this->prefix.$key);
    }

    /**
     * @return void|bool
     */
    public function flush()
    {
        return $this->memcache->flush();
    }
}