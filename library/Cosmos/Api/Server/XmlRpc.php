<?php
class Cosmos_Api_Server_XmlRpc extends Zend_XmlRpc_Server
{
	protected $_faultCodeMap = array(
		620 => -32601,
		623 => -32602,
		631 => -32700,
		632 => -32600
	);
	
	public function run()
	{
		
	}
	
	public function cosmosHandle($request)
	{
	    return $this->handle($request->getNativeRequest());
	}
}