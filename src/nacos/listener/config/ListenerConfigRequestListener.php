<?php


namespace Alicloud\ConfigMonitor\nacos\listener\config;


use Alicloud\ConfigMonitor\nacos\listener\Listener;

/**
 * 正常情况下的通知 
 * Class ListenerConfigRequestListener
 * @package Alicloud\ConfigMonitor\nacos\listener\config
 */
class ListenerConfigRequestListener extends Listener
{
    /**
     * @var array 观察者数组
     */
    protected static $observers = array();
}