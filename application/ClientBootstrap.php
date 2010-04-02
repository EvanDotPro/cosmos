<?php
class ClientBootstrap extends Cosmos_Bootstrap
{
    protected function _initCoreTranslations()
    {
        // @todo: auto-detect and/or make the locale dynamic
        $adapter = new Zend_Translate('csv', APPLICATION_PATH . '/core/etc/languages', 'en_US');
        Zend_Registry::set('Zend_Translate', $adapter);
    }

    protected function _initLogger()
    {
        $this->initLog();
    }

    protected function _initCosmosOptions()
    {
        $options = $this->getOptions();
        Zend_Registry::set('options', new Zend_Config($options['cosmos']));
    }

    protected function _initDatabaseConnection()
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

    protected function _initStoreRoutes()
    {
        $request = new Zend_Controller_Request_Http();

        $chainedRoute = new Zend_Controller_Router_Route_Chain();
        $path = explode('/',substr($request->getPathInfo(),1));
        $requestedPath = array_shift($path);
        $requestedHost = $request->getHttpHost();

        $hostnameRoute = new Zend_Controller_Router_Route_Hostname($requestedHost,
                                array('controller'=>'index', 'action'=>'index'));

        $chainedRoute->chain($hostnameRoute);

        if($store = Cosmos_Api::get()->cosmos->getStoreByHostPath($requestedHost, $requestedPath)){
            if($store['path']){
                $pathRoute = new Zend_Controller_Router_Route($store['path'],array('controller'=>'index', 'action'=>'index'));
            } else {
                $pathRoute = new Zend_Controller_Router_Route_Static('');
            }
            $chainedRoute->chain($pathRoute);
        } else {
            // no matching store?
        }
        // Cleaner way to do this than Zend_Registry?
        Zend_Registry::set('masterRoute', $chainedRoute);
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