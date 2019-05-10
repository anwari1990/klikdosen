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

class Attachment
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $file;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var int
     */
    protected $length;
    /**
     * @var string
     */
    protected $encoding;
    /**
     * @var string
     */
    protected $disposition;

    /**
     * Attachment constructor.
     * @param $file
     * @param string $name
     * @param string $type
     * @param string $disposition
     * @param string $encoding
     * @param null $id
     * @throws MailException
     */
    public function __construct($file, $name = '', $type = '', $disposition = 'attachment', $encoding = 'base64', $id = null)
    {
        if (!file_exists($file)) {
            throw new MailException('File ['.$file.'] does not exists');
        }
        $this->file = $file;
        $this->setLength(filesize($file));
        if (empty($name)) {
            $name = basename($file);
        }
        if (empty($type)) {
            $type = mime_content_type($file);
        }
        $this->setName($name);
        $this->setType($type);
        $this->setDisposition($disposition);
        $this->setEncoding($encoding);
        $this->setId($id);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     * @throws MailException
     */
    public function getData()
    {
        $handle = fopen($this->file, 'r');
        $data = Encoder::encode(fread($handle, $this->length), $this->encoding);
        fclose($handle);
        return $data;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
    public function getDisposition()
    {
        return $this->disposition;
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param string $id
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @param string $disposition
     * @return void
     */
    public function setDisposition($disposition)
    {
        $this->disposition = $disposition;
    }

    /**
     * @param $encoding
     * @return void
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * @param int $length
     * @return void
     */
    public function setLength($length)
    {
        $this->length = $length;
    }
}