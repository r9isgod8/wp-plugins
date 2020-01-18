<?php
ArContactUsLoader::loadModel('ArContactUsConfigModel');

class ArContactUsConfigPopup extends ArContactUsConfigModel
{
    public $timeout;
    public $message;
    public $phone_placeholder;
    public $phone_mask_on;
    public $maskedinput;
    public $phone_mask;
    public $proccess_message;
    public $success_message;
    public $fail_message;
    public $btn_title;
    public $email;
    public $email_list;
    
    public $hr1;
    public $twilio;
    public $twilio_api_key;
    public $twilio_auth_token;
    public $twilio_phone;
    public $twilio_tophone;
    public $twilio_message;
    
    public $hr2;
    public $tg;
    public $tg_token;
    public $tg_chat_id;
    public $tg_text;
    
    public $hr3;
    public $onesignal;
    public $onesignal_alert;
    public $onesignal_app_id;
    public $onesignal_api_key;
    public $onesignal_title;
    public $onesignal_message;
    
    public $hr4;
    public $recaptcha;
    public $key;
    public $secret;
    public $hide_recaptcha;
    
    public function getFormTitle()
    {
        return __('Callback popup settings', AR_CONTACTUS_TEXT_DOMAIN);
    }
    
    public function attributeDefaults()
    {
        return array(
            'timeout' => '0',
            'message' => __("Please enter your phone number\nand we call you back soon", AR_CONTACTUS_TEXT_DOMAIN),
            'phone_placeholder' => __("+XXX-XX-XXX-XX-XX", AR_CONTACTUS_TEXT_DOMAIN),
            'phone_mask' => '+XXX-XX-XXX-XX-XX',
            'proccess_message' => __("We are calling you to phone", AR_CONTACTUS_TEXT_DOMAIN),
            'success_message' => __("Thank you.\nWe are call you back soon.", AR_CONTACTUS_TEXT_DOMAIN),
            'fail_message' => __("Connection error. Please refresh the page and try again.", AR_CONTACTUS_TEXT_DOMAIN),
            'btn_title' => __("Waiting for call", AR_CONTACTUS_TEXT_DOMAIN),
            'tg_text' => __('New callback request from phone: {phone}', AR_CONTACTUS_TEXT_DOMAIN),
            'maskedinput' => 1,
            'email' => 1,
            'email_list' => $this->getAdminEmail(),
            'onesignal' => 0,
            'onesignal_title' => __('New callback request', AR_CONTACTUS_TEXT_DOMAIN),
            'onesignal_message' => __('New callback request received from {site}. Please call to {phone}.', AR_CONTACTUS_TEXT_DOMAIN),
            'recaptcha' => 0,
            'hide_recaptcha' => 1,
            'twilio_message' => __("New callback request received from {phone}", AR_CONTACTUS_TEXT_DOMAIN)
        );
    }
    
    public function getAdminEmail()
    {
        return get_option('admin_email');
    }
}
