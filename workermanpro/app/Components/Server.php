<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-12-1
 * Time: 下午5:08
 */

namespace Components;
use \Controllers\TestController;

class Server
{
    protected $worker = null;
    public function __construct($worker)
    {
        $this->worker = $worker;
        $this->initVars();
        $this->bindEvents();
    }
    public function onConnect($connection){}
    public function onMessage($connection, $message) {
        echo "{$message}\n";
        TestController::getInstance()->onMessage($message);
    }
    public function onClose($connection){}
    public function onWorkerStop($connection){
        echo 'server worker stopped.'."\n";
    }
    public function onWorkerStart($worker){
        echo 'server worker started'."\n";
    }
    public function start(){
        echo 'Server started'."\n";
    }
    protected function initVars(){
        $this->worker->count = 1;
        $this->worker->transport = 'udp';
    }
    protected function bindEvents(){
        if($this->worker === null)
            throw  new \Exception('the worker of server is null.');
        $this->worker->onMessage = array($this,'onMessage');
        $this->worker->onConnect = array($this,'onConnect');
        $this->worker->onClose = array($this,'onClose');
        $this->worker->onWorkerStop = array($this,'onWorkerStop');
        //$this->worker->onWorkerStart = array($this,'onWorkerStart');
    }
}