<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-12-2
 * Time: 下午8:57
 */

namespace Components;


class DataTools
{
    public static function create_uuid($prefix = ""){
        $str = md5(uniqid(mt_rand(), true));
        $uuid  = substr($str,0,8) . '-';
        $uuid .= substr($str,8,4) . '-';
        $uuid .= substr($str,12,4) . '-';
        $uuid .= substr($str,16,4) . '-';
        $uuid .= substr($str,20,12);
        return $prefix . $uuid;
    }

}