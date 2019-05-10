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

use Exception;
use SessionHandlerInterface;

class FileSessionHandler implements SessionHandlerInterface
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
     * FileSessionHandler constructor.
     * @param array $config
     * @throws Exception
     */
    public function __construct($config = [])
    {
        if (isset($config['path'])) {
            $this->path = rtrim($config['path'], '/') .'/';
        }
        if (isset($config['prefix'])) {
            $this->prefix = $config['prefix'];
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
        if (file_exists($file = $this->path.$this->prefix.$session_id)) {
            unlink($file);
        }
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
     * @return false|string
     */
    public function read($session_id)
    {
        return (string)@file_get_contents($this->path.$this->prefix.$session_id);
    }

    /**
     * @param string $session_id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        return file_put_contents($this->path.$this->prefix.$session_id, $session_data) === false ? false : true;
    }
}

