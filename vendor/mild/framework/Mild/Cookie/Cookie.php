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

use DateTimeInterface;
use InvalidArgumentException;

class Cookie
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $value;
    /**
     * @var bool
     */
    protected $secure;
    /**
     * @var string
     */
    protected $domain;
    /**
     * @var int
     */
    protected $expired;
    /**
     * @var bool
     */
    protected $httpOnly;
    /**
     * @var string
     */
    protected $sameSite;

    /**
     * Cookie constructor.
     * @param string $name
     * @param string $value
     * @param int|string|\DateTimeInterface$expired
     * @param string $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param null $sameSite
     */
    public function __construct($name, $value = '', $expired = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true, $sameSite = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->setPath($path);
        $this->setDomain($domain);
        $this->setSecure($secure);
        $this->setHttpOnly($httpOnly);
        $this->setSameSite($sameSite);
        if (is_string($expired) === true && ($expired = strtotime($expired)) === false) {
            throw new InvalidArgumentException('Invalid value expiration cookie.');
        } elseif ($expired instanceof DateTimeInterface === true) {
            $expired = $expired->format('U');
        }
        $this->expired = $expired;
    }

    /**
     * @param $path
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @param $domain
     * @return void
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @param bool $secure
     * @return void
     */
    public function setSecure($secure = true)
    {
        $this->secure = $secure;
    }

    /**
     * @param bool $httpOnly
     * @return void
     */
    public function setHttpOnly($httpOnly = true)
    {
        $this->httpOnly = $httpOnly;
    }

    /**
     * @param $sameSite
     * @return void
     */
    public function setSameSite($sameSite)
    {
        $this->sameSite = $sameSite;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getExpired()
    {
        return $this->expired;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return bool
     */
    public function isSecure()
    {
        return $this->secure === true;
    }

    /**
     * @return bool
     */
    public function isHttpOnly()
    {
        return $this->httpOnly === true;
    }

    /**
     * @return string
     */
    public function getSameSite()
    {
        return $this->sameSite;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $format = urlencode($this->name).'=';
        if ($this->value === '') {
            $format .= 'deleted; Expires='.gmdate('D, d-M-Y H:i:s T', time() - 31536001).'; Max-Age=0';
        } else {
            $format .= rawurlencode($this->value);
            if ($this->expired !== 0) {
                $format .= '; Expires='.gmdate('D, d-M-Y H:i:s T', $this->expired).'; Max-Age='.($this->expired - time());
            }
        }
        if ($this->path !== null) {
            $format .= '; Path='.$this->path;
        }
        if ($this->domain !== null) {
            $format .= '; Domain='.$this->domain;
        }
        if ($this->isSecure()) {
            $format .= '; Secure';
        }
        if ($this->isHttpOnly()) {
            $format .= '; HttpOnly';
        }
        if ($this->sameSite !== null) {
            $format .= '; SameSite='.$this->sameSite;
        }
        return $format;
    }
}
