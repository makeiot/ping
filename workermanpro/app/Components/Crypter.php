<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-12-1
 * Time: 下午5:07
 */

namespace Components;


class Crypter
{
    static  public  function encrypt($data, $key)
    {
        return ~$data;
        $key    =   md5($key);
        $x      =   0;
        $len    =   strlen($data);
        $l      =   strlen($key);
        $char   =   '';
        $str    =   '';
        for ($i = 0; $i < $len; $i++)
        {
            if ($x == $l)
            {
                $x = 0;
            }
            $char .= $key{$x};
            $x++;
        }
        for ($i = 0; $i < $len; $i++)
        {
            $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
        }
        return base64_encode($str);
    }


    static public function decrypt($data, $key)
    {
        if($data == null || $data == '')
            return '';
        return ~$data;
        $key = md5($key);
        $x = 0;
        $data = base64_decode($data);
        $len = strlen($data);
        $l = strlen($key);
        $char  = '';
        $str    = '';

        for ($i = 0; $i < $len; $i++)
        {
            if ($x == $l)
            {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        for ($i = 0; $i < $len; $i++)
        {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
            {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            }
            else
            {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return $str;
    }
    static public function get_random($length = CRYPT_LEN) {
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return mt_rand($min, $max);
    }
}