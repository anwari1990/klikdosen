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

use Closure;
use Throwable;
use Mild\Http\Response;
use InvalidArgumentException;
use Mild\Handlers\HandlerInterface;

class Router
{
    /**
     * @var \Mild\App
     */
    protected $app;
    /**
     * @var string
     */
    protected $baseUrl;
    /**
     * @var int
     */
    protected $queue = 0;
    /**
     * @var string
     */
    protected $currentUrl;
    /**
     * @var array
     */
    protected $nameStack = [];
    /**
     * @var array
     */
    protected $routeStack = [];
    /**
     * @var array
     */
    public $middlewareStack = [];
    /**
     * @var array
     */
    public $middlewareAliases = [];
    /**
     * @var array
     */
    protected $groupAttributes = [
        'prefix' => '',
        'namespace' => '',
        'conditions' => [],
        'middleware' => []
    ];

    /**
     * Router constructor.
     * @param \Mild\App $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @param $url
     * @param null $action
     * @return Route
     */
    public function get($url, $action = null)
    {
        return $this->map(['GET', 'HEAD'], $url, $action);
    }

    /**
     * @param $url
     * @param null $action
     * @return Route
     */
    public function post($url, $action = null)
    {
        return $this->map(['POST'], $url, $action);
    }

    /**
     * @param $url
     * @param null $action
     * @return Route
     */
    public function put($url, $action = null)
    {
        return $this->map(['PUT'], $url, $action);
    }

    /**
     * @param $url
     * @param null $action
     * @return Route
     */
    public function delete($url, $action = null)
    {
        return $this->map(['DELETE'], $url, $action);
    }

    /**
     * @param $url
     * @param null $action
     * @return Route
     */
    public function patch($url, $action = null)
    {
        return $this->map(['PATCH'], $url, $action);
    }

    /**
     * @param $url
     * @param null $action
     * @return Route
     */
    public function options($url, $action = null)
    {
        return $this->map(['OPTIONS'], $url, $action);
    }

    /**
     * @param $url
     * @param null $action
     * @return Route
     */
    public function any($url, $action = null)
    {
        return $this->map(['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'], $url, $action);
    }

    /**
     * @param array $attributes
     * @param $router
     */
    public function group(array $attributes, $router)
    {
        $oldGroupAttribute = $this->groupAttributes;
        if (isset($attributes['prefix'])) {
            $this->groupAttributes['prefix'] .= '/'.trim($attributes['prefix'], '/');
        }
        if (isset($attributes['namespace'])) {
            $this->groupAttributes['namespace'] .= '\\'.trim($attributes['namespace'], '\\');
        }
        if (isset($attributes['conditions'])) {
            $this->groupAttributes['conditions'] = $attributes['conditions'];
        }
        if (isset($attributes['middleware'])) {
            if (!is_array($attributes['middleware'])) {
                $attributes['middleware'] = [$attributes['middleware']];
            }
            foreach ($attributes['middleware'] as $middleware) {
                $this->groupAttributes['middleware'][] = $middleware;
            }
        }
        if ($router instanceof Closure) {
            $router($this);
        } else {
            require $router;
        }
        $this->groupAttributes = $oldGroupAttribute;
    }

    /**
     * @param $name
     * @return RouterAttribute
     */
    public function name($name)
    {
        return $this->attribute()->name($name);
    }

    /**
     * @param $key
     * @param $value
     * @return RouterAttribute
     */
    public function where($key, $value)
    {
        return $this->attribute()->where($key, $value);
    }

    /**
     * @param $prefix
     * @return RouterAttribute
     */
    public function prefix($prefix)
    {
        return $this->attribute()->prefix($prefix);
    }

    /**
     * @param $namespace
     * @return RouterAttribute
     */
    public function namespace($namespace)
    {
        return $this->attribute()->namespace($namespace);
    }

    /**
     * @return RouterAttribute
     */
    public function middleware()
    {
        return $this->attribute()->middleware(...func_get_args());
    }

    /**
     * @return RouterAttribute
     */
    public function attribute()
    {
        return new RouterAttribute($this);
    }

    /**
     * @param \Mild\Http\Request $request
     * @return \Mild\Http\Response
     * @throws \ReflectionException
     */
    public function run($request)
    {
        $found = $allowed = false;
        $uri = $request->getUri();
        $parts = explode('/', $request->getServerParam('SCRIPT_NAME'));
        $file = array_pop($parts);
        $this->currentUrl = substr($uri->getPath(), strlen($path = implode('/', $parts)));
        $parts = explode('/', $this->currentUrl);
        if ($parts[1] === $file) {
            unset($parts[1]);
        }
        $this->currentUrl = '/'.trim(implode('/', $parts), '/');
        $this->baseUrl = $uri->getScheme().'://'.$uri->getAuthority().$path;
        foreach ($this->routeStack as $route) {
            if (($name = $route->getAction('name')) !== '') {
                $this->nameStack[$name] = $route->getUrl();
            }
            if (($matches = $route->match($this->currentUrl)) !== []) {
                $found = true;
                if ($route->allow($request->getMethod())) {
                    $allowed = true;
                    array_shift($matches);
                    $request = $request->withAttribute('router', [
                        'parameters' => $matches,
                        'callback' => $route->getAction('callback')
                    ]);
                    foreach ($route->getAction('middleware') as $value) {
                        $this->middlewareStack[] = $value;
                    }
                    $this->middlewareStack[] = [$this, 'resolve'];
                }
            }
        }
        if ($found === false) {
            throw new RouterException(404);
        }
        if ($allowed === false) {
            throw new RouterException(405);
        }
        return $this->pipe($request, new Response);
    }

    /**
     * @param \Mild\Http\Request $request
     * @param \Mild\Http\Response $response
     * @return \Mild\Http\Response
     * @throws \ReflectionException
     */
    public function pipe($request, $response)
    {
        if (!isset($this->middlewareStack[$this->queue])) {
            return $response;
        }
        $this->app->set('request', $request);
        $this->app->set('response', $response);
        $middleware = $this->middlewareStack[$this->queue];
        ++$this->queue;
        if (is_string($middleware)) {
            if (isset($this->middlewareAliases[$middleware])) {
                $middleware = $this->middlewareAliases[$middleware];
            }
            $middleware = $this->app->instance($middleware);
        }
        return $middleware($request, $response, [$this, 'pipe']);
    }

    /**
     * @param \Mild\Http\Request $request
     * @param \Mild\Http\Response $response
     * @return \Mild\Http\Response
     * @throws \ReflectionException
     */
    public function resolve($request, $response)
    {
        try {
            ob_start();
            $attribute = $request->getAttribute('router');
            if (($output = $this->app->call($attribute['callback'], $attribute['parameters'])) instanceof Response) {
                ob_end_clean();
                return $output;
            }
            $response->getBody()->write(ob_get_clean().$output);
            return $response;
        } catch (Throwable $e) {
            $handler = $this->app->get(HandlerInterface::class);
            $handler->report($e);
            return $handler->handle($e, $request);
        }
    }

    /**
     * @return \Mild\App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param $key
     * @param array $parameters
     * @return string
     */
    public function getName($key, $parameters = [])
    {
        if (!isset($this->nameStack[$key])) {
            throw new InvalidArgumentException('Route name ['.$key.'] does not exist.');
        }
        $url = $this->nameStack[$key];
        if (strpos($url, '{') === false && strpos($url, '}') === false) {
            return $this->getBaseUrl($url);
        }
        if ($parameters === []) {
            throw new InvalidArgumentException('Route name ['.$key.'] need a parameters.');
        }
        return $this->getBaseUrl(preg_replace_callback('/\{(.*?)\}/', function () use (&$parameters) {
            return array_shift($parameters);
        }, $url));
    }

    /**
     * @param string $url
     * @return string
     */
    public function getBaseUrl($url = '')
    {
        return $this->baseUrl.'/'.ltrim($url, '/');
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->currentUrl;
    }

    /**
     * @return array
     */
    public function getRouteStack()
    {
        return $this->routeStack;
    }

    /**
     * @param $routeStack
     * @return $this
     */
    public function setRouteStack($routeStack)
    {
        $this->routeStack = $routeStack;
        return $this;
    }

    /**
     * @param $methods
     * @param $url
     * @param $action
     * @return Route
     */
    protected function map($methods, $url, $action)
    {
        if (!is_array($action)) {
            $action = ['callback' => $action];
        }
        if (isset($this->groupAttributes['conditions'])) {
            foreach ($this->groupAttributes['conditions'] as $key => $value) {
                $action['conditions'][$key] = $value;
            }
        }
        if (isset($action['callback']) && $action['callback'] instanceof Closure === false) {
            if (is_array($action['callback'])) {
                if (is_string($action['callback'][0])) {
                    $action['callback'][0] = rtrim($this->groupAttributes['namespace'], '\\').'\\'.ltrim($action['callback'][0], '\\');
                }
            } else {
                $action['callback'] = rtrim($this->groupAttributes['namespace'], '\\').'\\'.ltrim($action['callback'], '\\');
            }
        }
        $middleware = $this->groupAttributes['middleware'];
        if (isset($action['middleware'])) {
            if (!is_array($action['middleware'])) {
                $action['middleware'] = [$action['middleware']];
            }
            foreach ($action['middleware'] as $value) {
                $middleware[] = $value;
            }
        }
        $action['middleware'] = $middleware;
        $url = '/'.trim($url, '/');
        if (($prefix = '/'.trim($this->groupAttributes['prefix'], '/')) !== '/') {
            $url = rtrim($prefix.$url, '/');
        }
        $this->routeStack[] = $route = new Route($url, $methods, $action);
        return $route;
    }
}
