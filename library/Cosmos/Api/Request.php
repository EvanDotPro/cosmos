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
 * @package    Cosmos_Api
 * @copyright  Copyright (c) 2010 Cosmos Team (http://cosmoscommerce.org/)
 * @license    http://cosmoscommerce.org/license     Dual licensed under the MIT or GPL Version 2 licenses
 */

/**
 * Cosmos API request object
 *
 * Encapsulates an API request, holding the method call and all parameters.
 * Provides accessors for these, as well as the ability to manage OAuth request
 * signing data.
 *
 * @category   Cosmos
 * @package    Cosmos_Api
 * @copyright  Copyright (c) 2010 Cosmos Team (http://cosmoscommerce.org/)
 * @license    http://cosmoscommerce.org/license     Dual licensed under the MIT or GPL Version 2 licenses
 * @version    {{version}}
 */
class Cosmos_Api_Request
{
    /**
     * Standard request formats
     * @var array
     */
    protected $_requestFormats = array(
	    'local'		=> true,
	    'xmlrpc'	=> true,
        'jsonrpc'	=> true
    );

    /**
     * Standard response formats
     *
     * @var array
     */
    protected $_responseFormats = array(
	    'xmlrpc'		=> true,
        'jsonrpc'		=> true,
        'local'			=> true
    );

    /**
     * Method to call
     * @var string
     */
    protected $_method;

    /**
     * Method parameters
     * @var array
     */
    protected $_params = array();

    /**
     * API version (only used for local requests)
     * @var string
     */
    protected $_version;

    /**
     * API server URL
     * @var string
     */
    protected $_url;

    /**
     * Format of the request
     * @var string
     */
    protected $_requestFormat;

    /**
     * Format of the response (so the server knows how to response)
     * @var string
     */
    protected $_responseFormat;

    /**
     * OAuth parameters
     * @var array
     */
    protected $_oauthParams;

    /**
     * Consumer secret for OAuth request signing
     * @var string
     */
    protected $_oauthConsumerSecret;

    /**
     * Set the call method
     *
     * @param string $method
     * @return Cosmos_Api_Request
     */
    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }

    /**
     * Retrieve call method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Set the method parameters
     *
     * @param array $params
     * @return Cosmos_Api_Request
     */
    public function setParams($params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * Retrieve method parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Set the request URL (used for OAuth request signing)
     *
     * @param string $url
     * @return Cosmos_Api_Request
     */
    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    /**
     * Retrieve request URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Set the API version
     *
     * @param string $version
     * @return Cosmos_Api_Request
     */
    public function setVersion($version)
    {
        $this->_version = $version;
        return $this;
    }

    /**
     * Retrieve API version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Set the OAuth consumer secret for request signing
     *
     * @param string $oauthConsumerSecret
     * @return Cosmos_Api_Request
     */
    public function setOauthConsumerSecret($oauthConsumerSecret)
    {
        $this->_oauthConsumerSecret = $oauthConsumerSecret;
        return $this;
    }

    /**
     * Get OAuth consumer secret
     *
     * @return string
     */
    protected function _getOauthConsumerSecret()
    {
        return $this->_oauthConsumerSecret;
    }

    /**
     * Set the request format
     *
     * @param string $requestFormat
     * @return Cosmos_Api_Request
     * @throws Cosmos_Api_Exception
     */
    public function setRequestFormat($requestFormat)
    {
        $requestFormat = strtolower($requestFormat);
        if(!isset($this->_requestFormats[$requestFormat]) || $this->_requestFormats[$requestFormat] == false){
            throw new Cosmos_Api_Exception("Invalid API request format: {$requestFormat}");
        }
        $this->_requestFormat= $requestFormat;
        return $this;
    }

    /**
     * Get request format
     *
     * @return string
     */
    public function getRequestFormat()
    {
        return $this->_requestFormat;
    }

    /**
     * Set the response format
     *
     * @param string $responseFormat
     * @return Cosmos_Api_Request
     * @throws Cosmos_Api_Exception
     */
    public function setResponseFormat($responseFormat)
    {
        if(!isset($this->_responseFormats[$responseFormat]) || $this->_responseFormats[$responseFormat] == false){
            throw new Cosmos_Api_Exception("Invalid API response format: {$responseFormat}");
        }
        $this->_responseFormat = $responseFormat;
    }

    /**
     * Get response format
     *
     * @return string
     */
    public function getResponseFormat()
    {
        return $this->_responseFormat;
    }

    /**
     * Set an OAuth parameter
     *
     * @param string $name
     * @param mixed $value
     * @return Cosmos_Api_Request
     */
    public function setOauthParam($name, $value)
    {
        $this->_oauthParams[$name] = $value;
        return $this;
    }
    
    /**
     * Returns the value of a given OAuth parameter
     * 
     * @param string $name
     * @return mixed
     */
    public function getOauthParam($name)
    {
        if(isset($this->_oauthParams[$name])){
            return $this->_oauthParams[$name];
        } else {
            return false;
        }
    }

    /**
     * Get OAuth 'Authentication' header string
     *
     * @return string
     */
    public function getOauthHeader()
    {
        $this->_oauthParams['oauth_timestamp'] = time();
        $this->_oauthParams['oauth_nonce'] = md5(uniqid(rand(), true));
        // http://oauth.googlecode.com/svn/spec/ext/body_hash/1.0/drafts/5/spec.html
        // Haven't fully implemented that spec, but the idea is the same.
        // TODO: The server should validate this hash to be more secure
        $this->_oauthParams['oauth_body_hash'] = $this->_getRequestHash();
        
        $this->_generateOauthSignature('POST');

        $headerValue = array();
        foreach ($this->_oauthParams as $key => $value) {
            $headerValue[] = Zend_Oauth_Http_Utility::urlEncode($key)
            . '="'
            . Zend_Oauth_Http_Utility::urlEncode($value) . '"';
        }
        return implode(",", $headerValue);
    }

    /**
     * Gets an HMAC-SHA1 signature for the request
     *
     * @param string $httpMethod
     * @return string
     */
    protected function _generateOauthSignature($httpMethod = 'POST')
    {
        $sig = new Zend_Oauth_Signature_Hmac($this->_getOauthConsumerSecret(), null, 'sha1');
        $signatureString = $sig->sign($this->_oauthParams, $httpMethod, $this->getUrl());

        $this->_oauthParams['oauth_signature'] = $signatureString;
        return $signatureString;
    }

    /**
     * Returns a hash that uniquely identifies this request
     *
     * @return string
     */
    protected function _getRequestHash()
    {
        $string = $this->getNativeRequest()->__toString();
//        $string = serialize($this);
        return base64_encode(sha1($string, true));
    }

    /**
     * Returns an instance of the native request object
     *
     * @return mixed
     */
    public function getNativeRequest()
    {
        $method = $this->getMethod();
        $params = $this->getParams();
        switch($this->getRequestFormat())
        {
            case 'local':
                $request = false;
                $request = $this;
                break;
            case 'xmlrpc':
                $request = new Zend_XmlRpc_Request($method, $params);
                break;
            case 'jsonrpc':
                $request = new Zend_Json_Server_Request();
                $request->setMethod($method);
                $request->setParams($params);
                $request->setId('1');
                $request->setVersion('2.0');
                break;
        }
        return $request;
    }
}
