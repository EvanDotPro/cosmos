<?php
class Cosmos_Addon
{

    /**
     * An array of addons
     * @var array
     */
    protected $_addons = null;

    /**
     * Singleton instance
     *
     * Marked only as protected to allow extension of the class. To extend,
     * simply override {@link getInstance()}.
     *
     * @var Cosmos_Addon
     */
    protected static $_instance = null;

    /**
     * If any addons override the layout, then this will be set
     * @var string
     */
    protected $_layoutPath = null;


    protected $_request = null;

    /**
     * Constructor
     *
     * Instantiate using {@link getInstance()}; the cosmos addon manager is a singleton
     * object.
     *
     * @return void
     */
    protected function __construct()
    {
        $this->_setAddons();

        foreach($this->_addons as $addonName => $addon){
            $addonDir = $addon['directory'];
            $config = $addon['config'];

            // Add any services provided by the add-on
            if (isset($config['services']) && $config['services'] == true) {
                $this->_addServices($addonDir . '/services');
            }

            // Add the library folder to the include path if it exists
            if (isset($config['library']) && $config['library'] == true) {
                $libraryPath = $addonDir . "/library";
                // @todo: possibly use the zend autoloader instead?
                set_include_path(implode(PATH_SEPARATOR, array(get_include_path(), $libraryPath)));
            }

            // Add any translations
            if (isset($config['languages']) && $config['languages'] == true) {
                $this->_addLanguages($addonDir . '/etc/languages');
            }

            // Add any routes the add-on provides
            if (isset($config['routes']) && is_array($config['routes'])) {
                $this->_addRoutes(new Zend_Config($config['routes']));
            }

            // Add any modules the add-on provide
            if (isset($config['modules']['provided']) && is_array($config['modules']['provided'])) {
                foreach($config['modules']['provided'] as $moduleName => $module){
                    $moduleDir = $addonDir . '/modules/' . $moduleName . '/controllers';
                    Zend_Controller_Front::getInstance()->addControllerDirectory($moduleDir, $moduleName);
                }
            }
        }
        Zend_Controller_Front::getInstance()->getRouter()->addRoute('master', Zend_Registry::get('masterRoute'));
    }

    /**
     * Enforce singleton; disallow cloning
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Singleton instance
     *
     * @return Cosmos_Addon
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function setRequest(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;

        $moduleName = $request->getModuleName();
        $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();


	    // Add the core module's view path first so ZF doesn't try to later and mess things up
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->initView();
        $moduleDirectory = Zend_Controller_Front::getInstance()->getModuleDirectory($moduleName);
		$scriptPath = "{$moduleDirectory}/views";
		if (is_dir($scriptPath)) {
			Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addBasePath($scriptPath);
		}
		$moduleLayoutDirectory = "{$moduleDirectory}/views/layouts";
        if (is_dir($moduleLayoutDirectory)) {
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addScriptPath($moduleLayoutDirectory);
        } else {
            $defualtModule = Zend_Controller_Front::getInstance()->getDefaultModule();
            $defaultModuleDirectory = Zend_Controller_Front::getInstance()->getModuleDirectory($defualtModule);
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addBasePath($defaultModuleDirectory . '/views');
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addScriptPath($defaultModuleDirectory . '/views/layouts');
        }


        foreach ($this->_addons as $addonName => $addon) {
            $addonDir = $addon['directory'];


            // Add extended module stuff...
            if (isset($addon['config']['modules']['extended']['ext_' . $moduleName])) {
                $extended = $addon['config']['modules']['extended']['ext_' . $moduleName];
                $extendedDir = $addonDir . "/modules/ext_{$moduleName}";
                // View stuff
                if (isset($extended['views']) && $extended['views']) {
                    // Add the view path
                    $viewPath = $extendedDir . '/views';
                    Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addBasePath($viewPath);

                    // Add the layouts path
                    if (isset($extended['views']['layouts']) && $extended['views']['layouts'] == true) {
                        $layoutPath = $viewPath.'/layouts';
                        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addScriptPath($layoutPath);
                    }

                    // Run the placeholders
                    if (isset($extended['views']['placeholders']) && $extended['views']['placeholders'] == true) {
                        $this->_runPlaceholders($viewPath.'/scripts');
                    }
                }

                // Extended controllers
                if (!isset($controllerLoaded)
                    && isset($extended['controllers'])
                    && $extended['controllers'] == true
                    && !$dispatcher->isDispatchable($request)) {
                    $controllerPath = $extendedDir . '/controllers';
                    $file = $dispatcher->getControllerClass($request).'.php';
                    if (Zend_Loader::isReadable($controllerPath.'/'.$file)) {
                        Zend_Loader::loadFile($file, $controllerPath, true);
                        $controllerLoaded = true;
                    }
                }
            }
        }
    }

    protected function _setAddons()
    {
        $addons = Cosmos_Api::get()->cosmos->listEnabledAddons();
        $addonsDir = APPLICATION_PATH . '/addons';
        $this->_addons = array();
        foreach($addons as $addonName){
            $addonDir = $addonsDir . '/'. $addonName;
            $this->_addons[$addonName]['config'] = include $addonDir . '/addon.php';
            $this->_addons[$addonName]['directory'] = $addonDir;
        }
    }

    protected function _runPlaceholders($path)
    {
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->render('_placeholders.php');
    }

    protected function _addRoutes($routesConfig)
    {
        foreach($routesConfig as $config){
            $route = Zend_Controller_Router_Route::getInstance($config);
            Zend_Registry::get('masterRoute')->chain($route);
        }
    }

    protected function _addServices($path)
    {
        $config =  include $path . '/_services.php';
        foreach($config as $namespace => $service)
        {
            require_once $path . '/' . $service['file'];
            Zend_Registry::get('server')->setClass($service['class'], $namespace);
        }
    }

    protected function _addLanguages($path)
    {
        // @todo: auto-detect and/or make the locale dynamic
        Zend_Registry::get('Zend_Translate')->addTranslation($path,'en_US',array('scan' => Zend_Translate::LOCALE_DIRECTORY));
    }

    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Returns an array of the enabled add-ons, without having to make another API call.
     *
     * @return array
     */
    public function listEnabledAddons()
    {
        $addons = array();
        foreach($this->_addons as $addon => $details){
            $addons[] = $addon;
        }
        return $addons;
    }

    /**
     * Returns the layout path if any add-ons have overridden it
     *
     * @return string
     */
    public function getLayoutPath()
    {
        return $this->_layoutPath;
    }
}