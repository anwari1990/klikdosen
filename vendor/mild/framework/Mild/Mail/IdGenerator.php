<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Mail;

class IdGenerator
{
    /**
     * @return string
     */
    public static function generate()
    {
        return str_rand(32).'@'.$_SERVER['SERVER_NAME'];
    }
}