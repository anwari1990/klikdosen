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

use Redis;

class RedisHandler implements HandlerInterface
{
    /**
     * @var Redis
     */
    protected $redis;

    /**
     * RedisHandler constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->redis = new Redis;
        $host = 'localhost';
        $port = null;
        $persistent = false;
        $persistentId = null;
        $timeout = 0.0;
        $retryInterval = 0;
        $readTimeOut = null;
        if (isset($config['host'])) {
            $host = $config['host'];
        }
        if (isset($config['port'])) {
            $port = $config['port'];
        }
        if (isset($config['persistent'])) {
            $persistent = $config['persistent'];
        }
        if ($persistent === true && isset($config['persistent_id'])) {
            $persistentId = $config['persistent_id'];
        }
        if (isset($config['timeout'])) {
            $timeout = $config['timeout'];
        }
        if (isset($config['retry_interval'])) {
            $retryInterval = $config['retry_interval'];
        }
        if (version_compare(phpversion('redis'), '3.1.3', '>=') && isset($config['read_timeout'])) {
            $readTimeOut = $config['read_timeout'];
        }
        $this->redis->{($persistent ? 'pconnect' : 'connect')}($host, $port, $timeout, $persistentId, $retryInterval, $readTimeOut);
        if(!empty($config['password'])) {
            $this->redis->auth($config['password']);
        }
        if (!empty($config['prefix'])) {
            $this->redis->setOption(Redis::OPT_PREFIX, $config['prefix']);
        }
        if (!empty($config['database'])) {
            $this->redis->select($config['database']);
        }
        if ($readTimeOut) {
            $this->redis->setOption(Redis::OPT_READ_TIMEOUT, $readTimeOut);
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->redis->get($key);
        if (!is_numeric($value)) {
            $value = unserialize($value);
        }
        return $value;
    }

    /**
     * @param $key
     * @param $value
     * @param int $expired
     * @return bool|mixed
     */
    public function set($key, $value, $expired = 0)
    {
        if (!is_numeric($value)) {
            $value = serialize($value);
        }
        if ($expired === 0) {
            $expired = null;
        }
        return $this->redis->set($key, $value, $expired);
    }

    /**
     * @param $key
     * @param int $value
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        return $this->redis->incrBy($key, $value);
    }

    /**
     * @param $key
     * @param int $value
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return $this->redis->decrBy($key, $value);
    }

    /**
     * @param $key
     * @return void|bool
     */
    public function put($key)
    {
        return $this->redis->delete($key);
    }

    /**
     * @return bool|void
     */
    public function flush()
    {
        return $this->redis->flushDB();
    }
}
