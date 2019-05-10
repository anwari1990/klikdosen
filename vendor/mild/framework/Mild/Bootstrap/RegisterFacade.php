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

use Mild\Supports\Facades\Facade;

class RegisterFacade
{
    /**
     * @param \Mild\App $app
     * @throws \ReflectionException
     * @return void
     */
    public function bootstrap($app)
    {
        Facade::setApp($app);
        $app->facades($app->get('config')->get('app.aliases', []));
    }
}