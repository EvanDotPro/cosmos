<?php
class Cosmos_Api_Client_JsonRpc_Request
{
	protected $_method;
	
	protected $_params;
	
	public function setMethod($method)
	{
		$this->_method = $method;
	}
	
	public function setParams($params)
	{
		$this->_params = $params;
	}
	
	public function __toString()
	{
		$request = array();
		$request['jsonrpc'] = '2.0';
		$request['method'] = $this->_method;
		$request['params'] = $this->_params;
		$request['id'] = 1;
		
		return Zend_Json::encode($request);
	}
}