<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-12-1
 * Time: 下午9:43
 */

namespace Controllers;
use \Components\Client;
use Components\DataProvider;
use Components\DataResolver;
use Components\DataTools;
use \Components\Server;
use system\model\dbhelper;
use Workerman\Lib\Timer;
use \Workerman\Worker;
class TestController
{
    public function onMessage($action,$data,$recvTime = null){
        //echo "收到指定节点的数据！\n";
        switch ($action){
            case T_SENDSYN:
                if($this->_receivedSYN === true)
                    return;
                echo "收到新的SYN请求\n";
                $this->_receivedSYN = true;
                $testKey = substr($data,TIMELEN+ROLEKEY_LEN,TESTKEY_LEN);
                DataProvider::getInstance()->setSYN($testKey);
                echo "开始发送回复ACKSYN\n";
                $this->_sendMgr->addSendTask(T_SENDACKSYN);
                break;
            case T_SENDACKSYN:
                if($this->_receivedSYNACK === true)
                    return;
                echo "收到正确的SYNACK回复\n";
                $this->_receivedSYNACK = true;
                echo "停止发送SYN\n";
                $this->_sendMgr->stopTask(T_SENDSYN);
                echo "开始发送测试数据\n";
                $this->_sendMgr->addSendTask(T_SENDTESTDATA,0.01);
                break;
            case T_SENDACK:
                break;
            case T_SENDTESTDATA:
               if( $this->_receivedTestData  == false){
                   echo "收到测试数据\n";
                   echo "停止发送回复SYNACK\n";
                   $this->_sendMgr->stopTask(T_SENDACKSYN);
                   $this->_receivedTestData = true;
               }
               $sendTime = substr($data,0,TIMELEN);
               $costTime = abs($recvTime - $sendTime);
               //echo $sendTime."\n";
              // echo $recvTime."\n";
               $this->_recvPackages[$sendTime] = $costTime;
               //echo "花销：".$costTime."\n";
               $this->_recvTestDataCount++;
               $this->_recvTestDataLen+=strlen($data);
                break;
            case T_TESTFINISHOK:
                if($this->_receivedFinishOK == false){
                    echo "对方已经将记录存入数据库\n";
                    $this->_receivedFinishOK = true;
                    $this->_sendMgr->stopTask(T_TESTFINISH);
                    $this->_sendMgr->addSendTask(T_DISCONNECT);
                    $this->showReport();
                }
                break;
            case T_TESTFINISH:
                if($this->_receivedFinishData == false){
                    echo "对方已经完成测试!\n";
                    $this->_receivedFinishData = true;
                    $this->onTagNodeTestEnd();
                    $this->_sendMgr->addSendTask(T_TESTFINISHOK);
                }
                break;
            case T_DISCONNECT:
                if($this->_receivedDisconect == false){
                    echo "对方请求断开连接\n";
                    $this->_receivedDisconect = true;
                    $this->_sendMgr->stopTask(T_TESTFINISHOK);
                }
                break;
        }
    }
    public function onSelfTestEnd(){
        echo "测试数据发送完毕\n";
        echo "停止发送测试数据\n";
        $this->_sendMgr->stopTask(T_SENDTESTDATA);
        $packages = DataProvider::getInstance()->getOutPutPackages();
        echo "本次发送数据包".count($packages)."个\n";
        self::saveOutPackages($packages);
        echo "通知目标节点本次测试已完成\n";
        $this->_sendMgr->addSendTask(T_TESTFINISH);
    }
    public function onTagNodeTestEnd(){
        echo "收到对方数据包：".$this->_recvTestDataCount."个，总共 ".$this->_recvTestDataLen." 字节\n";
        self::saveRecvPackages($this->_recvPackages);
    }
    public function setSendMgr($sendMgr){
        $this->_sendMgr = $sendMgr;
    }
    private static function saveOutPackages($packages){
        echo "正在将已发送的所有包信息存入数据库...！\n";
        $con =  dbhelper::getInstance('Tests');
        $testKey = TestController::getInstance()->getTestKey();

        foreach ($packages as $val){
            $sql = "insert into Tests (TestKey,Package,Lost,TransTime) values ('".$testKey."','".$val."',true,0)";
            //echo $sql."\n";
//            $arr = array(
//                "TestKey"    =>$testKey,
//                "Package"    =>$val,
//                "Lost"       =>true,
//                "TransTime"  =>0
//            );
//            $con->add($arr);
            $con->execute($sql);
        }
        echo "已将本次发送的所有包信息存入数据库！\n";
    }
    private static function saveRecvPackages($packages){
        echo "正在将已收到的所有包信息存入数据库...！\n";
        $con =  dbhelper::getInstance('Tests');
        $synKey = $testKey = DataProvider::getInstance()->getSYN();
        foreach ($packages as $key => $val){
            $sql = "update Tests set Lost = false,TransTime = '".$val."' where TestKey = '".$synKey."' and Package = '".$key."'";
            //echo $sql."\n";
            $con->execute($sql);
            // $con->where(array('TestKey'=>$synKey,'Package' => $key))->update(array('Lost' => false,'TransTime' => $val));
        }
        echo "已将已收到的所有包信息存入数据库！\n";
    }
    public function startTest(){
        $this->_testKey = DataTools::create_uuid(TESTSIGNHEADER);
        echo "本次测试任务标识码：".$this->_testKey."\n";
        SendTaskMgr::getInstance()->addSendTask(T_SENDSYN);
    }
    public function getTestKey(){
        return $this->_testKey;
    }
    private static $_instance = null;
    private function __construct() {
            echo "ctrl构造\n";
    }
    private function __clone() {
    }
    static public function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self ();
        }
        return self::$_instance;
    }
    private function showReport(){
        echo "正在做最后的数据整理...\n";

        $pFailed = dbhelper::getInstance('Tests')->where(array('TestKey'=>$this->getTestKey(),'Lost' => true))->select();
        $pSuccessful = dbhelper::getInstance('Tests')->where(array('TestKey'=>$this->getTestKey(),'Lost' => false))->order('TransTime Desc')->select();
        foreach ($pFailed as $arr){
            dbhelper::getInstance('Tests_FAILED')->add($arr);
        }
        foreach ($pSuccessful as $arr){
            dbhelper::getInstance('Tests_SUCCESSFUL')->add($arr);
        }
        $cFailed = count($pFailed);
        $cSuccessful = count($pSuccessful);
        $count = $cFailed+$cSuccessful;
        $maxTime = (int) $pSuccessful[0]['TransTime'];
        $minTime = (int) $pSuccessful[$cSuccessful-1]['TransTime'];
        $aveTime = dbhelper::getInstance('Tests')->field('AVG(TransTime)')->where(array('TestKey'=>$this->getTestKey(),'Lost' => false))->select();
        echo "========================================================================\n";
        echo "                                   测试报告\n";
        echo "========================================================================\n";
        echo "发送数据包数量：".$count."\n";
        echo "成功：".$cSuccessful."  失败：".$cFailed."  丢包率:".($cFailed / $count * 100)."% \n";
        echo "最大延迟:".$maxTime."mms  最小延迟：".$minTime."mms  平均延迟：".$aveTime[0]["AVG(TransTime)"]."mms\n";
    }
    private $_testKey = '';
    private $_receivedSYN = false;
    private $_receivedSYNACK = false;
    private $_receivedTestData = false;
    private $_receivedFinishData = false;
    private $_receivedFinishOK = false;
    private $_receivedDisconect = false;
    private $_sendMgr = null;
    private $_recvTestDataLen = 0;
    private $_recvTestDataCount = 0;
    private $_recvPackages = array();
}