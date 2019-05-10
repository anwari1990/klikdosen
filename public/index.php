<?php
/**
 * Mild Framework (https://github.com/mildphp/mild)
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @since 2018
 * @package Mild Framework
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('ELAPSED', microtime(true));
/**
 * Register Autoloader
 */
require '../vendor/autoload.php';
/**
 * Get application
 */
$app = require_once __DIR__.'/../bootstrap/app.php';
/**
 * Run application
 */
$app->get(App\Http\Kernel::class)->handle(Mild\Http\Request::capture())->send();