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

class Message
{
    /**
     * @var string
     */
    protected $from;
    /**
     * @var string
     */
    protected $subject;
    /**
     * @var string
     */
    protected $boundary;
    /**
     * @var array
     */
    protected $to = [];
    /**
     * @var array
     */
    protected $cc = [];
    /**
     * @var array
     */
    protected $bcc = [];
    /**
     * @var array
     */
    protected $body = [];
    /**
     * @var array
     */
    protected $headers = [];
    /**
     * @var array
     */
    protected $attachment = [];
    /**
     * @var array
     */
    protected $headerPriority = [
        'Return-Path', 'Message-ID', 'Date',
        'Subject', 'From', 'To', 'Cc', 'Bcc',
        'Reply-To', 'In-Reply-To', 'MIME-Version'
    ];
    /**
     * @var array
     */
    protected $headerLine = [
        'From' ,'To', 'Cc', 'Bcc',
        'Reply-To', 'In-Reply-To'
    ];

    /**
     * Message constructor.
     * @param string $subject
     * @throws MailException
     */
    public function __construct($subject = '')
    {
        $this->setSubject($subject);
        $this->setHeader('MIME-Version', '1.0');
        $this->setHeader('Date', date('D, d M Y H:i:s O'));
        $this->setHeader('Message-ID', '<'.IdGenerator::generate().'>');
        $this->setBoundary('mild_'.time().'_'.str_rand(32));
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @return array
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasHeader($key)
    {
        return isset($this->headers[$key]);
    }

    /**
     * @param $key
     * @param array $default
     * @return array
     */
    public function getHeader($key, array $default = [])
    {
        if ($this->hasHeader($key) === false) {
            return $default;
        }
        return $this->headers[$key];
    }

    /**
     * @return array
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @return array
     */
    public function getHeaderLine()
    {
        return $this->headerLine;
    }

    /**
     * @return array
     */
    public function getHeaderPriority()
    {
        return $this->headerPriority;
    }

    /**
     * @param $from
     * @return void
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @param string $boundary
     * @return void
     */
    public function setBoundary($boundary)
    {
        $this->boundary = $boundary;
    }

    /**
     * @param $to
     * @return void
     */
    public function setTo($to)
    {
        $this->to[] = $to;
    }

    /**
     * @param $cc
     * @return void
     */
    public function setCc($cc)
    {
        $this->cc[] = $cc;
    }

    /**
     * @param $bcc
     * @return void
     */
    public function setBcc($bcc)
    {
        $this->bcc[] = $bcc;
    }

    /**
     * @param Body $body
     * @return void
     */
    public function setBody(Body $body)
    {
        $this->body[] = $body;
    }

    /**
     * @param $key
     * @param $value
     * @throws MailException
     */
    public function setHeader($key, $value)
    {
        if (strpos($value, "\n") !== false || strpos($value, "\r") !== false) {
            throw new MailException('The header value can not contain newline characters.');
        }
        $this->headers[$key][] = $value;
    }

    /**
     * @param Attachment $attachment
     * @return void
     */
    public function setAttachment(Attachment $attachment)
    {
        $this->attachment[] = $attachment;
    }

    /**
     * @param $key
     * @return void
     */
    public function putHeader($key)
    {
        unset($this->headers[$key]);
    }

    /**
     * @param array $key
     * @return void
     */
    public function setHeaderLine($key)
    {
        $this->headerLine[] = $key;
    }

    /**
     * @param array $key
     * @return void
     */
    public function setHeaderPriority($key)
    {
        $this->headerPriority[] = $key;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $result = '';
        foreach ($this->headerPriority as $priority) {
            if ($priority === 'Subject') {
                if ($this->subject !== '') {
                    $result .= 'Subject: '.$this->subject."\r\n";
                }
            } else {
                if (($header = $this->getHeader($priority)) === []) {
                    continue;
                }
                if (in_array($priority, $this->headerLine)) {
                    $result .= $priority.': '.implode(', ', $header)."\r\n";
                } else {
                    foreach ($header as $value) {
                        $result .= $priority.': '.$value."\r\n";
                    }
                }
            }
            $this->putHeader($priority);
        }
        $header = '';
        foreach ($this->getHeaders() as $key => $values) {
            foreach ($values as $value) {
                $header .= $key.': '.$value."\r\n";
            }
        }
        $attach = '';
        if (($attachments = $this->getAttachment()) !== []) {
            $result .= "Content-Type: multipart/mixed; boundary={$this->boundary}\r\n{$header}";
            foreach ($attachments as $attachment) {
                $name = $attachment->getName();
                $contentId = '';
                if (($id = $attachment->getId()) !== null) {
                    $contentId = "Content-ID: <{$id}>\r\n";
                }
                $attach .= "\r\n--{$this->boundary}\r\nContent-Type: {$attachment->getType()}; name={$name}\r\nContent-Disposition: {$attachment->getDisposition()}; filename={$name}\r\nContent-Transfer-Encoding: {$attachment->getEncoding()}\r\n{$contentId}\r\n{$attachment->getData()}\r\n";
            }
            $attach .= "\r\n--{$this->boundary}--";
            $header = '';
        }
        $boundary = '';
        $multiBody = false;
        if (count($this->body) > 1) {
            $multiBody = true;
            $boundary = 'mild_'.time().'_'.str_rand(32);
            if ($attach !== '') {
                $result .= "\r\n--{$this->boundary}\r\n";
            }
            $result .= "Content-Type: multipart/alternative; boundary={$boundary}\r\n{$header}";
            $header = '';
        }
        foreach ($this->body as $body) {
            if ($multiBody === true) {
                $result .= "\r\n--{$boundary}\r\n";
            } elseif ($attach !== '') {
                $result .= "\r\n--{$this->boundary}\r\n";
            }
            $result .= "Content-Type: {$body->getType()}; charset={$body->getCharset()}\r\nContent-Transfer-Encoding: {$body->getEncoding()}\r\n{$header}\r\n{$body->getData()}";
            if ($multiBody === true) {
                $result .= "\r\n";
            }
        }
        if ($multiBody === true) {
            $result .= "\r\n--{$boundary}--";
        }
        if ($attach !== '') {
            if (!empty($this->body)) {
                $attach = "\r\n{$attach}";
            }
            $result .= $attach;
        }
        return $result;
    }
}
