<?php
class ZendC_Controller_Plugin_CosmosAddonLoader extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
	    if(!Cosmos_Addon::getInstance()->getRequest()){
	        Cosmos_Addon::getInstance()->setRequest($request);
	    }
	}
}