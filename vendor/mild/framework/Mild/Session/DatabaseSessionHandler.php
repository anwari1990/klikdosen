<?php

namespace Mild\Session;

use SessionHandlerInterface;

class DatabaseSessionHandler implements SessionHandlerInterface
{
    /**
     * @var \Mild\Database\Database
     */
    protected $database;
    /**
     * Determine if the session is exists
     *
     * @var bool
     */
    protected $exists = false;
    /**
     * @var string
     */
    protected $prefix;
    /**
     * @var string
     */
    protected $table = 'sessions';
    /**
     * @var array
     */
    protected $columns = [
        'id' => 'id',
        'payload' => 'payload',
        'last_activity' => 'last_activity'
    ];

    /**
     * DatabaseSessionHandler constructor.
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
            foreach ((array) $config['columns'] as $key => $value) {
                $this->columns[$key] = $value;
            }
        }
    }

    /**
     * @return bool
     */
    public function isExists()
    {
        return $this->exists;
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
        $this->exists = false;
        $this->database->table($this->table)->where($this->columns['id'], '=', $this->prefix.$session_id)->delete();
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
        $session = $this->database->table($this->table)->where($this->columns['id'], '=', $this->prefix.$session_id)->first();
        if ($session === null) {
            return '';
        }
        $this->exists = true;
        return base64_decode($session->{$this->columns['payload']});
    }

    /**
     * @param string $session_id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        $last_activity = time();
        $session_id = $this->prefix.$session_id;
        $session_data = base64_encode($session_data);
        if ($this->exists) {
            $this->database->table($this->table)->where($this->columns['id'], '=', $session_id)->update([
               $this->columns['payload'] => $session_data,
               $this->columns['last_activity'] => $last_activity
            ]);
        } else {
            $this->database->table($this->table)->insert([
               $this->columns['id'] => $session_id,
               $this->columns['payload'] => $session_data,
               $this->columns['last_activity'] => $last_activity
            ]);
        }
        return $this->exists = true;
    }
}
