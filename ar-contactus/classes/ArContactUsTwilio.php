<?php

class ArContactUsTwilio
{
    protected $apiKey;
    protected $authToken;
    protected $phone;
    
    const URL = 'https://api.twilio.com/2010-04-01/Accounts/{apikey}/Messages.json';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    
    public function __construct($apiKey, $authToken) {
        $this->apiKey = $apiKey;
        $this->authToken = $authToken;
    }
    
    public function sendSMS($body, $fromPhone, $toPhone)
    {
        return $this->sendRequest(self::URL, array(
            '{apikey}' => $this->apiKey
        ), array(
            'From' => $fromPhone,
            'To' => $toPhone,
            'Body' => $body
        ), self::METHOD_POST);
    }
    
    protected function sendRequest($url, $urlParams, $data = array(), $method = self::METHOD_GET)
    {
        $url = $this->buildUrl($url, $urlParams);
        $auth = base64_encode("{$this->apiKey}:{$this->authToken}");
        if($method == self::METHOD_POST){
            $headers = array(
                'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                'Authorization: Basic ' . $auth
            );
            $headers = implode(PHP_EOL, $headers) . PHP_EOL;
        }
        
        $context = stream_context_create(array(
            'http' => array(
                'header' => $headers,
                'method' => $method,
                'content' => $method == self::METHOD_GET? http_build_query($data) : http_build_query($data),
            ),
        ));
        if ($res = file_get_contents($url, false, $context)) {
            if ($json = json_decode($res)) {
                return $json;
            }
        }
        return false;
    }
    
    protected function buildUrl($baseUrl, $params)
    {
        return strtr($baseUrl, $params);
    }
}
