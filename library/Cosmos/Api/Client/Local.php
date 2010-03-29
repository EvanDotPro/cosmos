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
 * Cosmos 'local' API client
 *
 * This class works as a sort of proxy to allow an instance of Cosmos
 * to use the local library code to respond to API calls instead of
 * going through the XML/JSON/etc service layer.
 *
 * @category   Cosmos
 * @package    Cosmos_Api
 * @subpackage Client
 * @copyright  Copyright (c) 2010 Cosmos Team (http://cosmoscommerce.org/)
 * @license    http://cosmoscommerce.org/license     Dual licensed under the MIT or GPL Version 2 licenses
 * @version    {{version}}
 */
class Cosmos_Api_Client_Local
{
    /**
     * Instance of Cosmos API server
     * @var Cosmos_Api_Server
     */
    protected $_server;
    
    /**
     * Creates a new instance of a 'local' API client
     * 
     * @return void
     */
    public function __construct()
    {
        $this->_server = new Cosmos_Api_Server();
        Zend_Registry::set('server', $this->_server);
    }
    
    /**
     * This is a universal method all client types must implement to
     * take a Cosmos_Api_Request object and make the call via the
     * underlying client object.
     * 
     * @param Cosmos_Api_Request $request
     * @return Cosmos_Api_Response
     */
    public function cosmosRequest(Cosmos_Api_Request $request)
    {
        $apiKey = Cosmos_Api::getApiKey($request->getOauthParam('oauth_consumer_key'));
		if(!$apiKey){
		    Zend_Debug::dump($apiKey);
//			throw new Cosmos_Api_Exception('Unauthorized - Bad consumer key provided', 401);
		}
        $this->_server->setRequest($request);
        $response = $this->_server->handle();
        return $response;
//        return $response->getResponseValue();
    }
    
}