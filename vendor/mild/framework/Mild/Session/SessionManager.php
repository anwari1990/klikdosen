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

use ArrayAccess;
use RuntimeException;
use Mild\Supports\Traits\Dot;

class SessionManager implements ArrayAccess
{
    use Dot;

    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var \SessionHandlerInterface
     */
    protected $handler;
    /**
     * @var bool
     */
    protected $started = false;

    /**
     * SessionManager constructor.
     * @param string $name
     * @param \SessionHandlerInterface $handler
     * @param null $id
     */
    public function __construct($name, $handler, $id = null)
    {
        $this->setId($id);
        $this->name = $name;
        $this->handler = $handler;
    }

    /**
     * @param $id
     * @return void
     */
    public function setId($id)
    {
        if ($this->isValidId($id) === false) {
            $id = str_rand(40);
        }
        $this->id = $id;
    }

    /**
     * @param $id
     * @return bool
     */
    protected function isValidId($id)
    {
        return is_string($id) === true && ctype_alnum($id) === true && strlen($id) === 40;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $destroy
     * @return bool
     * @throws \Exception
     */
    public function regenerate($destroy = false)
    {
        if ($this->started === false) {
            throw new RuntimeException('Cannot regenerate the session when status is inactive');
        }
        if ($destroy === true) {
            $this->handler->destroy($this->id);
        }
        $this->id = session_create_id();
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \SessionHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function start()
    {
        if ($this->started === true) {
            throw new RuntimeException('Cannot start the session when status is active');
        }
        if (is_array($items = unserialize($this->handler->read($this->id)))) {
            $this->items = $items;
        }
        return $this->started = true;
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * @return bool
     */
    public function destroy()
    {
        if ($this->started === false) {
            throw new RuntimeException('Cannot destroy the session when status is inactive');
        }
        $this->items = [];
        $this->started = false;
        return true;
    }

    /**
     * @return bool
     */
    public function save()
    {
        if ($this->started === false || ($item = serialize($this->items)) === $this->handler->read($this->id)) {
            return false;
        }
        $this->handler->destroy($this->id);
        return $this->handler->write($this->id, $item);
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @param $name
     * @return void
     */
    public function __unset($name)
    {
        $this->put($name);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->put($offset);
    }
}
