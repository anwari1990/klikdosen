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

class Encoder
{
    /**
     * @param $string
     * @param $type
     * @return string
     * @throws MailException
     */
    public static function encode($string, $type)
    {
        switch (strtolower($type)) {
            case 'quoted-printable':
                $string = quoted_printable_encode($string);
                break;
            case 'base64':
                $string = chunk_split(base64_encode($string));
                break;
            default:
                throw new MailException('Unsupported ['.$type.'] encoding.');
                break;
        }
        return trim($string);
    }
}