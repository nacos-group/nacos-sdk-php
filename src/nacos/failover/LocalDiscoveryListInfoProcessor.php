<?php


namespace Alicloud\ConfigMonitor\nacos\failover;


use SplFileInfo;
use Alicloud\ConfigMonitor\nacos\NacosConfig;
use Alicloud\ConfigMonitor\nacos\util\DiscoveryUtil;
use Alicloud\ConfigMonitor\nacos\model\InstanceList;

/**
 * Class LocalDiscoveryListInfoProcessor
 * @package Alicloud\ConfigMonitor\nacos\failover
 */
class LocalDiscoveryListInfoProcessor extends Processor
{
    const DS = DIRECTORY_SEPARATOR;

    public static function getFailover($serviceName, $namespaceId, $clusters)
    {
        $failoverFile = self::getFailoverFile($serviceName, $namespaceId, $clusters);
        if (!is_file($failoverFile)) {
            return null;
        }
        return InstanceList::decode(file_get_contents($failoverFile));
    }

    public static function getFailoverFile($serviceName, $namespaceId, $clusters)
    {
        $failoverFile = NacosConfig::getSnapshotPath() . self::DS . "naming-data"
            . self::DS . DiscoveryUtil::getInstanceListId($serviceName, $namespaceId, $clusters);
        return $failoverFile;
    }

    /**
     * 获取本地缓存文件内容。NULL表示没有本地文件或抛出异常。
     */
    public static function getSnapshot($serviceName, $namespaceId, $clusters)
    {
        $snapshotFile = self::getSnapshotFile($serviceName, $namespaceId, $clusters);
        if (!is_file($snapshotFile)) {
            return null;
        }
        return InstanceList::decode(file_get_contents($snapshotFile));
    }

    public static function getSnapshotFile($serviceName, $namespaceId, $clusters)
    {
        $snapshotFile = NacosConfig::getSnapshotPath() . self::DS . "naming-list-data-snapshot"
            . self::DS . DiscoveryUtil::getInstanceListId($serviceName, $namespaceId, $clusters);
        return $snapshotFile;
    }

    /**
     * @param $serviceName
     * @param $namespaceId
     * @param $clusters
     * @param $instanceList InstanceList
     */
    public static function saveSnapshot($serviceName, $namespaceId, $clusters, $instanceList)
    {
        $snapshotFile = self::getSnapshotFile($serviceName, $namespaceId, $clusters);
        if (!$instanceList) {
            unlink($snapshotFile);
        } else {
            $file = new SplFileInfo($snapshotFile);
            if (!is_dir($file->getPath())) {
                mkdir($file->getPath(), 0777, true);
            }
            file_put_contents($snapshotFile, $instanceList->encode());
        }
    }

}