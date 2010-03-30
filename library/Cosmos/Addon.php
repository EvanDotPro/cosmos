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
        
        $this->_addons = Cosmos_Api::get()->cosmos->listEnabledAddons();
        
        $addonsDir = APPLICATION_PATH . '/addons';
        
        foreach($this->_addons as $addonName){
            
            $thisAddonDir = $addonsDir . '/' . $addonName;
            
            // Add the library folder to the include path if it exists
            $libraryPath = $thisAddonDir . "/library";
			if (is_dir($libraryPath)) {
			    // @todo: possibly use the zend autoloader instead?
				set_include_path(implode(PATH_SEPARATOR, array(get_include_path(), $libraryPath)));
			}
			
			// Add any routes the add-on provides
			$this->_addRoutes($thisAddonDir . '/etc/routes');
			
            // Add any modules the add-on provide
            try{
                $dir = new DirectoryIterator($thisAddonDir . '/modules');
            } catch(Exception $e) {
                continue;
            }
            
            $addon = strtolower($addonName);
            
            foreach ($dir as $file) {
                if ($file->isDot() || !$file->isDir()) {
                    continue;
                }

                $moduleName = strtolower($file->getFilename());
                
                if (strlen($moduleName) <= strlen($addon) || substr($moduleName,0,4) == 'ext_' || (substr($moduleName,0,strlen($addon)).'_' != $addon.'_')) {
                    continue;
                }
                
                $moduleDir = $file->getPathname() . DIRECTORY_SEPARATOR . Zend_Controller_Front::getInstance()->getModuleControllerDirectoryName();
                if(is_dir($moduleDir)){
                    Zend_Controller_Front::getInstance()->addControllerDirectory($moduleDir, $file->getFilename());
                }
            }
            
            // Add any routes the addon provides
            
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
			$this->_runPlaceholders($scriptPath.'/scripts');
		}
		$moduleLayoutDirectory = "{$moduleDirectory}/views/layouts";
        if(is_dir($moduleLayoutDirectory)){
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addScriptPath($moduleLayoutDirectory);
        } else {
            $defualtModule = Zend_Controller_Front::getInstance()->getDefaultModule();
            $defaultModuleDirectory = Zend_Controller_Front::getInstance()->getModuleDirectory($defualtModule);
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addBasePath($defaultModuleDirectory . '/views');
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addScriptPath($defaultModuleDirectory . '/views/layouts');
        }
        foreach($this->_addons as $addon){
            
            // Add any services provided by the add-on
			$iniFile = APPLICATION_PATH . "/addons/{$addon}/services/services.ini";
			if (Zend_Loader::isReadable($iniFile)) {
				$path = APPLICATION_PATH . "/addons/{$addon}/services";
				$config = new Zend_Config_Ini($iniFile);
				$config = $config->toArray();
				if ($config['services'] !== null) {
				    foreach($config['services'] as $namespace => $service)
    				{
    					require_once "{$path}/{$service['file']}";
    					Zend_Registry::get('server')->setClass($service['class'], $namespace);
    				}
				}
			}
			
			// Skip on to the next if there's no ext_ modules
			$extPath = APPLICATION_PATH . "/addons/{$addon}/modules/ext_{$moduleName}";
			if (!is_dir($extPath)) {
			    continue;
			}
			
            // Add the view path if it's provided by the add-on
		    $scriptPath = APPLICATION_PATH . "/addons/{$addon}/modules/ext_{$moduleName}/views";
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
		    $controllerPath = APPLICATION_PATH . "/addons/{$addon}/modules/ext_{$moduleName}/controllers";
			if (is_dir($controllerPath) && !isset($controllerLoaded) && !$dispatcher->isDispatchable($request)) {
                $file = $dispatcher->getControllerClass($request).'.php';
    			if (Zend_Loader::isReadable($controllerPath.'/'.$file)) {
    				Zend_Loader::loadFile($file, $controllerPath, true);
    				$controllerLoaded = true;
    			}
			}
        }
    }
    
    protected function _runPlaceholders($path)
    {
        if (Zend_Loader::isReadable($path.'/placeholders.php')) {
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->render('placeholders.php');
        }
    }
    
    protected function _addRoutes($path)
    {
        try{
            $dir = new DirectoryIterator($path);
        } catch(Exception $e) {
            return;
        }
        if($this->_router == null){
            $this->_router = Zend_Controller_Front::getInstance()->getRouter();
        }
        
        foreach ($dir as $file) {
            if ($file->isDot()) {
                continue;
            }
            $config = new Zend_Config_Ini($file->getPathname(), 'routes');
            $this->_router->addConfig($config, 'routes');
        }
    }
    
    protected function _addLanguages($path)
    {
        Zend_Registry::get('Zend_Translate')->addTranslation($path,null,array('scan' => Zend_Translate::LOCALE_DIRECTORY));
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