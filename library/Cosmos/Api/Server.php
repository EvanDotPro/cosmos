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
 * @subpackage Server
 * @copyright  Copyright (c) 2010 Cosmos Team (http://cosmoscommerce.org/)
 * @license    http://cosmoscommerce.org/license     Dual licensed under the MIT or GPL Version 2 licenses
 */

/**
 * Cosmos API server object
 *
 * An RPC server that sits on top of Zend_XmlRpc_Server and Zend_Json_Server allowing
 * for some really cool things like named parameters and mixing request formats with
 * different response formats (ie. request via XML but get response in JSON), and
 * supports OAuth request signing.
 *
 * @category   Cosmos
 * @package    Cosmos_Api
 * @subpackage Server
 * @copyright  Copyright (c) 2010 Cosmos Team (http://cosmoscommerce.org/)
 * @license    http://cosmoscommerce.org/license     Dual licensed under the MIT or GPL Version 2 licenses
 * @version    {{version}}
 */
class Cosmos_Api_Server
{
	protected $_request;

	protected $_internalServer;

	protected $_requestFormat;

	protected $_version;

	public function __construct(Cosmos_Api_Request $request = null)
	{
	    if($request !== null){
	        $this->setRequest($request);
	    }
    }

    public function setVersion($version)
	{
	    if($version == $this->_version){
	        return;
	    }
	    $path = APPLICATION_PATH . "/core/services/{$version}";
	    $file = "{$path}/_services.php";
	    if(!file_exists($file)){
			throw new Cosmos_Api_Server_Exception("Invalid API version specified: {$version}", 404);
		}
		$this->_version = $version;

		$this->_createInternalServer();
		$this->_bindMethods();
	}

	public function setRequest(Cosmos_Api_Request $request)
	{
	    $this->_request = $request;
	    $this->setVersion($this->_request->getVersion());
	}

	public function getRequest()
	{
	    return $this->_request;
	}

	public function setClass($class, $namespace)
	{
	    $this->_internalServer->setClass($class, $namespace);
	}

    public function getMethodTable()
	{
		switch($this->getRequest()->getRequestFormat())
		{
			case 'local':
			case 'jsonrpc':
				return $this->_internalServer->getFunctions();
				break;
			case 'xmlrpc':
			    return $this->_internalServer->getDispatchTable();
			    break;
		}
	}

	public function handle()
	{
	    $request = $this->getRequest();

	    $method = $request->getMethod();
	    $params = $request->getParams();

	    if(!$this->getMethodTable()->getMethod($method)){
	        throw new Cosmos_Api_Server_Exception("Invalid method requested: '{$method}'", 404);
	    }
	    $sigs = $this->getMethodTable()->getMethod($method)->getPrototypes();
		$methodSignature = array_pop($sigs);
		$parameters = $methodSignature->getParameterObjects();


		if(count($params) == 1 && is_array($params[0]) && count($params[0]) <= count($parameters)){
		    $isNameBased = true;
		} else {
		    $isNameBased = false;
		}

		/**
		 * TODO: There's probably a cleaner way to handle this.
		 * Basically, we're making _sure_ that they meant to pass
		 * named parameters.
		 */
		if($isNameBased == true){
		    $paramsClone = $params[0];
		    foreach($parameters as $index => $paramObj)
    		{
    		    $name = $paramObj->getName();

			    if(isset($paramsClone[$name])){
			        unset($paramsClone[$name]);
			    }
    		}
    		if(count($paramsClone) > 0){
    		    $isNameBased = false;
    		    unset($paramsClone);
    		}
		}

		/**
		 * Now we're sure about if we should be handling them as
		 * named params or ordered params.
		 */
	    $paramsToPass = array();
		foreach($parameters as $index => $paramObj)
		{
		    $name = $paramObj->getName();
		    $type = $paramObj->getType();

		    if($isNameBased == true && isset($params[0][$name])){
		        $value = $params[0][$name];
		    } elseif($isNameBased == false && isset($params[$index])){
		        $value = $params[$index];
		    } else {
    		    if($paramObj->isOptional()){
			        $value = $paramObj->getDefaultValue();
			    } else {
			        throw new Cosmos_Api_Server_Exception("Required parameter '{$name}' not passed to '{$method}'", 404);
			    }
		    }
		    $paramsToPass[$index] = $value;
		}

//		return array('namebased'=>$isNameBased,'method'=>$method,'params'=>$paramsToPass);
//	    $request = array('method' => $method, 'params' => $params);
        $request->setParams($paramsToPass);
	    $responseObject = $this->_internalServer->cosmosHandle($request);
	    return $responseObject;
	    if($request->getRequestFormat() == $request->getResponseFormat()){
	        return $request->getRequestFormat() .' == '.  $request->getResponseFormat();
	        return $responseObject->__toString();
	    } else {
    	    $cosmosResponse = new Cosmos_Api_Response($responseObject, $request->getResponseFormat());
	    }
	    return $cosmosResponse;
	}

	protected function _bindMethods()
	{
	    if(!$this->_version){
	        return false;
	    }
	    $path = APPLICATION_PATH . "/core/services/{$this->_version}";
		$config = include "{$path}/_services.php";
		if($config === null){
		    return false;
		}
		foreach($config as $namespace => $service)
		{
		    require_once "{$path}/{$service['file']}";
		    $this->setClass($service['class'], $namespace);
		}
	}

	protected function _createInternalServer()
	{
	    switch($this->getRequest()->getRequestFormat()){
	        case 'local':
	            $this->_internalServer = new Cosmos_Api_Server_Local();
	            break;
	        case 'xmlrpc':
	            $this->_internalServer = new Cosmos_Api_Server_XmlRpc();
	            break;
	        case 'jsonrpc':
	            $this->_internalServer = new Cosmos_Api_Server_JsonRpc();
	            break;
		}
	}
}