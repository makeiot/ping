<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-12-2
 * Time: 下午8:39
 */

namespace Components;




use Controllers\TestController;

class DataProvider
{
    private static $_instance = null;
    private $_sendLen = 0;
    public function getSendData($task){
        $data = '';
        switch ($task){
            case T_NONE:
                //echo "获取NONE数据\n";
                break;
            case T_SENDSYN:
                //echo "获取SYN数据\n";
                $data = $this->getSYNData();
                break;
            case T_SENDACKSYN:
                //echo "获取TEST数据\n";
                $data = $this->getACKSYNData();
                break;
            case T_SENDTESTDATA:
                if($this->_sendLen < TESTDATAMAXLEN - TESTPACKAGELEN) {
                    $data = $this->getTestData();
                    $this->_sendLen += strlen($data);
                }
                else{
                    TestController::getInstance()->onSelfTestEnd();
                }
                break;
            case T_TESTFINISH:
                $data = $this->getTestFinishData();
                break;
            case T_TESTFINISHOK:
                $data = $this->getTestFinishOKData();
                break;
            case T_DISCONNECT:
                $data = $this->getDisconnectData();
                break;
            default:
                break;
        }
        return $data;
    }
    public function setSYN($strSYNTESTKEY){
        $this->_receivedSYNTESTKEY = $strSYNTESTKEY;
    }
    public function getSYN(){
        return $this->_receivedSYNTESTKEY;
    }
    private function getSYNData(){
        //SYN
        //4字节秘钥明文+密文（2字节AtionID+13字节时间戳+8字节身份标识符+41字节测试标志码）
        $actionId = T_SENDSYN;
        $strTime = $this->get_total_millisecond();
        $testKey = TestController::getInstance()->getTestKey();
        $data = $actionId.$strTime.NODE_NAME.$testKey;
        return $this->encodePackage($data);
    }
    private function getACKSYNData(){
        //ACK+SYN
        //4字节秘钥明文+密文（2字节AtionID+13字节时间戳+8字节身份标识符+通过SYN收到的41字节测试标志码）
        $actionId = T_SENDACKSYN;
        $strTime = $this->get_total_millisecond();
        $syn = $this->_receivedSYNTESTKEY;
       // $testKey = TestController::getInstance()->getTestKey();
        $data = $actionId.$syn.$strTime.NODE_NAME;
        return $this->encodePackage($data);
    }
    private function getACKData(){
        //ACK
    }
    private function getTestData(){
        //TEST Data
        //4字节秘钥明文+密文（2字节AtionID+13字节时间戳+8位身份标识符+41字节测试标志码+1396字节填充数据）总计1460字节
        $actionId = T_SENDTESTDATA;
        $testKey = TestController::getInstance()->getTestKey();
        $strTime = $this->get_total_millisecond();
        array_push($this->_putPackages,$strTime);
        $data = $actionId.$strTime.NODE_NAME.$testKey;
        $strFoo = $this->getFooStr(1456-strlen($data));
        //echo strlen($strFoo)."\n";
        //echo strlen($data)."\n";
        $data .= $strFoo;
        //echo strlen($data)."\n";
        $encodeData = $this->encodePackage($data);
        //echo "test data len ".strlen($encodeData)."\n";
        return $encodeData;
    }
    public function getOutPutPackages(){
        return $this->_putPackages;
    }
    public function getTestFinishData(){
        //SYN
        //4字节秘钥明文+密文（2字节AtionID+13字节时间戳+8字节身份标识符+41字节测试标志码）
        $actionId = T_TESTFINISH;
        $strTime = $this->get_total_millisecond();
        $testKey = TestController::getInstance()->getTestKey();
        $data = $actionId.$strTime.NODE_NAME.$testKey;
        return $this->encodePackage($data);
    }
    public function getTestFinishOKData(){
        //4字节秘钥明文+密文（2字节AtionID+13字节时间戳+8字节身份标识符+通过SYN收到的41字节测试标志码）
        $actionId = T_TESTFINISHOK;
        $strTime = $this->get_total_millisecond();
        $syn = $this->_receivedSYNTESTKEY;
        $data = $actionId.$syn.$strTime.NODE_NAME;
        return $this->encodePackage($data);
    }
    public function getDisconnectData(){
        //SYN
        //4字节秘钥明文+密文（2字节AtionID+13字节时间戳+8字节身份标识符+41字节测试标志码）
        $actionId = T_DISCONNECT;
        $strTime = $this->get_total_millisecond();
        $testKey = TestController::getInstance()->getTestKey();
        $data = $actionId.$strTime.NODE_NAME.$testKey;
        return $this->encodePackage($data);
    }
    private function __construct() {

    }
    private function __clone() {
    }
    static public function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self ();
        }
        return self::$_instance;
    }
    private function encodePackage($data){
        $decodeKey = Crypter::get_random();
        $encodeData = Crypter::decrypt($data,$decodeKey);
        $reData = $decodeKey.$encodeData;
        return $reData;
    }
    function get_total_millisecond()
    {
        $time = explode (" ", microtime () );
        $time = $time [1] . ($time [0] * 1000);
        $time2 = explode ( ".", $time );
        $time = $time2 [0];
        $len = strlen($time);
        if($len < 13){
            $c = 13 - $len;
            for($i = 0; $i<$c;$i++){
                $time = $time."0";
            }
        }
        return $time;
    }
    private function getFooStr($len){
        $str = '';
        for($i = 0;$i<$len;++$i)
            $str.='f';
        return $str;
    }
    private $_receivedSYNTESTKEY = '';
    private $_putPackages = array();

}