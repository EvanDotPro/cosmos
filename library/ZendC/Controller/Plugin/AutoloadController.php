<?php
class ZendC_Controller_Plugin_AutoloadController extends Zend_Controller_Plugin_Abstract
{

	public function routeShutdown(Zend_Controller_Request_Abstract $request)
	{
		$dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();

		// Skip if it's fine...
		//        if($dispatcher->isDispatchable($request)){
		//            return;
		//        }
		$file = $dispatcher->getControllerClass($request).'.php';

		$plugins = Cosmos_Api::get()->cosmos->listEnabledPlugins();

		foreach($plugins as $plugin){
			$path = APPLICATION_PATH . "/plugins/{$plugin}/modules/ext-{$request->getModuleName()}/controllers/";
			if(Zend_Loader::isReadable($path.$file)){
				Zend_Loader::loadFile($file, $path, true);
			}

			$iniFile = APPLICATION_PATH . "/plugins/{$plugin}/services/services.ini";
			if (Zend_Loader::isReadable($iniFile)) {
				$path = APPLICATION_PATH . "/plugins/{$plugin}/services";
				$config = new Zend_Config_Ini($iniFile);
				$config = $config->toArray();
				if($config['services'] === null)
				{
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

	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->initView();
		$scriptPath = APPLICATION_PATH . "/core/modules/{$request->getModuleName()}/views/";
		if(is_dir($scriptPath)){
			Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addBasePath($scriptPath);
		}

		$plugins = Cosmos_Api::get()->cosmos->listEnabledPlugins();

		foreach($plugins as $plugin){
			$scriptPath = APPLICATION_PATH . "/plugins/{$plugin}/modules/ext-{$request->getModuleName()}/views/";
			if(is_dir($scriptPath)){
				Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->addBasePath($scriptPath);
			}
		}
	}
}