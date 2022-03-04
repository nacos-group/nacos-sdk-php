<?php

namespace Alicloud\ConfigMonitor\nacos\request\config;

/**
 * Class PublishConfigRequest
 * @author suxiaolin
 * @package Alicloud\ConfigMonitor\nacos\request\config
 */
class PublishConfigRequest extends ConfigRequest
{
    protected $uri = "/nacos/v1/cs/configs";
    protected $verb = "POST";

    /**
     * 配置内容
     *
     * @var
     */
    private $content;

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}