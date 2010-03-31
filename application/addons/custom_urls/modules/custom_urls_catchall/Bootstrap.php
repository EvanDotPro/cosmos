<?php
class Custom_urls_catchall_Bootstrap extends Cosmos_Bootstrap_Module
{
    protected $_file = __FILE__;
    
    protected function _initCustomUrls()
    {
        $this->bootstrap('FrontController');
        
        Zend_Loader_Autoloader::getInstance()
            ->registerNamespace('Customurl')->registerNamespace('Customurl');

        $plugin = new Customurl_ControllerPlugin();
        $this->getResource('FrontController')->registerPlugin($plugin);
    }
}