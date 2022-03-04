<?php


namespace Alicloud\ConfigMonitor\nacos\exception;


use Exception;

/**
 * Class RequestVerbRequiredException
 * @author suxiaolin
 * @package Alicloud\ConfigMonitor\nacos\exception
 */
class RequestVerbRequiredException extends Exception
{
    /**
     * RequestVerbRequiredException constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}