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

use Memcached;

class MemcachedHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $prefix;
    /**
     * @var Memcached
     */
    protected $memcached;

    /**
     * MemcachedHandler constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->memcached = isset($config['persistent_id']) ? new Memcached : new Memcached($config['persistent_id']);
        if (isset($config['prefix'])) {
            $this->prefix = $config['prefix'];
        }
        if (!empty($config['options'])) {
            $this->memcached->setOptions($config['options']);
        }
        if (count($config['sasl']) === 2) {
            $this->memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
            $this->memcached->setSaslAuthData($config['sasl'][0], $config['sasl'][1]);
        }
        $this->memcached->addServer($config['host'], $config['port'], $config['weight']);
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        $this->get($key);
        return Memcached::RES_NOTFOUND !== $this->memcached->getResultCode();
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->memcached->get($this->prefix.$key);
    }

    /**
     * @param $key
     * @param $value
     * @param int $expired
     * @return bool|mixed
     */
    public function set($key, $value, $expired = 0)
    {
        return $this->memcached->set($this->prefix.$key, $value, $expired);
    }

    /**
     * @param $key
     * @param int $value
     * @return bool|int
     */
    public function increment($key, $value = 1)
    {
        return $this->memcached->increment($this->prefix.$key, $value);
    }

    /**
     * @param $key
     * @param int $value
     * @return bool|int
     */
    public function decrement($key, $value = 1)
    {
        return $this->memcached->decrement($this->prefix.$key, $value);
    }

    /**
     * @param $key
     * @return bool
     */
    public function put($key)
    {
        return $this->memcached->delete($this->prefix.$key);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->memcached->flush();
    }
}