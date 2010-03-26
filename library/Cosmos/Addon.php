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
            $path = APPLICATION_PATH . "/addons/{$addon}/modules/provides/";
            if(is_dir($path)){
                Zend_Controller_Front::getInstance()->addModuleDirectory($path);
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
        $moduleName = $request->getModuleName();
        $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();
        
	    // Add the core module's view path first so ZF doesn't try to later and mess things up
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->initView();
		$scriptPath = APPLICATION_PATH . "/core/modules/{$moduleName}/views/";
		if (is_dir($scriptPath)) {
			Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addBasePath($scriptPath);
		}
        
        foreach($this->_addons as $addon){
            // Add the view path if it's provided by the add-on
		    $scriptPath = APPLICATION_PATH . "/addons/{$addon}/modules/ext-{$moduleName}/views/";
			if (is_dir($scriptPath)) {
				Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addBasePath($scriptPath);
			}
			
            // Load the controller file if it's provided by the add-on
			if (!isset($controllerLoaded) && !$dispatcher->isDispatchable($request)) {
    		    $path = APPLICATION_PATH . "/addons/{$addon}/modules/ext-{$moduleName}/controllers/";
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
				if ($config['services'] === null) {
					return false;
				}
				foreach($config['services'] as $namespace => $service)
				{
					require_once "{$path}/{$service['file']}";
					Zend_Registry::get('server')->setClass($service['class'], $namespace);
				}
			}
        }
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
}