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
        $path = explode('/',trim($request->getPathInfo(),'/'));
        $requestedPath = array_shift($path);
        $requestedHost = $request->getHttpHost();

        if($store = Cosmos_Api::get()->cosmos->getStoreByHostPath($requestedHost, $requestedPath)){
            Zend_Registry::get('log')->info($store);
            if($store['path']){
                $this->bootstrap('frontcontroller');
                $front = Zend_Controller_Front::getInstance();
                $request = $front->getRequest();
                $dispatcher = $front->getDispatcher();
                $defaultRoute = new Zend_Controller_Router_Route_Module(array(), $dispatcher, $request);
                $front->getRouter()->removeDefaultRoutes();
                $defaults = array('path'    =>$store['path'],
                                  'module'  => $dispatcher->getDefaultModule());
                $pathRoute = new Zend_Controller_Router_Route(':path',$defaults);
                Zend_Registry::set('cosmosRoute', $pathRoute);
                // This is to catch empty requests because for some reason 'default' doesn't.
                $front->getRouter()->addRoute('default-empty', $pathRoute);
                $front->getRouter()->addRoute('default', $pathRoute->chain($defaultRoute));
                Zend_Registry::set('mode','path');
            } else {
                Zend_Registry::set('mode','host');
            }
        } else {
            die('fail');
            // no matching store?
        }
        Cosmos_Addon::getInstance($store['addons']);
    }
}
