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

class Mailer
{
    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * Mailer constructor.
     * @param Transport\TransportInterface $transport
     */
    public function __construct($transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param callable $callback
     * @return bool
     * @throws MailException
     */
    public function send(callable $callback)
    {
        $callback($message = new SimpleMessage);
        return $this->transport->send($message->getMessage());
    }

    /**
     * @return Transport\TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }
}