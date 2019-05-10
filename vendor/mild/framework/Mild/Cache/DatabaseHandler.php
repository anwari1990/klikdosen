<?php

namespace Mild\Cache;

class DatabaseHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $prefix;
    /**
     * Set the table on the database
     *
     * @var string
     */
    protected $table = 'cache';
    /**
     * @var \Mild\Database\Database
     */
    protected $database;
    /**
     * @var array
     */
    protected $columns = [
        'key' => 'key',
        'payload' => 'payload',
        'expired' => 'expired'
    ];

    /**
     * DatabaseHandler constructor.
     * @param \Mild\Database\Database $database
     * @param array $config
     */
    public function __construct($database, $config = [])
    {
        $this->database = $database;
        if (isset($config['table'])) {
            $this->table = $config['table'];
        }
        if (isset($config['prefix'])) {
            $this->prefix = $config['prefix'];
        }
        if (isset($config['columns'])) {
            foreach ( (array) $config['columns'] as $key => $value) {
               $this->columns[$key] = $value;
            }
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return $this->database->table($this->table)->where($this->columns['key'], '=', $this->prefix.$key)->exists();
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $cache = $this->database->table($this->table)->where($this->columns['key'], '=', $this->prefix.$key)->first();
        if ($cache === null) {
            return false;
        }
        if (($expired = (int) $cache->{$this->columns['expired']}) !== 0 && $expired <= time()) {
            return $this->put($key);
        }
        return unserialize($cache->{$this->columns['payload']});

    }

    /**
     * @param $key
     * @param $value
     * @param int $expired
     * @return int|mixed
     */
    public function set($key, $value, $expired = 0)
    {
        $value = serialize($value);
        if ($this->has($key)) {
          return $this->database->table($this->table)->where($this->columns['key'], '=', $this->prefix.$key)->update([
              $this->columns['payload'] => $value,
              $this->columns['expired'] => $expired
          ]);
        }
        return $this->database->table($this->table)->insert([
            $this->columns['key'] => $this->prefix.$key,
            $this->columns['payload'] => $value,
            $this->columns['expired'] => $expired
        ]);
    }

    /**
     * @param $key
     * @param int $value
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        return $this->incrementOrDecrement($key, $value, function ($current, $value) {
            return $current + $value;
        });
    }

    /**
     * @param $key
     * @param int $value
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return $this->incrementOrDecrement($key, $value, function ($current, $value) {
            return $current - $value;
        });
    }

    /**
     * @param $key
     * @param $value
     * @param callable $callback
     * @return bool|int
     */
    protected function incrementOrDecrement($key, $value, callable $callback)
    {
        return $this->database->table($this->table)->where($this->columns['key'], '=', $this->prefix.$key)->update([
           $this->columns['payload'] => serialize($callback($this->get($key), $value))
        ]);
    }

    /**
     * @param $key
     * @return bool
     */
    public function put($key)
    {
        return $this->database->table($this->table)->where($this->columns['key'], '=', $this->prefix.$key)->delete();
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->database->table($this->table)->delete();
    }
}
