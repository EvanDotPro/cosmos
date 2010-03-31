<?php
class Customurl_ControllerPlugin extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
	    // 404, try a custom url...
	    if(!Zend_Controller_Front::getInstance()->getDispatcher()->isDispatchable($request)){
    	    $temp = $request->getRequestUri();
	        $checker = Cosmos_Api::get()->url->check($temp);
	        if($checker){
	            // @todo: this should allow for regular redirects, or dispatching a different controller/action/module without actually redirecting.
	            Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector')->gotoSimple($checker['action'],$checker['controller'],$checker['module']);
	        }
	    }
	}
}