<?php
class ClientBootstrap extends Cosmos_Bootstrap
{
    protected function _initLog()
    {
        $this->initLog();
    }
    
    protected function _initCosmosOptions()
    {
        $options = $this->getOptions();
        Zend_Registry::set('options', new Zend_Config($options['cosmos']));
    }
    
    protected function _initDb()
    {
        $this->initDatabase();
    }
    
    protected function _initClientSession()
    {
        $this->bootstrap('session');
        if(!$namespace = 'cosmos_'.Zend_Registry::get('options')->sharedsession->group){
            $namespace = 'cosmos_'.Zend_Registry::get('options')->store->id;
        }
        Zend_Registry::set('csession', new Zend_Session_Namespace("cosmos_{$namespace}"));
        Cosmos_Sso::initiate($namespace);
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
    
    /**
     * Instantiates the Cosmos addon loader. 
     * NOTE: This must be ran _AFTER_ the API client is set up.
     * 
     * @return void
     */
    protected function _initAddons()
    {
        Cosmos_Addon::getInstance();
    }

    
//    protected function _initZFDebug()
//    {
//        if ($this->hasOption('zfdebug'))
//        {
//            $autoloader = Zend_Loader_Autoloader::getInstance();
//            $autoloader->registerNamespace('ZFDebug');
//            $this->bootstrap('FrontController');
//            $zfdebug = new ZFDebug_Controller_Plugin_Debug($this->getOption('zfdebug'));
//            $this->getResource('FrontController')->registerPlugin($zfdebug);
//        }
//    }
}