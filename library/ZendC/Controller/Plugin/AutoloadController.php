<?php
class ZendC_Controller_Plugin_AutoloadController extends Zend_Controller_Plugin_Abstract
{
    // TODO: Dynamically only include enabled plugins, etc.
    protected $_plugins = array('csr_available','sample','test_theme');
    
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();

        // Skip if it's fine...
//        if($dispatcher->isDispatchable($request)){
//            return;
//        }
        $file = $dispatcher->getControllerClass($request).'.php';
        
        foreach($this->_plugins as $plugin){
            $path = APPLICATION_PATH . "/plugins/{$plugin}/modules/ext-{$request->getModuleName()}/controllers/";
            if(Zend_Loader::isReadable($path.$file)){
                Zend_Loader::loadFile($file, $path, true);
            }
        }
    }
    
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        foreach($this->_plugins as $plugin){
            $scriptPath = APPLICATION_PATH . "/plugins/{$plugin}/modules/ext-{$request->getModuleName()}/views/";
            if(is_dir($scriptPath)){
                Zend_Layout::getMvcInstance()->getView()->addBasePath($scriptPath);
            }
            $basePath = Zend_Layout::getMvcInstance()->getView()->getScriptPaths();Zend_Debug::dump($basePath);
        }
    }
}