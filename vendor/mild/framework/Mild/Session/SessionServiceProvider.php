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
use Mild\Supports\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->set('session', function ($app) {
            $config = $app->get('config')->get('session');
            switch ($config['default']) {
                case 'file':
                    $handler = new FileSessionHandler($config['drivers'][$config['default']]);
                    break;
                case 'cache':
                    $handler = new CacheSessionHandler($app->get('cache'), $config['drivers'][$config['default']]);
                    break;
                case 'cookie':
                    $handler = new CookieSessionHandler($app->get('cookie'), $app->get('request'), $config['drivers'][$config['default']]);
                    break;
                case 'database':
                    $handler = new DatabaseSessionHandler($this->app->get('db'), $config['drivers'][$config['default']]);
                    break;
                default:
                    throw new Exception('Unsupported session driver ['.$config['default'].']');
                    break;
            }
            return new SessionManager($config['name'], $handler);
        });
        $this->app->set('flash', function ($app) {
            return new Flash($app->get('session'));
        });
    }
}

