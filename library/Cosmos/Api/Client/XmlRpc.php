<?php
class Cosmos_Api_Client_XmlRpc extends Zend_XmlRpc_Client
{
    public function __construct($server, Zend_Http_Client $httpClient = null)
    {
        $this->setSkipSystemLookup(true);
        parent::__construct($server, $httpClient);
    }
    
    public function cosmosRequest(Cosmos_Api_Request $request)
    {
        $this->getHttpClient()->setHeaders('Authorization', $request->getOauthHeader());
        $this->doRequest($request->getNativeRequest());
        return $this->getLastResponse()->getReturnValue();
    }
}