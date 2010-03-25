<?php
class ZendC_Controller_Plugin_AutoloadController extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();

        // Skip if it's fine...
//        if($dispatcher->isDispatchable($request)){
//            return;
//        }
        $file = $dispatcher->getControllerClass($request).'.php';
        // TODO: Dynamically only include enabled plugins, etc.
        $plugins = array('csr_available','calc');
        foreach($plugins as $plugin){
            $scriptPath = APPLICATION_PATH . "/plugins/{$plugin}/modules/ext-{$request->getModuleName()}/views/";
            if(is_dir($scriptPath)){
                Zend_Layout::getMvcInstance()->getView()->addBasePath($scriptPath);
            }
            
            $path = APPLICATION_PATH . "/plugins/{$plugin}/modules/ext-{$request->getModuleName()}/controllers/";
            if(Zend_Loader::isReadable($path.$file)){
                Zend_Loader::loadFile($file, $path, true);
            }
        }
    }
}