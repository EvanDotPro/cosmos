<?php
class ClientBootstrap extends Cosmos_Bootstrap
{
    protected function _initDatabase()
    {
        $this->initDatabase();
    }
    
    protected function _initLog()
    {
        $this->initLog();
    }
    
    protected function _initClientSession()
    {
        $this->bootstrap('session');
        $options = $this->getOptions();
        
        Cosmos_Sso::initiate($options['cosmos']['namespace']);
    }
    
    protected function _initTheme()
    {
        $options = $this->getOptions();
        defined('COSMOS_THEME') 
            || define('COSMOS_THEME',
			(isset($options['cosmos']['theme']) ? $options['cosmos']['theme']
											: 'default'));
    }
    
    protected function _initApiClient()
    {
        $options = $this->getOptions();
        try {
//            if(Zend_Registry::get('csession')->apiToken){
        		$client = new Cosmos_Api_Client($options['cosmos']['api']);
    		    Zend_Registry::set('api',$client->getProxy());
//            }
		} catch (Exception $e){
		    Zend_Registry::get('log')->err($e);
		}
    }
}