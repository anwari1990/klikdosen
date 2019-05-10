<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Routing;

class Route
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var array
     */
    protected $methods;
    /**
     * @var array
     */
    protected $actions = [
        'name' => '',
        'callback' => '',
        'conditions' => [],
        'middleware' => []
    ];

    /**
     * Route constructor.
     * @param string $url
     * @param array $methods
     * @param array $actions
     */
    public function __construct($url, $methods, $actions)
    {
        $this->url = $url;
        $this->methods = $methods;
        if (isset($actions['name'])) {
            $this->actions['name'] = $actions['name'];
        }
        if (isset($actions['callback'])) {
            $this->actions['callback'] = $actions['callback'];
        }
        if (isset($actions['conditions'])) {
            $this->actions['conditions'] = $actions['conditions'];
        }
        if (isset($actions['middleware'])) {
            $this->actions['middleware'] = $actions['middleware'];
        }
    }

    /**
     * @param $name
     * @return $this
     */
    public function name($name)
    {
        $this->actions['name'] = $name;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function where($key, $value)
    {
        $this->actions['conditions'][$key] = $value;
        return $this;
    }

    /**
     * @return $this
     */
    public function middleware()
    {
        $middleware = func_get_args();
        if (isset($middleware[0]) && is_array($middleware[0])) {
            $middleware = $middleware[0];
        }
        if ($this->actions['middleware'] === []) {
            $this->actions['middleware'] = $middleware;
        } else {
            foreach ($middleware as $value) {
                $this->actions['middleware'][] = $value;
            }
        }
        return $this;
    }

    /**
     * @param sring $url
     * @return array
     */
    public function match($url)
    {
        preg_match('#^'.preg_replace_callback('/\{(.*?)\}/', [$this, 'replace'], $this->url).'$#i', $url, $matches);
        return $matches;
    }

    /**
     * @param string $method
     * @return bool
     */
    public function allow($method)
    {
        return in_array($method, $this->methods);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public function getAction($name, $default = null)
    {
        if (!isset($this->actions[$name])) {
            return $default;
        }
        return $this->actions[$name];
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

       /**
     * @param array $match
     * @return string
     */
    private function replace($match)
    {
        if (isset($this->actions['conditions'][$match[1]])) {
            return '('.$this->actions['conditions'][$match[1]].')';
        }
        return '([^/]+)';
    }
}
