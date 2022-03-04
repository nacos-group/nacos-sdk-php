<?php


namespace Alicloud\ConfigMonitor\nacos;


use Alicloud\ConfigMonitor\nacos\failover\LocalConfigInfoProcessor;
use Alicloud\ConfigMonitor\nacos\util\LogUtil;

/**
 * Class Nacos
 * @author suxiaolin
 * @package Alicloud\ConfigMonitor\nacos
 */
class Nacos
{
    private static $clientClass;

    public static function init($host, $env, $dataId, $group, $tenant)
    {
        static $client;
        if ($client == null) {
            NacosConfig::setHost($host);
            NacosConfig::setEnv($env);
            NacosConfig::setDataId(urlencode($dataId));
            NacosConfig::setGroup(urlencode($group));
            NacosConfig::setTenant(urlencode($tenant));

            if (getenv("NACOS_ENV") == "local") {
                LogUtil::info("nacos run in dummy mode");
                self::$clientClass = DummyNacosClient::class;
            } else {
                self::$clientClass = NacosClient::class;
            }

            $client = new self();
        }
        return $client;
    }

    public function runOnce()
    {
        return call_user_func_array([self::$clientClass, "get"], [NacosConfig::getEnv(), NacosConfig::getDataId(), NacosConfig::getGroup(), NacosConfig::getTenant()]);
    }

    public function listener()
    {
        $currenConf = LocalConfigInfoProcessor::getSnapshot(NacosConfig::getEnv(), NacosConfig::getDataId(), NacosConfig::getGroup(), NacosConfig::getTenant());
        call_user_func_array([self::$clientClass, "listener"], [NacosConfig::getEnv(), NacosConfig::getDataId(), NacosConfig::getGroup(), $currenConf, NacosConfig::getTenant()]);
        return $this;
    }

}