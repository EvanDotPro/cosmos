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
 * Cosmos API response object
 *
 * Encapsulates an API response.
 *
 * @category   Cosmos
 * @package    Cosmos_Api
 * @copyright  Copyright (c) 2010 Cosmos Team (http://cosmoscommerce.org/)
 * @license    http://cosmoscommerce.org/license     Dual licensed under the MIT or GPL Version 2 licenses
 * @version    {{version}}
 */
class Cosmos_Api_Response
{
    /**
     * Response value
     * @var mixed
     */
    protected $_responseValue;
    
    protected $_responseFormat;
    
    /**
     * 
     * @param mixed $response
     */
    public function __construct($response, $responseFormat)
    {
        if($response instanceof Zend_Json_Server_Response){
            if($response->isError()){
                var_dump($this->getError());die('JSON ERROR!');
            }
            echo $response->__toString();
            die();
        } elseif($response instanceof Zend_XmlRpc_Response){ 
            if($response->isFault()){
                var_dump($response->getFault());die('XMLRPC ERROR!');
            }
            
        }else {
            $this->setResponseValue($response);
            var_dump($this->getResponseValue());die();
        }
    }
    
    public function setResponseValue($value)
    {
        $this->_responseValue = $value;
    }
    
    public function getResponseValue()
    {
        return $this->_responseValue;
    }
    
    public function loadResponseString($string, $format)
    {
        switch($format){
            case 'xmlrpc':
                
                break;
            
            
        }
    }
    
    public function setInternalResponseObject($requestObject)
    {
        $this->_internalResponseObject = $requestObject;
    }
    
    public function getInternalResponseObject()
    {
        return $this->_internalResponseObject;
    }
    
    public function __toString()
    {
        switch($this->_responseFormat){
            case 'xmlrpc':
                return $this->getInternalResponseObject()->__toString();
                break;
            
            
        }
    }
    
    
}