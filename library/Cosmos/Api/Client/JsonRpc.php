<?php
class Cosmos_Api_Client_JsonRpc
{
	protected $_serverAddress;
	
	protected $_httpClient = null;
	
	public function __construct($serverAddress)
	{
		
		$this->_serverAddress = $serverAddress;
		
		$this->_httpClient = new Zend_Http_Client($this->_serverAddress);
		
	}
	
	public function getHttpClient()
	{
		return $this->_httpClient;
	}
	
	public function call($method, $params)
	{
		
	}
	
	public function doRequest($request)
	{
		$http = $this->getHttpClient();
		if($http->getUri() === null) {
 			$http->setUri($this->_serverAddress);
		}
		
		$http->setRawData($request->__toString());
		
		$httpResponse = $http->request(Zend_Http_Client::POST);
		
		return $httpResponse;
	}
	
	public function mapRequest()
	{
		$http = $this->getHttpClient();
		$http->resetParameters();
		$httpResponse = $http->request(Zend_Http_Client::GET);
		return $httpResponse;
	}
	
    public function cosmosRequest(Cosmos_Api_Request $request)
    {
        $this->getHttpClient()->setHeaders('Authorization', $request->getOauthHeader());
        $lastResponse = $this->doRequest($request->getNativeRequest());
        return $lastResponse;
    }
}