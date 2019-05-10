<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Database;

use Mild\Supports\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->set('db', function ($app) {
            return new Database($app->get('config')->get('database'));
        });
    }

    /**
     * @return void
     */
    public function boot()
    {
        Model::setApp($this->app);
        Pagination::setCurrentPathResolver([$this, 'currentPathResolver']);
        Pagination::setCurrentPageResolver([$this, 'currentPageResolver']);
    }

    /**
     * @return string
     */
    public function currentPathResolver()
    {
        return $this->app->get('request')->getUri()->__toString();
    }

    /**
     * @param $pageName
     * @return int
     * @throws \ReflectionException
     */
    public function currentPageResolver($pageName)
    {
        $page = $this->app->get('request')->getQueryParam($pageName, 1);
        if (filter_var($page, FILTER_VALIDATE_INT) !== false && $page >= 1) {
            return $page;
        }
        return 1;
    }
}
