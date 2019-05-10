<?php

namespace App\Console;

use Throwable;
use Mild\Bootstrap\HandleError;
use Mild\Bootstrap\RegisterConfig;
use Mild\Bootstrap\RegisterFacade;
use Mild\Handlers\HandlerInterface;
use Mild\Bootstrap\RegisterProvider;
use App\Console\Commands\ClosureCommand;
use Symfony\Component\Console\Application;

class Kernel
{
    /**
     * @var \Mild\App $app
     */
    protected $app;
    /**
     * Register path for load route command
     *
     * @var array
     * @see ClosureCommand
     */
    protected $path = [
        'routes/console.php'
    ];
    /**
     * Register Command
     *
     * @var array
     */
    protected $commands = [
        Commands\PsyCommand::class,
        Commands\ServeCommand::class,
        Commands\OptimizeCommand::class,
        Commands\MakeRuleCommand::class,
        Commands\ViewClearCommand::class,
        Commands\MakeModelCommand::class,
        Commands\RouteListCommand::class,
        Commands\RouteCacheCommand::class,
        Commands\RouteClearCommand::class,
        Commands\CacheClearCommand::class,
        Commands\CacheForgetCommand::class,
        Commands\MakeConsoleCommand::class,
        Commands\MakeHandlerCommand::class,
        Commands\ConfigCacheCommand::class,
        Commands\ConfigClearCommand::class,
        Commands\MakeProviderCommand::class,
        Commands\MakeMiddlewareCommand::class,
        Commands\MakeControllerCommand::class
    ];

    /**
     * Kernel constructor.
     * @param \Mild\App $app
     * @throws \ReflectionException
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @param \Symfony\Component\Console\Input\ArgvInput $input
     * @param \Symfony\Component\Console\Output\ConsoleOutput $output
     * @return void
     */
    public function handle($input, $output)
    {
        try {
            $this->bootstrap();
            $this->loadCommandFromRegisteredPath();
            $console = new Application('Mild Framework', $this->app->getVersion());
            foreach ($this->commands as $command) {
                if (is_string($command)) {
                    $command = $this->app->instance($command);
                }
                $command->setMild($this->app);
                $console->add($command);
            }
            $console->run($input, $output);
        } catch (Throwable $e) {
            $handler = $this->app->get(HandlerInterface::class);
            $handler->report($e);
            $handler->handle($e);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function bootstrap()
    {
        (new HandleError)->bootstrap($this->app);
        (new RegisterConfig)->bootstrap($this->app);
        (new RegisterFacade)->bootstrap($this->app);
        (new RegisterProvider)->bootstrap($this->app);
    }

    /**
     * Add command with a closure command to handle.
     * 
     * @param $name
     * @param callable $callback
     * @return ClosureCommand
     */
    public function command($name, $callback)
    {
        $this->commands[] = $command = new ClosureCommand($name, $callback);
        return $command;
    }

    /**
     * @return \Mild\App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return array
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Load command from registered path on path property
     *
     * @param \Mild\App $app
     * @return void
     */
    protected function loadCommandFromRegisteredPath()
    {
        foreach ($this->path as $path) {
            require $this->app->getPath($path);
        }
    }
}
