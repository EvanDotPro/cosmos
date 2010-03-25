<?php
class Storefront_SamplePluginController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->view->content = file_get_contents(APPLICATION_PATH . '/data/content.txt');
    }
    
}