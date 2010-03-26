<?php
class ZendC_Controller_Plugin_CosmosAddonLoader extends Zend_Controller_Plugin_Abstract
{
	public function routeShutdown(Zend_Controller_Request_Abstract $request)
	{
	    Cosmos_Addon::getInstance()->setRequest($request);
	}
}