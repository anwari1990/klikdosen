<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild;

use Closure;
use ArrayAccess;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;

class App implements ArrayAccess
{
    /**
     * @var string
     */
    protected $basePath;
    /**
     * @var array
     */
    protected $bindings = [];
    /**
     * @var bool
     */
    protected $booted = false;
    /**
     * @var static
     */
    protected static $instance;
    /**
     * @var array
     */
    protected $aliasStack = [
        App::class => 'app',
        Config::class => 'config',
        Views\View::class => 'view',
        Log\Logger::class => 'logger',
        Database\Database::class => 'db',
        Http\Request::class => 'request',
        Http\Response::class => 'response',
        Routing\Router::class => 'router',
        Http\Response::class => 'response',
        Cookie\CookieJar::class => 'cookie',
        Cache\CacheManager::class => 'cache',
        Validation\Factory::class => 'validator',
        Session\SessionManager::class => 'session',
        Encryption\Encryption::class => 'encryption'
    ];
    /**
     * @var array
     */
    protected $facadeStack = [];
    /**
     * Application version
     * @var string
     */
    protected $version = '1.0.0';
    /**
     * @var string
     */
    protected $configPath;
    /**
     * @var string
     */
    protected $routeCachePath;
    /**
     * @var string
     */
    protected $configCachePath;
    /**
     * @var array
     */
    protected $providerStack = ['defers' => [], 'registered' => []];

    /**
     * App constructor.
     * @param string $basePath
     */
    public function __construct($basePath = '')
    {
        static::$instance = $this;
        if ($basePath) {
            $this->setBasePath($basePath);
        }
        $this->set('app', function ($app) {
           return $app;
        });
        $this->register(new Log\LogServiceProvider($this));
    }

    /**
     * @param $path
     * @return void
     */
    public function setBasePath($path)
    {
        $this->basePath = rtrim($path, '/');
    }

    /**
     * @param $path
     * @return void
     */
    public function setRouteCachePath($path)
    {
        $this->routeCachePath = $path;
    }

    /**
     * @return string
     */
    public function getRouteCachePath()
    {
        return $this->routeCachePath;
    }

    /**
     * @param $path
     * @return void
     */
    public function setConfigPath($path)
    {
        $this->configPath = $path;
    }

    /**
     * @return string
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }

    /**
     * @param $path
     */
    public function setConfigCachePath($path)
    {
        $this->configCachePath = $path;
    }

    /**
     * @return string
     */
    public function getConfigCachePath()
    {
        return $this->configCachePath;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getPath($name = '')
    {
        if ($name !== '') {
            $name = '/'.$name;
        }
        return $this->getBasePath().$name;
    }

    /**
     * @return App
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * @param Supports\ServiceProvider $provider
     * @param bool $defer
     * @return void
     */
    public function register($provider, $defer = true)
    {
        if ($provider instanceof Supports\ServiceProvider === false) {
            $provider = new $provider($this);
        }
        if ($defer === true && $provider->isDefer() === true) {
            foreach ($provider->provides() as $p) {
                $this->providerStack['defers'][$p] = $provider;
            }
            return;
        }
        $provider->register();
        $this->providerStack['registered'][] = $provider;
        if ($this->booted) {
            $provider->boot();
        }
    }

    /**
     * @param array $facades
     * @return void
     */
    public function facades($facades = [])
    {
        $this->facadeStack = $facades;
        spl_autoload_register([$this, 'loadFacade'], true, true);
    }

    /**
     * @param $facade
     * @return bool
     */
    public function loadFacade($facade)
    {
        if (isset($this->facadeStack[$facade])) {
            return class_alias($this->facadeStack[$facade], $facade);
        }
        if (strpos($facade, 'Facades\\') === 0) {
            if (file_exists($path = $this->getPath('storage/cache/').sha1($facade).'.php')) {
                require $path;
                return true;
            }
            $namespace = explode('\\', $facade);
            $name = array_pop($namespace);
            $namespace = implode('\\', $namespace);
            $target = substr($facade, 8);
            $stub = <<<EOF
<?php

namespace $namespace;

use Mild\Supports\Facades\Facade;

class $name extends Facade
{
    protected static function setFacadeRoot()
    {
        return '$target';
    }
}
EOF;
            file_put_contents($path, $stub);
            require $path;
            return true;
        }
        return false;
    }

    /**
     * @param $providers
     */
    public function providers($providers)
    {
        foreach ($providers as $key => $provider) {
            $this->register($provider);
        }
        foreach ($this->providerStack['registered'] as $p) {
            $p->boot();
        }
        $this->booted = true;
    }

    /**
     * @return array
     */
    public function getFacadeStack()
    {
        return $this->facadeStack;
    }

    /**
     * @return array
     */
    public function getProviderStack()
    {
        return $this->providerStack;
    }

    /**
     * @return array
     */
    public function getAliasStack()
    {
        return $this->aliasStack;
    }

    /**
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * @param ReflectionMethod|ReflectionFunction $reflector
     * @param array $parameters
     * @return array
     * @throws \ReflectionException
     */
    public function dependencies($reflector, $parameters = [])
    {
        $index = 0;
        $dependencies = [];
        foreach ($reflector->getParameters() as $parameter) {
            $position = $parameter->getPosition();
            if ($class = $parameter->getClass()) {
                $dependencies[$position] = $this->instance($class);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[$position] = $parameter->getDefaultValue();
            } elseif (isset($parameters[$index])) {
                $dependencies[$position] = $parameters[$index];
                ++$index;
            }
        }
        return $dependencies;
    }

    /**
     * @param $callable
     * @param array $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    public function call($callable, $parameters = [])
    {
        if (is_string($callable) && strpos($callable, '@') !== false) {
            $callable = explode('@', $callable);
            $callable[0] = $this->instance($callable[0]);
        }
        return call_user_func_array($callable, $this->dependencies(is_array($callable) ? new ReflectionMethod($callable[0], $callable[1]) : new ReflectionFunction($callable) , $parameters));
    }

    /**
     * @param $class
     * @param array $parameters
     * @return mixed|object
     * @throws \ReflectionException
     */
    public function instance($class, $parameters = [])
    {
        $class = $this->resolveReflectionClassInstance($class);
        if ($this->has($name = $class->getName())) {
            return $this->get($name);
        }
        return $class->newInstanceArgs(($constructor = $class->getConstructor()) !== null ? $this->dependencies($constructor, $parameters) : $parameters);
    }

    /**
     * @param $class
     * @return ReflectionMethod
     * @throws \ReflectionException
     */
    public function getConstructor($class)
    {
        return $this->resolveReflectionClassInstance($class)->getConstructor();
    }

    /**
     * @param $class
     * @return ReflectionClass
     * @throws \ReflectionException
     */
    protected function resolveReflectionClassInstance($class)
    {
        if ($class instanceof ReflectionClass === false) {
            return new ReflectionClass($class);
        }
        return $class;
    }

    /**
     * @param $id
     * @return bool
     * @throws \ReflectionException
     */
    public function has($id)
    {
        if (isset($this->providerStack['defers'][$id])) {
            $this->register($this->providerStack['defers'][$id], false);
            unset($this->providerStack['defers'][$id]);
        }
        if (isset($this->aliasStack[$id]) && $this->has($this->aliasStack[$id])) {
            $this->bindings[$id] = $this->get($this->aliasStack[$id]);
        }
        return isset($this->bindings[$id]);
    }

    /**
     * @param $id
     * @return mixed|object
     * @throws \ReflectionException
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            return $this->instance($id);
        }
        if (is_string($binding = $this->bindings[$id])) {
            $this->set($id, $binding = $this->instance($binding));
        } elseif ($binding instanceof Closure) {
            $this->set($id, $binding = $binding($this));
        }
        return $binding;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->bindings;
    }

    /**
     * @param $id
     * @param $value
     */
    public function set($id, $value)
    {
        $this->bindings[$id] = $value;
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function alias($key, $value)
    {
        $this->aliasStack[$value] = $key;
    }

    /**
     * @param $id
     * @return void
     */
    public function put($id)
    {
        unset($this->bindings[$id]);
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
     * @return mixed|object
     * @throws \ReflectionException
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
     * @return mixed|object
     * @throws \ReflectionException
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
}
