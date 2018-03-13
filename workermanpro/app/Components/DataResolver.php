<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-12-3
 * Time: 下午2:47
 */

namespace Components;


use Controllers\TestController;

class DataResolver
{
    static public function onMessage($data,$recvTime){

       $realData = self::encode($data);
       $actionId = (int)substr($realData,0,ACTIONIDLEN);//取出2位actionId
       $otherData = substr($realData,ACTIONIDLEN,strlen($realData)-ACTIONIDLEN);
       //校验数据合法性
       switch ($actionId){
           case T_SENDACKSYN:
           case T_TESTFINISHOK:
               //如果该数据是回复数据，应当检查合法性
               //数据头格式必须是13字节时间戳+8字节身份标识符+41字节测试标志码
               $roleKey = substr($otherData,TESTKEY_LEN+TIMELEN,ROLEKEY_LEN);
               //echo $roleKey."\n";
               //echo TAG_NODE_NAME."\n";
               if(strcmp($roleKey,TAG_NODE_NAME )!= 0 ){
                   echo "收到未知来源回复数据，但该数据的来源不是指定的测试节点！\n";
                   return;
               }
               $testKey = substr($otherData,0,TESTKEY_LEN);
               if(strcmp($testKey,TestController::getInstance()->getTestKey()) != 0 ){
                   echo $actionId."\n";
                   echo "收到：{$testKey}";
                   echo "应是：".TestController::getInstance()->getTestKey()."\n";
                   echo "收到测试节点回复的数据，但该数据的测试码不正确！\n";
                   return;
               }
               break;
           case T_SENDSYN:
               $roleKey = substr($otherData,TIMELEN,ROLEKEY_LEN);
               if(strcmp($roleKey,TAG_NODE_NAME )!= 0 ){
                   echo "收到SYN数据，但该数据来源不是指定节点！\n";
                   return;
               }
               break;
           case T_TESTFINISH:
           case T_DISCONNECT:
           case T_SENDTESTDATA:
                    if($actionId == T_SENDTESTDATA){
                        if(strlen($data) != TESTPACKAGELEN){
                            echo "该测试数据包不完整！\n";
                            return;
                        }
                    }
                   $roleKey = substr($otherData,TIMELEN,ROLEKEY_LEN);
                   if(strcmp($roleKey,TAG_NODE_NAME )!= 0 ){
                       echo "收到TESTFINISH 或 DISCONNECT 数据，但该数据来源不是指定节点！\n";
                       return;
                   }
                   $testKey = substr($otherData,TIMELEN+ROLEKEY_LEN,TESTKEY_LEN);
                   if(strcmp($testKey,DataProvider::getInstance()->getSYN()) != 0 ){
                       echo $actionId."\n";
                       echo "收到：{$testKey}";
                       echo "应是：".DataProvider::getInstance()->getSYN()."\n";
                       echo "收到TESTFINISH 或 DISCONNEC数据，但该数据的测试码不正确！\n";
                       return;
                   }
               break;
       }

       TestController::getInstance()->onMessage($actionId,$otherData,$recvTime);
    }
    static public function encode($data){
        $key = (int)substr($data,0,CRYPT_LEN);
        $data = substr($data,CRYPT_LEN,strlen($data) - CRYPT_LEN);
        return Crypter::decrypt($data,$key);
    }
}