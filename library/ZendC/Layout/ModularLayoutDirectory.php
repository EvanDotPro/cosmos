<?php
class ZendC_Layout_ModularLayoutDirectory
    extends Zend_Layout_Controller_Plugin_Layout
{
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $moduleName = $request->getModuleName();
        $path = APPLICATION_PATH . "/core/modules/{$moduleName}/views/layouts";
        if(is_dir($path)){
//            Zend_Layout::getMvcInstance()->getView()->addBasePath($scriptPath);
            $this->getLayout()->setLayoutPath($path);
        }
    }
}