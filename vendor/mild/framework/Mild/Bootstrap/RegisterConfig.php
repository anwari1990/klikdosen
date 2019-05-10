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

use Mild\Config;
use Carbon\Carbon;

class RegisterConfig
{

    /**
     * @param \Mild\App $app
     * @return void
     */
    public function bootstrap($app)
    {
        if (file_exists($cached = $app->getConfigCachePath())) {
            $items = require $cached;
        } else {
            $items = $this->load($app->getConfigPath());
        }
        $app->set('config', $config = new Config($items));
        date_default_timezone_set($config->get('app.timezone', 'Asia/Jakarta'));
        mb_internal_encoding('UTF-8');
        Carbon::setLocale($config->get('app.locale', 'id'));
    }

    /**
     * @param $path
     * @return array
     */
    protected function load($path)
    {
        $items = [];
        $handler = opendir($path);
        while (false !== ($file = readdir($handler))) {
            if ($file !== '.' && $file !== '..') {
                $key = basename($file, '.php');
                $file = $path.'/'.$file;
                if (is_dir($file)) {
                    $items[$key] = $this->load($file);
                } else {
                    $items[$key] = require $file;
                }
            }
        }
        closedir($handler);
        return $items;
    }
}