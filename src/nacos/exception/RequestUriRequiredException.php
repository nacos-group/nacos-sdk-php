<?php


namespace Alicloud\ConfigMonitor\nacos\exception;


use Exception;

/**
 * Class RequestUriRequiredException
 * @author suxiaolin
 * @package Alicloud\ConfigMonitor\nacos\exception
 */
class RequestUriRequiredException extends Exception
{
    /**
     * RequestUriRequiredException constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}