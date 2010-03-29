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
 * @subpackage Client
 * @copyright  Copyright (c) 2010 Cosmos Team (http://cosmoscommerce.org/)
 * @license    http://cosmoscommerce.org/license     Dual licensed under the MIT or GPL Version 2 licenses
 */

/**
 * Cosmos API client object
 *
 * An RPC client that abstracts Zend's XmlRpc client and adds the ability to use
 * JSON-RPC, or call RPC methods 'locally' directly via PHP. It also allows for
 * some really cool things like named parameters, mixing request formats with
 * different response formats (ie. request via XML but get response in JSON) and
 * also implements OAuth request signing for non-local API requests.
 *
 * @category   Cosmos
 * @package    Cosmos_Api
 * @subpackage Client
 * @copyright  Copyright (c) 2010 Cosmos Team (http://cosmoscommerce.org/)
 * @license    http://cosmoscommerce.org/license     Dual licensed under the MIT or GPL Version 2 licenses
 * @version    {{version}}
 */
class Cosmos_Api_Client
{
	/**
	 * Internal client 
	 * @var mixed
	 */
    protected $_internalClient;
	
	/**
	 * An instance of Cosmos_Api_Request
	 * @var Cosmos_Api_Request
	 */
    protected $_request;
    
    /**
     * A cache of the proxy objects and their namespaces
     * @var array
     */
    protected $_proxyCache;
	
    /**
     * Takes the API configuration array from the INI and
     * starts a new instance of the API client
     * 
     * @param array $config
     * @return void
     * @throws Cosmos_Api_Client_Exception|Cosmos_Api_Exception
     */
    public function __construct($config)
    {
        if(!isset($config['consumerKey']) || !$config['consumerKey']){
            throw new Cosmos_Api_Client_Exception("Cannot start API client without a consumer key.");
        }
        
        if(!isset($config['requestFormat']) || !$config['requestFormat']){
            throw new Cosmos_Api_Client_Exception("Cannot start API client without specifying a request format.");
        }
        
        $this->_request = new Cosmos_Api_Request();
        
        $this->_request->setRequestFormat($config['requestFormat']);
        
        $this->_request->setOauthParam('oauth_consumer_key', $config['consumerKey']);
        $this->_request->setOauthParam('oauth_signature_method', 'HMAC-SHA1');
        $this->_request->setOauthParam('oauth_version', '1.0');
        
        $format = $this->_request->getRequestFormat();
        
        if($format == 'local'){
            if(!isset($config['version']) || !$config['version']){
                throw new Cosmos_Api_Client_Exception("Using local mode requires an API version number to be specified.");
            }
            if(isset($config['responseFormat'])){
                Zend_Registry::get('log')->info('Notice: The API responseFormat configuration parameter is ignored when you are using local request mode.');
            }
            if(isset($config['consumerSecret'])){
                Zend_Registry::get('log')->info('Notice: There is no need to specify the API consumerSecret when using local mode.');
            }
            if(isset($config['url'])){
                Zend_Registry::get('log')->info('Notice: There is no need to specify the API URL when using local mode.');
            }
            $this->_request->setVersion($config['version']);
            $this->_request->setResponseFormat($format);
        } else {
            if(!isset($config['url']) || !$config['url']){
                throw new Cosmos_Api_Client_Exception("Using {$format} mode requires an API URL to be specified.");
            }
            if(!isset($config['consumerSecret']) || !$config['consumerSecret']){
                throw new Cosmos_Api_Client_Exception("Using {$format} mode requires the consumer secret to be specified.");
            }
            if(isset($config['version'])){
                Zend_Registry::get('log')->info('Notice: The API version configuration parameter is ignored unless you are using local request mode.');
            }
            $this->_request->setUrl($config['url']);
            $this->_request->setOauthConsumerSecret($config['consumerSecret']);
        }
        
        switch($format)
        {
            case 'local':
                $this->_internalClient = new Cosmos_Api_Client_Local($config['version']);
                break;
            case 'xmlrpc':
                $this->_internalClient = new Cosmos_Api_Client_XmlRpc($config['url']);
                break;
            case 'jsonrpc':
                $this->_internalClient = new Cosmos_Api_Client_JsonRpc($config['url']);
                break;
        }
    }
    
    /**
     * Makes an API request and returns the result
     * 
     * @param string $method
     * @param array $params
     */
    public function call($method, $params)
    {
        $this->_request->setMethod($method);
        $this->_request->setParams($params);
        
        try {
            $return = $this->_internalClient->cosmosRequest($this->_request);
        } catch(Exception $e){
            Zend_Debug::dump($e);die();
//            $return = array();
//            $return['request'] = $this->_internalClient->getHttpClient()->getLastRequest();
//            $return['response'] = $this->_internalClient->getHttpClient()->getLastResponse();
        }
//        $return = array();
//        $return['request'] = $this->_internalClient->getHttpClient()->getLastRequest();
//        $return['response'] = $this->_internalClient->getHttpClient()->getLastResponse();
        return $return;
    }

    /**
     * Returns a proxy object for more convenient method calls
     * (This great idea is from Zend_XmlRpc_Client_ServerProxy)
     *
     * @param $namespace  Namespace to proxy or empty string for none
     * @return Cosmos_Api_Client_ServerProxy
     */
    public function getProxy($namespace = '')
    {
        if (empty($this->_proxyCache[$namespace])) {
            $proxy = new Cosmos_Api_Client_ServerProxy($this, $namespace);
            $this->_proxyCache[$namespace] = $proxy;
        }
        return $this->_proxyCache[$namespace];
    }

}