<?php

namespace Alicloud\ConfigMonitor\nacos\request\config;

use Alicloud\ConfigMonitor\nacos\NacosConfig;
use Alicloud\ConfigMonitor\nacos\util\LogUtil;
use Alicloud\ConfigMonitor\nacos\request\Request;
use Alicloud\ConfigMonitor\nacos\util\ReflectionUtil;

/**
 * Class ConfigRequest
 * @author suxiaolin
 * @package Alicloud\ConfigMonitor\nacos\request\config
 */
class ConfigRequest extends Request
{
    /**
     * 租户信息，对应 Nacos 的命名空间字段。
     * @var
     */
    private $tenant;

    /**
     * 配置 ID
     * @var
     */
    private $dataId;

    /**
     * 配置分组。
     * @var
     */
    private $group;

    /**
     * @return mixed
     */
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * @param mixed $tenant
     */
    public function setTenant($tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * @return mixed
     */
    public function getDataId()
    {
        return $this->dataId;
    }

    /**
     * @param mixed $dataId
     */
    public function setDataId($dataId)
    {
        $this->dataId = $dataId;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    protected function getParameterAndHeader()
    {
        $timeStamp = round(microtime(true) * 1000);
        $headers = [
            'Diamond-Client-AppName' => 'ACM-SDK-PHP',
            'Client-Version'         => '0.0.1',
            'Content-Type'           => 'application/x-www-form-urlencoded; charset=utf-8',
            'exConfigInfo'           => 'true',
            'Spas-AccessKey'         => NacosConfig::getAk(),
            'timeStamp'              => $timeStamp,
            'Spas-Signature' => $this->_makeSign(NacosConfig::getTenant(), NacosConfig::getGroup(), $timeStamp)
        ];
        $parameterList = [];

        $properties = ReflectionUtil::getProperties($this);
        foreach ($properties as $propertyName => $propertyValue) {
            if (in_array($propertyName, $this->standaloneParameterList)) {
                // 忽略这些参数
            } else if ($propertyName == "longPullingTimeout") {
                $headers["Long-Pulling-Timeout"] = $this->getLongPullingTimeout();
            } else if ($propertyName == "listeningConfigs") {
                $parameterList["Listening-Configs"] = $this->getListeningConfigs();
            } else {
                $parameterList[$propertyName] = $propertyValue;
            }
        }
        unset($parameterList['standaloneParameterList']);
        
        
        if (NacosConfig::getIsDebug()) {
            LogUtil::info(strtr("parameterList: {parameterList}, headers: {headers}", [
                "parameterList" => json_encode($parameterList),
                "headers" => json_encode($headers)
            ]));
        }
        return [$parameterList, $headers];
    }
    
    
    /**
     * make sign
     * @param $nameSpace
     * @param $groupId
     * @param $timeStamp
     * @return string
     */
    protected function _makeSign($nameSpace, $groupId, $timeStamp)
    {
        $signStr = $nameSpace.'+';
    
        if (!empty($groupId)) {
            $signStr .= $groupId."+";
        }
    
        $signStr = $signStr.$timeStamp;
        return base64_encode(hash_hmac(
            'sha1',
            $signStr,
            NacosConfig::getSk(),
            true
        ));
    }
    
}