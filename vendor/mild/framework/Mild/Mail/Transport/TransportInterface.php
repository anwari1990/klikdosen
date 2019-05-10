<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Mail\Transport;

interface TransportInterface
{
    /**
     * @param \Mild\Mail\Message $message
     * @return bool
     */
    public function send($message);
}