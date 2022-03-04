<?php


namespace Alicloud\ConfigMonitor\nacos\listener\config;


use Alicloud\ConfigMonitor\nacos\listener\Listener;

class GetConfigRequestErrorListener extends Listener
{
    /**
     * @var array 观察者数组
     */
    protected static $observers = array();
}