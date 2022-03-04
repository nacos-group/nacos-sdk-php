<?php
/**
 * Created by PhpStorm.
 * User: huangwh
 * Date: 2021/12/30
 * Time: 17:29
 */

require '../vendor/autoload.php';;
use Alicloud\ConfigMonitor\MonitorHandle;




//这5项配置可以写死在 config 配置文件中，AK,SK设置在环境变量中
//$host = 'http://mse-8451d3c6-p.nacos-ans.mse.aliyuncs.com:8848/';
$host = 'http://127.0.0.1:8848/';
$dataId = 'project_name';                                           //配置项ID，对于laravel项目，一个项目就是一个ID
//$nameSpaceId = 'cbcae527-ed47-4732-95f8-c06d8906dcba';      //命名空间ID
$nameSpaceId = '';      //命名空间ID
$group = 'dev';                                            //配置分组，无分组时统一固定值
$evn = 'production';                                        // APP环境标识 正式环境固定为 production

//以下两个是实例启动时传入
$envFile = './.env';                                        //laravel .env 文件路径
$snapshotPath = __DIR__;                                    // 配置文件快照，laraval项目建议放在 storage_path('mse') 目录中
try {
    $monitoryHandle = new MonitorHandle($snapshotPath, $envFile, 30);
    $monitoryHandle->listenNotify();  //开启监控
    
} catch(\Exception $err) {
    $errorinfo = $err->getMessage();
    echo $errorinfo,PHP_EOL;
}




