<?php

class ArContactUsFormHelper
{
    protected $model = null;
    protected $view = 'helper/form.php';


    public function __construct($model) {
        $this->model = $model;
    }
    
    public function render()
    {
        $viewData = $this->model->getFormHelperConfig();
        ob_start();
        extract($viewData);
	include AR_CONTACTUS_PLUGIN_DIR . 'views/' . $this->view;
	$output = ob_get_clean();
	return $output;
    }
    
    public function setView($view)
    {
        $this->view = $view;
    }
}
