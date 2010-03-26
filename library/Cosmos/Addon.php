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
        $this->_addons = Cosmos_Api::get()->cosmos->listEnabledAddons();
        foreach($this->_addons as $addon){
            
            // Add the library folder to the include path if it exists
            $libraryPath = APPLICATION_PATH . "/addons/{$addon}/library/";
			if (is_dir($libraryPath)) {
			    // @todo: possibly use the zend autoloader instead?
				set_include_path(implode(PATH_SEPARATOR, array(get_include_path(), $libraryPath)));
			}
			
            // Add any modules the plugins provide
            $path = APPLICATION_PATH . "/addons/{$addon}/modules/";
            try{
                $dir = new DirectoryIterator($path);
            } catch(Exception $e) {
                continue;
            }

            foreach ($dir as $file) {
                if ($file->isDot() || !$file->isDir()) {
                    continue;
                }

                $module = $file->getFilename();

                if (strlen($module) <= $addon || strtolower(substr($module,0,4)) == 'ext_' || (strtolower(substr($module,0,strlen($addon))).'_' != strtolower($addon).'_') || preg_match('/^[^a-z]/i', $module)) {
                    continue;
                }
                
                $moduleDir = $file->getPathname() . DIRECTORY_SEPARATOR . Zend_Controller_Front::getInstance()->getModuleControllerDirectoryName();
                Zend_Controller_Front::getInstance()->addControllerDirectory($moduleDir, $module);
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
		$scriptPath = "{$moduleDirectory}/views/";
		if (is_dir($scriptPath)) {
			Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addBasePath($scriptPath);
		}
		$moduleLayoutDirectory = "{$moduleDirectory}/views/layouts";
        if(is_dir($moduleLayoutDirectory)){
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addScriptPath($moduleLayoutDirectory);
        } else {
            $defualtModule = Zend_Controller_Front::getInstance()->getDefaultModule();
            $defaultModuleDirectory = Zend_Controller_Front::getInstance()->getModuleDirectory($defualtModule);
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addBasePath($defaultModuleDirectory . '/views');
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addScriptPath($defaultModuleDirectory . '/views/layouts/');
        }
        foreach($this->_addons as $addon){
            // Add the view path if it's provided by the add-on
		    $scriptPath = APPLICATION_PATH . "/addons/{$addon}/modules/ext_{$moduleName}/views/";
			if (is_dir($scriptPath)) {
				Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addBasePath($scriptPath);
				// Set the layout path if given
				$layoutPath = $scriptPath.'layouts/';
				if (is_dir($layoutPath)) {
				    $this->_layoutPath = $layoutPath;
				    Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addScriptPath($layoutPath);
				}
			}
			
            // Load the controller file if it's provided by the add-on
			if (!isset($controllerLoaded) && !$dispatcher->isDispatchable($request)) {
    		    $path = APPLICATION_PATH . "/addons/{$addon}/modules/ext_{$moduleName}/controllers/";
                $file = $dispatcher->getControllerClass($request).'.php';
    			if(Zend_Loader::isReadable($path.$file)){
    				Zend_Loader::loadFile($file, $path, true);
    				$controllerLoaded = true;
    			}
			}
			
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
        }
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