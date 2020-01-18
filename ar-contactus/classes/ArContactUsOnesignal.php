<?php

/**
 * @property ArContactUsConfigPopup $config
 */
class ArContactUsOnesignal 
{
    protected $config;
    protected $restAPIKey;
    protected $appID;

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    public function __construct($config)
    {
        $this->config = $config;
        $this->restAPIKey = $config->onesignal_api_key;
        $this->appID = $config->onesignal_app_id;
    }
    
    public function sendMessage($contents, $headings, $targetUrl)
    {
        $url = 'https://onesignal.com/api/v1/notifications';
        $data = array(
            'app_id' => $this->appID,
            'included_segments' => 'All',
            'data' => array(
                'key' => 'value'
            ),
            'url' => $targetUrl,
            'contents' => $contents,
            'headings' => $headings
        );
        return $this->sendRequest($url, array(), $data, self::METHOD_POST);
    }
    
    protected function sendRequest($url, $urlParams, $data = array(), $method = self::METHOD_GET)
    {
        $url = $this->buildUrl($url, $urlParams);
        if ($method == self::METHOD_GET) {
            $headers = 'Authorization: Basic ' . $this->restAPIKey . PHP_EOL;
        } elseif ($method == self::METHOD_POST) {
            $headers = array(
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Basic ' . $this->restAPIKey
            );
            $headers = implode(PHP_EOL, $headers) . PHP_EOL;
        }
        
        $context = stream_context_create(array(
            'http' => array(
                'ignore_errors' => true,
                'method' => $method,
                'header' => $headers,
                'content' => $method == self::METHOD_GET? http_build_query($data) : json_encode($data),
            ),
        ));
        if ($res = file_get_contents($url, false, $context)) {
            if ($json = json_decode($res)) {
                return $json;
            }
        }
        var_dump($res);die();
        return false;
    }
    
    protected function buildUrl($baseUrl, $params)
    {
        return strtr($baseUrl, $params);
    }
}
