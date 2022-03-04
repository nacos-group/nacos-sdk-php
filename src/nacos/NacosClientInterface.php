<?php


namespace Alicloud\ConfigMonitor\nacos;


use ReflectionException;
use Alicloud\ConfigMonitor\nacos\exception\ResponseCodeErrorException;
use Alicloud\ConfigMonitor\nacos\exception\RequestUriRequiredException;
use Alicloud\ConfigMonitor\nacos\exception\RequestVerbRequiredException;

/**
 * Class NacosClientInterface
 * @author suxiaolin
 * @package Alicloud\ConfigMonitor\nacos
 */
interface NacosClientInterface
{
    /**
     * @param $env
     * @param $dataId
     * @param $group
     * @param $tenant
     * @return false|string|null
     */
    public static function get($env, $dataId, $group, $tenant);

    /**
     * @param $env
     * @param $dataId
     * @param $group
     * @param $config
     * @param string $tenant
     */
    public static function listener($env, $dataId, $group, $config, $tenant = "");

    /**
     * @param $dataId
     * @param $group
     * @param $content
     * @param string $tenant
     * @return bool
     */
    public static function publish($dataId, $group, $content, $tenant = "");

    /**
     * @param $dataId
     * @param $group
     * @param $tenant
     * @return bool true 删除成功
     * @throws ReflectionException
     * @throws RequestUriRequiredException
     * @throws RequestVerbRequiredException
     * @throws ResponseCodeErrorException
     */
    public static function delete($dataId, $group, $tenant);
}