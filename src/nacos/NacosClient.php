<?php


namespace Alicloud\ConfigMonitor\nacos;


use Exception;
use Alicloud\ConfigMonitor\nacos\listener\config\ListenerConfigRequestListener;
use Alicloud\ConfigMonitor\nacos\util\LogUtil;
use Alicloud\ConfigMonitor\nacos\listener\config\Config;
use Alicloud\ConfigMonitor\nacos\request\config\GetConfigRequest;
use Alicloud\ConfigMonitor\nacos\failover\LocalConfigInfoProcessor;
use Alicloud\ConfigMonitor\nacos\request\config\DeleteConfigRequest;
use Alicloud\ConfigMonitor\nacos\request\config\PublishConfigRequest;
use Alicloud\ConfigMonitor\nacos\request\config\ListenerConfigRequest;
use Alicloud\ConfigMonitor\nacos\listener\config\GetConfigRequestErrorListener;
use Alicloud\ConfigMonitor\nacos\listener\config\ListenerConfigRequestErrorListener;

/**
 * Class NacosClient
 * @author suxiaolin
 * @package Alicloud\ConfigMonitor\nacos
 */
class NacosClient implements NacosClientInterface
{
    public static function listener($env, $dataId, $group, $config, $tenant = "")
    {
        $loop = 0;
        do {
            $loop++;

            $listenerConfigRequest = new ListenerConfigRequest();
            $listenerConfigRequest->setDataId($dataId);
            $listenerConfigRequest->setGroup($group);
            $listenerConfigRequest->setTenant($tenant);
            $listenerConfigRequest->setContentMD5(md5($config));

            try {
                
                $response = $listenerConfigRequest->doRequest();
                if ($response->getBody()->getContents()) {
                    // 配置发生了变化
                    $config = self::get($env, $dataId, $group, $tenant);
                    //LogUtil::info("found changed config: " . $config);
                    if ($config){
                        //通知
                        ListenerConfigRequestListener::notify($config);
    
                        // 保存最新的配置
                        LocalConfigInfoProcessor::saveSnapshot($env, $dataId, $group, $tenant, $config);
                    } else {
                        throw new \Exception('获取远程配置失败,不做任何变更...', 1);
                    }
                } else {
                    LogUtil::info("配置无变化，监听中...\n");
                }
            } catch (Exception $e) {
                LogUtil::error($e->getMessage());
                ListenerConfigRequestErrorListener::notify($env, $dataId, $group, $tenant);
                // 短暂休息会儿
                sleep(4);
            }
            LogUtil::info("listener loop count: " . $loop);
        } while (true);
    }
    
    
    /**
     * 获取单个配置
     * @param $env
     * @param $dataId
     * @param $group
     * @param $tenant
     * @return bool|false|null|string
     */
    public static function get($env, $dataId, $group, $tenant)
    {
        $getConfigRequest = new GetConfigRequest();
        $getConfigRequest->setDataId($dataId);
        $getConfigRequest->setGroup($group);
        $getConfigRequest->setTenant($tenant);
        
        try {
            $response = $getConfigRequest->doRequest();
            $config = $response->getBody()->getContents();
            //LocalConfigInfoProcessor::saveSnapshot($env, $dataId, $group, $tenant, $config);
        } catch (Exception $e) {
          
            LogUtil::error($e->getMessage());
            //获取备份的配置文件
            $config = LocalConfigInfoProcessor::getFailover($env, $dataId, $group, $tenant);
            
            //否则获取快照文件
            $config = $config ? $config : LocalConfigInfoProcessor::getSnapshot($env, $dataId, $group, $tenant);
            
            $configListenerParameter = Config::of($env, $dataId, $group, $tenant, $config);
            
            //通过回调函数决定是否要从备份文件中修改配置。
            GetConfigRequestErrorListener::notify($configListenerParameter);
            if ($configListenerParameter->isChanged()) {
                $config = $configListenerParameter->getConfig();
            }
        }
        return $config ?? false;
    }
    
    /**
     * 发布（未测试）
     * @param        $dataId
     * @param        $group
     * @param        $content
     * @param string $tenant
     * @return bool
     */
    public static function publish($dataId, $group, $content, $tenant = "")
    {
        $publishConfigRequest = new PublishConfigRequest();
        $publishConfigRequest->setDataId($dataId);
        $publishConfigRequest->setGroup($group);
        $publishConfigRequest->setTenant($tenant);
        $publishConfigRequest->setContent($content);

        try {
            $response = $publishConfigRequest->doRequest();
        } catch (Exception $e) {
            return false;
        }
        return $response->getBody()->getContents() == "true";
    }
    
    /**
     * 删除（未测试）
     * @param $dataId
     * @param $group
     * @param $tenant
     * @return bool
     * @throws \ReflectionException
     * @throws exception\RequestUriRequiredException
     * @throws exception\RequestVerbRequiredException
     * @throws exception\ResponseCodeErrorException
     */
    public static function delete($dataId, $group, $tenant)
    {
        $deleteConfigRequest = new DeleteConfigRequest();
        $deleteConfigRequest->setDataId($dataId);
        $deleteConfigRequest->setGroup($group);
        $deleteConfigRequest->setTenant($tenant);

        $response = $deleteConfigRequest->doRequest();
        return $response->getBody()->getContents() == "true";
    }
}
