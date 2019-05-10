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

class Body
{
    /**
     * @var string
     */
    protected $data;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $charset;
    /**
     * @var string
     */
    protected $encoding;

    /**
     * Body constructor.
     * @param string $data
     * @param string $type
     * @param string $charset
     * @param string $encoding
     */
    public function __construct($data, $type ='text/html', $charset = 'utf-8', $encoding = 'quoted-printable')
    {
        $this->data = $data;
        $this->setType($type);
        $this->setCharset($charset);
        $this->setEncoding($encoding);
    }

    /**
     * @return string
     * @throws MailException
     */
    public function getData()
    {
        return Encoder::encode($this->data, $this->encoding);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param $type
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param string $charset
     * @return void
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * @param string $encoding
     * @return void
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }
}