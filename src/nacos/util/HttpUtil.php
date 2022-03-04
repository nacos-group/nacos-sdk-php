<?php


namespace Alicloud\ConfigMonitor\nacos\util;


use GuzzleHttp\Client;
use Alicloud\ConfigMonitor\nacos\NacosConfig;

/**
 * Class HttpUtil
 * @author suxiaolin
 * @package Alicloud\ConfigMonitor\nacos\util
 */
class HttpUtil
{
    public static function request($verb, $uri, $body = [], $headers = [], $options = [])
    {
        $httpClient = self::getGuzzle();
        $parameterList = [
            'headers' => $headers,
        ];
        if ($verb == "GET") {
            $parameterList['query'] = $body;
        } else {
            $parameterList['form_params'] = $body;
        }
        $response = $httpClient->request($verb, $uri, array_merge($parameterList, $options));
        return $response;
    }

    /**
     * @param $host
     * @param $timeout
     * @return Client
     */
    public static function getGuzzle()
    {
        static $guzzle;
        if ($guzzle == null) {
            $guzzle = new Client([
                'base_uri' => NacosConfig::getHost(),
                'verify' => false,
            ]);
        }
        return $guzzle;
    }
}