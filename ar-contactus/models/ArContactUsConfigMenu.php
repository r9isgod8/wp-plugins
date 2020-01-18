<?php
ArContactUsLoader::loadModel('ArContactUsConfigModel');

class ArContactUsConfigMenu extends ArContactUsConfigModel
{
    public $menu_size;
    public $menu_bg;
    public $menu_color;
    public $menu_hbg;
    public $menu_hcolor;
    
    public function getFormTitle()
    {
        return __('Menu settings', AR_CONTACTUS_TEXT_DOMAIN);
    }
    
    public function attributeDefaults()
    {
        return array(
            'menu_size' => 'large',
            'menu_bg' => 'ffffff',
            'menu_color' => '3b3b3b',
            'menu_hbg' => 'f0f0f0',
            'menu_hcolor' => '3b3b3b',
        );
    }
}
