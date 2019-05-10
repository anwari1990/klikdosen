<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Bootstrap;

use Exception;
use ErrorException;
use Mild\Handlers\HandlerInterface;

class HandleError
{
    /**
     * @var \Mild\App
     */
    protected $app;

    /**
     * @param \Mild\App $app
     */
    public function bootstrap($app)
    {
        $this->app = $app;
        set_error_handler([$this, 'error']);
        set_exception_handler([$this, 'exception']);
    }

    /**
     * @param $level
     * @param $message
     * @param string $file
     * @param int $line
     * @param array $context
     * @throws ErrorException
     */
    public function error($level, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * @param \Throwable $e
     * @throws \ReflectionException
     */
    public function exception($e)
    {
        try {
            $request = $this->app->get('request');
        } catch (Exception $e) {
            $request = null;
        }
        $handler = $this->app->get(HandlerInterface::class);
        try {
            $handler->report($e);
        } catch (Exception $e) {

        }
        $response = $handler->handle($e, $request);
        if ($request !== null) {
            $response->send();
        }
    }
}