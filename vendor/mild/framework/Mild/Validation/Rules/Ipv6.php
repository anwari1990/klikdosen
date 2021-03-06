<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Validation\Rules;

class Ipv6 extends Rule
{

    /**
     * @param array $datas
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function passes(array $datas, $attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * @return string
     */
    public function message()
    {
        return 'The %s must be a valid IPv6 address.';
    }
}