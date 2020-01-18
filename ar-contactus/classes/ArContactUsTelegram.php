<?php
class ArContactUsTelegram {
    protected $token;
    protected $chat_id;


    public function __construct($token, $chat_id)
    {
        $this->token = $token;
        $this->chat_id = $chat_id;
    }
    
    public function send($message)
    {
        $url = 'https://api.telegram.org/bot'. $this->token .'/sendMessage?chat_id='. $this->chat_id .'&text=' . urlencode($message);
        return file_get_contents($url);
    }
}
