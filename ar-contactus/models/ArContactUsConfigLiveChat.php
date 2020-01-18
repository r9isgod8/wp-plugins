<?php
ArContactUsLoader::loadModel('ArContactUsConfigModel');

class ArContactUsConfigLiveChat extends ArContactUsConfigModel
{
    public $tawk_to_head;
    public $tawk_to_on;
    public $tawk_to_site_id;
    public $tawk_to_widget;
    public $hr1;
    
    public $crisp_head;
    public $crisp_on;
    public $crisp_site_id;
    public $hr2;
    
    public $intercom_head;
    public $intercom_on;
    public $intercom_app_id;
    public $hr3;
    
    public $fb_head;
    public $fb_alert;
    public $fb_on;
    public $fb_page_id;
    public $fb_init;
    public $fb_lang;
    public $fb_color;
    public $hr4;
    
    public $vk_head;
    public $vk_on;
    public $vk_page_id;
    public $hr5;
    
    public $zopim_head;
    public $zopim_on;
    public $zopim_id;
    public $hr6;
    
    public $skype_head;
    public $skype_on;
    public $skype_type;
    public $skype_id;
    public $skype_message_color;
    public $hr7;
    
    public $zalo_head;
    public $zalo_on;
    public $zalo_id;
    public $zalo_welcome;
    public $zalo_width;
    public $zalo_height;
    
    public function getIntegrations()
    {
        $integrations = array();
        if ($this->isTawkToIntegrated()) {
            $integrations['tawkto'] = __('Tawk.to', AR_CONTACTUS_TEXT_DOMAIN);
        }
        if ($this->isCrispIntegrated()) {
            $integrations['crisp'] = __('Crisp', AR_CONTACTUS_TEXT_DOMAIN);
        }
        if ($this->isIntercomIntegrated()) {
            $integrations['intercom'] = __('Intercom', AR_CONTACTUS_TEXT_DOMAIN);
        }
        if ($this->isFacebookChatIntegrated()) {
            $integrations['facebook'] = __('Facebook customer chat', AR_CONTACTUS_TEXT_DOMAIN);
        }
        if ($this->isVkIntegrated()) {
            $integrations['vk'] = __('VK community messages', AR_CONTACTUS_TEXT_DOMAIN);
        }
        if ($this->isZopimIntegrated()) {
            $integrations['zopim'] = __('Zendesk chat', AR_CONTACTUS_TEXT_DOMAIN);
        }
        if ($this->isSkypeIntegrated()) {
            $integrations['skype'] = __('Skype web control', AR_CONTACTUS_TEXT_DOMAIN);
        }
        if ($this->isZaloIntegrated()) {
            $integrations['zalo'] = __('Zalo chat widget', AR_CONTACTUS_TEXT_DOMAIN);
        }
        return $integrations;
    }
    
    public function isFacebookChatIntegrated()
    {
        return $this->fb_on && $this->fb_page_id;
    }
    
    public function isTawkToIntegrated()
    {
        return $this->tawk_to_on && $this->tawk_to_site_id && $this->tawk_to_widget;
    }
    
    public function isCrispIntegrated()
    {
        return $this->crisp_on && $this->crisp_site_id;
    }
    
    public function isIntercomIntegrated()
    {
        return $this->intercom_on && $this->intercom_app_id;
    }
    
    public function isVkIntegrated()
    {
        return $this->vk_on && $this->vk_page_id;
    }
    
    public function isZopimIntegrated()
    {
        return $this->zopim_on && $this->zopim_id;
    }
    
    public function isZendeskChat()
    {
        return strpos($this->zopim_id, '-') !== false;
    }
    
    public function isSkypeIntegrated()
    {
        return $this->skype_on && $this->skype_id;
    }
    
    public function isZaloIntegrated()
    {
        return $this->zalo_on && $this->zalo_id;
    }
    
    public function getFormTitle()
    {
        return __('Live chat integrations', AR_CONTACTUS_TEXT_DOMAIN);
    }
    
    public function attributeDefaults()
    {
        return array(
            'tawk_to_widget' => 'default',
            'zalo_height' => '420',
            'zalo_width' => '350'
        );
    }
}
