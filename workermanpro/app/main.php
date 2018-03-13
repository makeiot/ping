<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-12-1
 * Time: 下午4:58
 */

use \Workerman\Worker;
use Components\Server;
use Components\Client;
use Controllers\TestController;
use Controllers\SendTaskMgr;
use \Workerman\Lib\Timer;
use system\model\dbhelper;
require_once __DIR__.'/Data/Constants.php';
require_once __DIR__.'/../workerman/Autoloader.php';
require_once __DIR__.'/Controllers/TestController.php';
require_once 'Components/Client.php';
require_once 'Components/Server.php';
require_once 'Components/DataProvider.php';
require_once __DIR__.'/Controllers/SendTaskMgr.php';
require_once __DIR__ . '/../Channel-master/src/Server.php';
require_once __DIR__ . '/../Channel-master/src/Client.php';
require_once __DIR__.'/Libs/dbhelper.php';
$taskWorker = new Worker(NODE_LISTENING_INFO);
$taskWorker->transport = 'udp';
$taskWorker->count = 1;
$senderMgr = SendTaskMgr::getInstance();
$taskCtrl = TestController::getInstance();
$taskCtrl->setSendMgr($senderMgr);
$taskWorker->onWorkerStart = function($task)use($taskCtrl){
    $curTaskId = $task->id;
    switch($curTaskId){
        case 0:
            echo 'connect task start'."\n";
            $taskCtrl->startTest();
            break;
    }
};
$taskWorker->onMessage = function($connection, $message)use($taskCtrl) {
    \Components\DataResolver::onMessage($message,\Components\DataProvider::getInstance()->get_total_millisecond());
    //$taskCtrl->onMessage($message);
};

Worker::runAll();
