<?php

namespace Alicloud\ConfigMonitor\nacos\request\config;

/**
 * Class DeleteConfigRequest
 * @author suxiaolin
 * @package Alicloud\ConfigMonitor\nacos\request\config
 */
class DeleteConfigRequest extends ConfigRequest
{
    protected $uri = "/nacos/v1/cs/configs";
    protected $verb = "DELETE";
}