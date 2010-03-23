<?php
/**
 * Cosmos Commerce
 *
 * LICENSE
 *
 * This source file is dual licensed under the MIT or GPL Version 2
 * licenses that are bundled with this package in the files 
 * GPL-LICENSE.txt and MIT-LICENSE.txt.
 * A copy is also available through the world-wide-web at this URL:
 * http://cosmoscommerce.org/license
 * If you did not receive a copy of these licenses and are unable
 * to obtain a copy through the world-wide-web, please send an email
 * to license@cosmoscommerce.org so we can send you a copy immediately.
 *
 * @category   Cosmos
 * @package	   Cosmos_Api
 * @copyright  Copyright (c) 2010 Cosmos Team (http://cosmoscommerce.org/)
 * @license    http://cosmoscommerce.org/license     Dual licensed under the MIT or GPL Version 2 licenses
 */

/**
 * Cosmos API HTTP request object
 *
 * An API request object that is initialized from the HTTP request.
 * This object accepts both XML-RPC and JSON-RPC requests and
 * Support for OAuth request signing is built in.
 *
 * @category   Cosmos
 * @package    Cosmos_Api
 * @copyright  Copyright (c) 2010 Cosmos Team (http://cosmoscommerce.org/)
 * @license    http://cosmoscommerce.org/license     Dual licensed under the MIT or GPL Version 2 licenses
 * @version    {{version}}
 */
class Cosmos_Api_Request_Http extends Cosmos_Api_Request
{
    /**
     * The native request object for the request format used
     * @var mixed
     */
    protected $_internalRequest;
    
    /**
     * HTTP request object
     * @var Zend_Controller_Request_Http
     */
    protected $_httpRequest;
    
    /**
     * This is the OAuth signature that was passed with the request
     * @var string
     */
    protected $_oauthSignature;
    
    public function __construct()
    {
        $this->setHttpRequest();
        $path = explode('/',substr($this->getHttpRequest()->getPathInfo(),1));
        if(isset($path[1])){
            $requestFormat = $path[1];
        } else {
            $requestFormat = null;
        }
        $this->setRequestFormat($requestFormat);
        
        $responseFormat = $this->getHttpRequest()->getQuery('response');
        if($responseFormat == null){
            $this->setResponseFormat($requestFormat);
        } else {
            $this->setResponseFormat($responseFormat);
        }
        
        $this->setVersion($path[0]);
        $this->_parseAuthorizationHeader();
//        try {
//        } catch (Exception $e) {
//            $authException = $e;
//        }
        switch($requestFormat){
            case 'xmlrpc':
                $this->setInternalRequest(new Zend_XmlRpc_Request_Http());
                break;
            case 'jsonrpc':
                $this->setInternalRequest(new ZendC_Json_Server_Request_Http());
                if($this->getInternalRequest()->isParseError()){
                    throw new Cosmos_Api_Exception('Parse Error');
                }
                break;
        }
        $this->setMethod($this->getInternalRequest()->getMethod());
        $this->setParams($this->getInternalRequest()->getParams());
    }
    
    public function setInternalRequest($request)
    {
        $this->_internalRequest = $request;
    }
    
    public function getInternalRequest()
    {
        return $this->_internalRequest;
    }
    
    public function setHttpRequest(Zend_Controller_Request_Http $request = null)
    {
        if($request == null){
            $request = new Zend_Controller_Request_Http();
        }
        $this->_httpRequest = $request;
        
    }
    
    public function getHttpRequest()
    {
        return $this->_httpRequest;
    }
    
    protected function _parseAuthorizationHeader()
	{
		$this->_oauth = array();
		$headerString = explode(',', $this->getHttpRequest()->getHeader('Authorization'));
		foreach($headerString as $string){
			$pair = explode('=', $string);
			if(count($pair) > 1){
			    $this->_oauthParams[$pair[0]] = rawurldecode(substr($pair[1], 1, -1)); // substr to get rid of quotes
			}
		}
		
		if(!isset($this->_oauthParams['oauth_consumer_key']) ||
		   !isset($this->_oauthParams['oauth_signature']) ||
		   !isset($this->_oauthParams['oauth_timestamp']) ||
		   !isset($this->_oauthParams['oauth_nonce'])
		){
			throw new Cosmos_Api_Exception('Unauthorized - OAuth header not valid or present.', 401);
		}
		
		$this->_oauthSignature = $this->_oauthParams['oauth_signature'];
		unset($this->_oauthParams['oauth_signature']);
		
	    return $this->_validateOauthRequest();
	}
	
	protected function _validateOauthRequest()
	{
	    $apiKey = Cosmos_Api::getApiKey($this->_oauthParams['oauth_consumer_key']);
		if(!$apiKey){
			throw new Cosmos_Api_Exception('Unauthorized - Bad consumer key provided', 401);
		}
		$this->setOauthConsumerSecret($apiKey['private_key']);
		$signature = $this->_generateOauthSignature('POST');
		if($signature !== $this->_oauthSignature){
			throw new Cosmos_Api_Server_Exception('Unauthorized - Bad signature provided', 401);
		}
		return true;
	}
	
    public function getUrl()
	{
		return $this->getHttpRequest()->getScheme() .'://' . $this->getHttpRequest()->getHttpHost() . $this->getHttpRequest()->getRequestUri(true);
	}
}