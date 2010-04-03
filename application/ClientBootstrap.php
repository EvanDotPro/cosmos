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
        $this->bootstrap('frontcontroller');
        $request = new Zend_Controller_Request_Http();
        $path = explode('/',trim($request->getPathInfo(),'/'));
        $requestedPath = array_shift($path);
        $requestedHost = $request->getHttpHost();
        $routeConfig = array();
//        $routeConfig['defaultmodule']['type'] = 'Zend_Controller_Router_Route_Module';
        $routeConfig['defaultmodule']['type'] = 'Zend_Controller_Router_Route_Hostname';
        $routeConfig['defaultmodule']['route'] = $requestedHost;
        if($store = Cosmos_Api::get()->cosmos->getStoreByHostPath($requestedHost, $requestedPath)){
            if($store['path']){
                $routeConfig['defaultroute']['type'] = 'Zend_Controller_Router_Route_Module';
                $routeConfig['pathroute']['type'] = 'Zend_Controller_Router_Route';
                $routeConfig['pathroute']['route'] = $store['path'];
                $routeConfig['pathroute']['defaults']['controller'] = 'index';
                $routeConfig['pathroute']['defaults']['action'] = 'index';
            } else {
//                $routeConfig['defaultmodule']['type'] = 'Zend_Controller_Router_Route_Module';

                $routeConfig['hostroute']['type'] = 'Zend_Controller_Router_Route_Hostname';
                $routeConfig['hostroute']['route'] = $requestedHost;
            }
        } else {
            die('fail');
            // no matching store?
        }

        $routeConfig['cosmos']['type'] = 'Zend_Controller_Router_Route_Chain';
        if(isset($routeConfig['pathroute'])){
            $routeConfig['cosmos']['chain'] = 'defaultmodule,pathroute';
        } else {
            $routeConfig['cosmos']['chain'] = ' defaultmodule';
        }
        Zend_Controller_Front::getInstance()->getRouter()->addConfig(new Zend_Config($routeConfig));
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

    protected function _initDumpRoutes()
    {
        $test = Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'cosmos-test_route');
        Zend_Debug::dump($test);
        $test = Zend_Controller_Front::getInstance()->getRouter()->getRoutes();
    }
}