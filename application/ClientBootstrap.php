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


        $front = Zend_Controller_Front::getInstance();

        $dispatcher = $front->getDispatcher();


        $defaults = array('controller'  => $dispatcher->getDefaultControllerName(),
                          'action'      => $dispatcher->getDefaultAction(),
                          'module'      => $dispatcher->getDefaultModule()
                    );
        $defaultRoute = new Zend_Controller_Router_Route_Module($defaults);
        Zend_Registry::set('defaultRoute',$defaultRoute);
        $routeConfig = array();
        if($store = Cosmos_Api::get()->cosmos->getStoreByHostPath($requestedHost, $requestedPath)){
            if($store['path']){
                Zend_Debug::dump($store['path']);
                $pathRoute = new Zend_Controller_Router_Route(':path',array('path'=>$store['path']));
                $cosmos = clone $pathRoute;
                $front->getRouter()->addRoute('cosmos', $pathRoute->chain($defaultRoute));
//                $cosmos = $pathRoute;
                Zend_Registry::set('mode','path');
            } else {
        $front->getRouter()->removeDefaultRoutes();
//                $hostnameRoute = new Zend_Controller_Router_Route_Hostname($requestedHost);
//                $cosmos = $hostnameRoute->chain($defaultRoute);

                $cosmos = $defaultRoute;

                Zend_Registry::set('mode','host');
            $front->getRouter()->addRoute('cosmos', $cosmos);
            }
        } else {
            die('fail');
            // no matching store?
        }
        Zend_Registry::set('cosmosRoute', $cosmos);
//        $front->getRouter()->addRoute('cosmos', $cosmos);
//        Zend_Controller_Front::getInstance()->getRouter()->addConfig(new Zend_Config($routeConfig));
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

//        Zend_Controller_Front::getInstance()->getRouter()->addRoute('cosmos', Zend_Registry::get('cosmosRoute')->chain(Zend_Registry::get('defaultRoute')));
//        $test = Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'cosmos-test_route');
//        Zend_Debug::dump($test);
//        $test = Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'cosmos');
        $test = Zend_Controller_Front::getInstance()->getRouter()->getRoutes();
//        Zend_Debug::dump($test);
    }
}