<?php
ArContactUsLoader::loadModel('ArContactUsConfigModel');

class ArContactUsConfigButton extends ArContactUsConfigModel
{
    public $mode;
    public $button_icon;
    public $button_color;
    public $button_size;
    public $position;
    public $x_offset;
    public $y_offset;
    public $pulsate_speed;
    public $icon_speed;
    public $text;
    public $drag;
    
    public function getFormTitle()
    {
        return __('Button settings', AR_CONTACTUS_TEXT_DOMAIN);
    }
    
    public function attributeDefaults()
    {
        return array(
            'mode' => 'menu',
            'button_icon' => 'hangouts',
            'button_size' => 'large',
            'button_color' => '008749',
            'position' => 'right',
            'x_offset' => '20',
            'y_offset' => '20',
            'pulsate_speed' => 2000,
            'icon_speed' => 600,
            'text' => __('Contact us', AR_CONTACTUS_TEXT_DOMAIN),
            'drag' => 0,
        );
    }
}
