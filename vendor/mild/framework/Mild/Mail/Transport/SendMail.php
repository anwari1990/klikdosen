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

class SendMail implements TransportInterface
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * SendMail constructor.
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @param \Mild\Mail\Message $message
     * @return bool
     */
    public function send($message)
    {
        return mail(implode(', ', array_merge($message->getTo(), $message->getCc(), $message->getBcc())), $message->getSubject(), '', str_replace("\r\n\r\n", "\r\n", $message->__toString()), implode(' ', $this->parameters));
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}