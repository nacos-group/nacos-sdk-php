<?php


namespace Alicloud\ConfigMonitor\nacos\util;


/**
 * Class EncodeUtil
 * @author suxiaolin
 * @package Alicloud\ConfigMonitor\nacos\util
 */
class EncodeUtil
{
    public static function twoEncode()
    {
        return pack("C*", 2);
    }

    public static function oneEncode()
    {
        return pack("C*", 1);
    }
}