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
 * Cosmos API client server proxy
 *
 * This is an awesome idea from Zend_XmlRpc_Client_ServerProxy that utilizes
 * magic methods to make the API client interface VERY simple to use.
 * 
 * Example:
 * $result = $proxy->category->readNavigationCategories();
 * instead of:
 * $result = $client->call('category.readNavigationCategories', array());
 *
 * @category   Cosmos
 * @package    Cosmos_Api
 * @subpackage Client
 * @copyright  Copyright (c) 2010 Cosmos Team (http://cosmoscommerce.org/)
 * @license    http://cosmoscommerce.org/license     Dual licensed under the MIT or GPL Version 2 licenses
 * @version    {{version}}
 */
class Cosmos_Api_Client_ServerProxy
{
	/**
	 * This proxy's namespace
	 * @var string
	 */
	private $_namespace = '';
	
	/**
	 * The instance of the client this proxy is working for
	 * @var Cosmos_Api_Client
	 */
	private $_client = null;
	
	/**
	 * Cache of proxy namespaces
	 * @var array of proxy namespaces
	 */
	private $_cache = array();
	
	/**
	 * Creates a new API proxy instance
	 * 
	 * @param Cosmos_Api_Client $client
	 * @param string $namespace
	 */
	public function __construct(Cosmos_Api_Client $client, $namespace = '')
	{
		$this->_namespace = $namespace;
		$this->_client    = $client;
	}
	
	/**
	 * Magic __call method that calls the underlying API client
	 * 
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, $arguments)
	{
		$method = ltrim("$this->_namespace.$method", '.');
		return $this->_client->call($method, $arguments);
	}
	
	/**
	 * Magic __get method that allows for calling the proxy with
	 * a namespace as a property
	 * 
	 * @param string $namespace
	 */
	public function __get($namespace)
	{
		$namespace = ltrim("$this->_namespace.$namespace", '.');
		if (!isset($this->_cache[$namespace])) {
			$this->_cache[$namespace] = new $this($this->_client, $namespace);
		}
		return $this->_cache[$namespace];
	}
}