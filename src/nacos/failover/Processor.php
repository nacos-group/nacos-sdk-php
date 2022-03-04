<?php


namespace Alicloud\ConfigMonitor\nacos\failover;


use Alicloud\ConfigMonitor\nacos\NacosConfig;
use Alicloud\ConfigMonitor\nacos\util\FileUtil;

class Processor
{
    const DS = DIRECTORY_SEPARATOR;

    /**
     * 清除snapshot目录下所有缓存文件。
     */
    public static function cleanAllSnapshot()
    {
        FileUtil::deleteAll(NacosConfig::getSnapshotPath());
    }

    public static function cleanEnvSnapshot($envName)
    {
        $envSnapshotPath = NacosConfig::getSnapshotPath() . self::DS . $envName . "_nacos" . self::DS;
        FileUtil::deleteAll($envSnapshotPath);
    }

}