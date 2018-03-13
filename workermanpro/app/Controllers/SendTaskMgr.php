<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-12-2
 * Time: 下午7:55
 */

namespace Controllers;
use \Components\DataSender;
require_once __DIR__.'/../Components/DataSender.php';
class SendTaskMgr
{
    private function __construct(){
        echo "构造\n";
        $this->_cTasks = SENDTASKMAXNUM;
        $this->createTaskPool();
    }

    public function runAll(){
//        for($i = 0;$i < count($this->_taskPool);$i++){
//            $this->_taskPool[$i]->start();
//        }
    }
    public function addSendTask($taskName,$intelval= P_SENDINTERVAL){
        $sender =  $this->getFreeSender();
        $sender->setInterval($intelval);
        $sender->setTask($taskName);
        $sender->start();

    }
    public function stopTask($task){
        $sender = $this->getSenderByTask($task);
        if($sender === null)
            return;
        $sender->stop();
    }
    protected function & getSenderByTask($task){
        $sender = null;
        for($i = 0; $i < count($this->_taskPool); ++$i){
            $curTask = $this->_taskPool[$i]->getTask();
            echo "curTask:{$curTask}\n";
            if($curTask == $task){
                $sender = &$this->_taskPool[$i];
                break;
            }
        }

        return $sender;
    }
    protected function & getFreeSender(){
        $sender = null;
        for($i = 0; $i < count($this->_taskPool); ++$i){
            if($this->_taskPool[$i]->isFree() === true){
                $sender = &$this->_taskPool[$i];
                break;
            }
        }
        if($sender === null){
            //没有足够的sender满足需求，这有悖于设计上的协议，抛出异常
            echo "异常：即将重启程序，因为没有足够的sender供使用\n";
            throw new \Exception("senderPool have not  sender enough.\n");
        }
        return $sender;
    }
    protected function createTaskPool(){
        for($i = 0;$i < $this->_cTasks;$i++) {
            array_push($this->_taskPool,new DataSender());
        }
    }
    private static $_instance = null;
    private function __clone() {
    }
    static public function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self ();
        }
        return self::$_instance;
    }
    protected $_taskPool = array();
    protected $_cTasks = 0;
}