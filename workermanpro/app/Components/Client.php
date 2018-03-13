<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-12-1
 * Time: 下午5:07
 */

namespace Components;
use \workerman\Lib\Timer;

class Client
{
    private static $_instance = null;
    private static $_handle = null;
    private function __construct() {

    }
    private function __clone() {
    }
    static public function getInstance() {
        if (is_null ( self::$_instance ) || isset ( self::$_instance )) {
            self::$_instance = new self ();
            self::$_handle = stream_socket_client(TAGNODE_LISTENING_INFO, $errno, $errstr);
        }
        return self::$_instance;
    }
    public function send($data){
        if(self::$_handle === null){
            return false;
        }
        return fwrite(Client::$_handle, $data);
    }
    
}