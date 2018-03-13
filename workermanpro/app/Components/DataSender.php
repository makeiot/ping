<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-12-2
 * Time: 下午6:29
 */

namespace Components;
use \Workerman\Lib\Timer;
class DataSender
{
    public function __construct($interval = P_SENDINTERVAL)
    {
        $this->_interval = $interval;
    }
    public function setTask($task){
        $this->_task = $task;
        echo "task:{$this->_task}\n";
    }
    public function setInterval($interval){
        if($this->timer != null){
            $this->stop();
        }
        $this->_interval = $interval;
    }
    public function start(){
        echo "start\n";
        $task = $this->_task;
        echo "任务{$this->_task}已启动";
        $this->timer = Timer::add($this->_interval,function()use($task){
            $data  = DataProvider::getInstance()->getSendData($task);
            if($data != ''){
               // echo "任务{$task}的数据正在发送\n";
                Client::getInstance()->send($data);
            }
        });
    }
    public function stop(){
        if($this->timer != null){
            Timer::del($this->timer);
        }
        echo "任务{$this->_task}已停止\n";
        $this->_task = T_NONE;
        $this->timer = null;
        $this->_interval = P_SENDINTERVAL;//恢复任务间隔为默认值
    }
    public function getTask(){
        return $this->_task;
    }
    public function isFree(){
        return $this->_task === T_NONE  ? true : false;
    }
    protected $_interval = P_SENDINTERVAL;
    protected $_task = T_NONE;
    protected $timer = null;
}