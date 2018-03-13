<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-12-1
 * Time: 下午5:09
 */

//8字节节点名,用于节点间身份验证
const NODE_NAME = '&^%&DFT$';
const TAG_NODE_NAME = 'ASD@#C*&';
//本节点的监听信息
const NODE_LISTENING_INFO = "Text://0.0.0.0:2222";
//目标节点的节点监听信息
const TAGNODE_LISTENING_INFO = "udp://192.168.191.3:8888";
//身份标识码长度
const ROLEKEY_LEN = 8;
//测试标识码长度
const TESTKEY_LEN = 41;
//加密key位数
const CRYPT_LEN = 4;
//测试数据最大发送字节
const TESTDATAMAXLEN = 2097152;//2M
//测试包单个字节数
const TESTPACKAGELEN = 1460;
//时间戳位数
const TIMELEN = 13;
//actionId位数
const ACTIONIDLEN = 2;
//身份标识
const SERVER_ID = 0;
const SENDTASK_ID = 1;
const CONTROLLER_ID = 2;

//身份标识
const CLIENT = 0;
const SERVER = 1;
const NONE   = 2;
//状态机状态

const S_FREE        =   0;
const S_CHECKROLE   =   1;

//业务连接协议发送周期（单位秒）
const P_SENDINTERVAL = 2;
//状态机参数
const SM_UPDATEINTERVAL  = 0.001;//状态机刷新间隔1ms

//同时发送数据任务的最大数量
const SENDTASKMAXNUM = 4;
//测试标识前缀
const TESTSIGNHEADER = "TEST_";
//任务标识

const T_NONE = 10;
const T_SENDSYN = 20;
const T_SENDACKSYN = 30;
const T_SENDACK = 40;
const T_SENDTESTDATA = 50;
const T_TESTFINISH = 60;
const T_TESTFINISHOK = 70;
const T_DISCONNECT = 80;