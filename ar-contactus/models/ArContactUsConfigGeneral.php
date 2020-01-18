<?php
ArContactUsLoader::loadModel('ArContactUsConfigModel');

class ArContactUsConfigGeneral extends ArContactUsConfigModel
{
    public $mobile;
    public $sandbox;
    public $allowed_ips;
    public $pages;
    
    public function attributeDefaults()
    {
        return array(
            'mobile' => 1,
            'sandbox' => 0,
            'allowed_ips' => $this->getCurrentIP()
        );
    }
    
    public function getFormTitle()
    {
        return __('General settings', AR_CONTACTUS_TEXT_DOMAIN);
    }
}
