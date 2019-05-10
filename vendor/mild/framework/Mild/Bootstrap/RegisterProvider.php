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

class RegisterProvider
{

    /**
     * Register Service Providers
     *
     * @param \Mild\App $app
     * @throws \ReflectionException
     * @return void
     */
    public function bootstrap($app)
    {
        return $app->providers($app->get('config')->get('app.providers', []));
    }
}