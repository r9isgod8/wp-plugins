<?php

abstract class ArContractUsControllerAbstract
{
    public function init()
    {
        $this->registerAjaxActions();
        $this->registerNoprivAjaxActions();
    }
    
    protected function registerAjaxActions()
    {
        foreach ($this->ajaxActions() as $action => $method){
            add_action('wp_ajax_' . $action, array($this, $method));
        }
    }
    
    protected function registerNoprivAjaxActions()
    {
        foreach ($this->ajaxNoprivActions() as $action => $method){
            add_action('wp_ajax_nopriv_' . $action, array($this, $method));
        }
    }
    
    protected function ajaxNoprivActions()
    {
        return array();
    }
    
    protected function ajaxActions()
    {
        return array();
    }
    
    public function render($view, $data)
    {
        return ArContactUsAdmin::render($view, $data);
    }
    
    public function returnJson($data)
    {
        return json_encode($data);
    }
}
