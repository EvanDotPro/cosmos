<?php
class Custom_urls_catchall_Bootstrap extends Cosmos_Bootstrap_Module
{
    protected function _initCustomUrls()
    {
        $this->bootstrap('frontController');

        Zend_Loader_Autoloader::getInstance()
            ->registerNamespace('Customurl')->registerNamespace('Customurl');

        $plugin = new Customurl_ControllerPlugin();
        $this->getResource('frontController')->registerPlugin($plugin);
    }
}