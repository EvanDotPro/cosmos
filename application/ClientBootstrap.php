<?php
class ClientBootstrap extends Cosmos_Bootstrap
{
    protected function _initCoreTranslations()
    {
        // @todo: auto-detect and/or make the locale dynamic
        $adapter = new Zend_Translate('csv', APPLICATION_PATH . '/core/etc/languages', 'en_US');
        Zend_Registry::set('Zend_Translate', $adapter);
    }

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
        if (!$namespace = 'cosmos_'.Zend_Registry::get('options')->sharedsession->group) {
            $namespace = 'cosmos_'.Zend_Registry::get('options')->store->id;
        }
        Zend_Registry::set('csession', new Zend_Session_Namespace("cosmos_{$namespace}"));
        Cosmos_Sso::initiate($namespace);
    }

    protected function _initApiClient()
    {
        $options = $this->getOptions();
        try {
    		$client = new Cosmos_Api_Client($options['cosmos']['api']);
		    Zend_Registry::set('api',$client->getProxy());
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
}