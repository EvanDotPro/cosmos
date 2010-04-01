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

    protected $_router = null;

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


        foreach($this->_addons as $addonName => $addon){
            $addonDir = $addon['directory'];
//			if(isset($addon['config']['modules']['extended']) && is_array($addon['config']['modules']['extended'])){
//
//			}
			// Skip on to the next if there's no ext_ modules
			$extPath = APPLICATION_PATH . "/addons/{$addonName}/modules/ext_{$moduleName}";
			if (!is_dir($extPath)) {
			    continue;
			}

            // Add the view path if it's provided by the add-on
		    $scriptPath = APPLICATION_PATH . "/addons/{$addonName}/modules/ext_{$moduleName}/views";
			if (is_dir($scriptPath)) {
				Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addBasePath($scriptPath);
				$this->_runPlaceholders($scriptPath.'/scripts');
				// Set the layout path if given
				$layoutPath = $scriptPath.'/layouts';
				if (is_dir($layoutPath)) {
				    $this->_layoutPath = $layoutPath;
				    Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addScriptPath($layoutPath);
				}
			}

            // Load the controller file if it's provided by the add-on
		    $controllerPath = APPLICATION_PATH . "/addons/{$addonName}/modules/ext_{$moduleName}/controllers";
			if (is_dir($controllerPath) && !isset($controllerLoaded) && !$dispatcher->isDispatchable($request)) {
                $file = $dispatcher->getControllerClass($request).'.php';
    			if (Zend_Loader::isReadable($controllerPath.'/'.$file)) {
    				Zend_Loader::loadFile($file, $controllerPath, true);
    				$controllerLoaded = true;
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

    protected function _addRoutes($routes)
    {
        if($this->_router == null){
            $this->_router = Zend_Controller_Front::getInstance()->getRouter();
        }
        $this->_router->addConfig($routes);
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
        return $this->_addons;
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