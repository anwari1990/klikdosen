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

class SimpleMessage
{
    /**
     * @var Message
     */
    protected $message;

    /**
     * SimpleMessage constructor.
     * @param string $subject
     * @throws MailException
     */
    public function __construct($subject = '')
    {
        $this->message = new Message($subject);
    }

    /**
     * @param $subject
     * @return $this
     */
    public function subject($subject)
    {
        $this->message->setSubject($subject);
        return $this;
    }

    /**
     * @param $email
     * @param string $name
     * @return SimpleMessage
     * @throws MailException
     */
    public function from($email, $name = '')
    {
        $this->message->setFrom($email);
        $email = '<'.$email.'>';
        return $this->header('From', $name ? $name.' '.$email : $email);
    }

    /**
     * @param $email
     * @param string $name
     * @return SimpleMessage
     * @throws MailException
     */
    public function to($email, $name = '')
    {
        $this->message->setTo($email);
        $email = '<'.$email.'>';
        return $this->header('To', $name ? $name.' '.$email : $email);
    }

    /**
     * @param $email
     * @param string $name
     * @return SimpleMessage
     * @throws MailException
     */
    public function cc($email, $name = '')
    {
        $this->message->setCc($email);
        $email = '<'.$email.'>';
        return $this->header('Cc', $name ? $name.' '.$email : $email);
    }

    /**
     * @param $email
     * @param string $name
     * @return SimpleMessage
     * @throws MailException
     */
    public function bcc($email, $name = '')
    {
        $this->message->setBcc($email);
        $email = '<'.$email.'>';
        return $this->header('Bcc', $name ? $name.' '.$email : $email);
    }

    /**
     * @param $email
     * @return SimpleMessage
     * @throws MailException
     */
    public function returnPath($email)
    {
        $this->message->putHeader('Return-Path');
        return $this->header('Return-Path', '<'.$email.'>');
    }

    /**
     * @param $level
     * @return SimpleMessage
     * @throws MailException
     */
    public function priority($level)
    {
        $this->message->putHeader('X-Priority');
        switch ($level) {
            case 1:
                $priority = '1 (Highest)';
                break;
            case 2:
                $priority = '2 (High)';
                break;
            case 3:
                $priority = '3 (Normal)';
                break;
            case 4:
                $priority = '4 (Low)';
                break;
            default:
                $priority = '5 (Lowest)';
                break;
        }
        return $this->header('X-Priority', $priority);
    }

    /**
     * @param $email
     * @param string $name
     * @param bool $in
     * @return SimpleMessage
     * @throws MailException
     */
    public function replyTo($email, $name = '', $in = false)
    {
        $email = '<'.$email.'>';
        return $this->header($in ? 'In-Reply-To' : 'Reply-To', $name ? $name.' '.$email : $email);
    }

    /**
     * @param $email
     * @param string $name
     * @return SimpleMessage
     * @throws MailException
     */
    public function inReplyTo($email, $name = '')
    {
        return $this->replyTo($email, $name, true);
    }

    /**
     * @param $body
     * @param string $type
     * @param string $charset
     * @param string $encoding
     * @return SimpleMessage
     * @throws MailException
     */
    public function body($body, $type = 'text/html', $charset = 'utf-8', $encoding = 'quoted-printable')
    {
        $this->message->setBody(new Body($body, $type, $charset, $encoding));
        return $this;
    }

    /**
     * @param $file
     * @param null $name
     * @param null $type
     * @param string $disposition
     * @param string $encoding
     * @return $this
     * @throws MailException
     */
    public function attach($file, $name = null, $type = null, $disposition = 'attachment', $encoding = 'base64')
    {
        $this->message->setAttachment(new Attachment($file, $name, $type, $disposition, $encoding));
        return $this;
    }

    /**
     * @param $file
     * @param bool $embedData
     * @return string
     * @throws MailException
     */
    public function embed($file, $embedData = false)
    {
        $attachment = new Attachment($file, '', '', 'inline', 'base64');
        if ($embedData === true) {
            return 'data:'.$attachment->getType().';'.$attachment->getEncoding().','.$attachment->getData();
        }
        $this->message->setAttachment($attachment);
        $attachment->setId($id = IdGenerator::generate());
        return 'cid:'.$id;
    }

    /**
     * @param $file
     * @return string
     * @throws MailException
     */
    public function embedData($file)
    {
        return $this->embed($file, true);
    }

    /**
     * @param $key
     * @param $value
     * @param array $parameters
     * @return $this
     * @throws MailException
     */
    public function header($key, $value, $parameters = [])
    {
        foreach ($parameters as $k => $v) {
            $value .= "; {$k}={$v}";
        }
        $this->message->setHeader($key, $value);
        return $this;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}