<?php
class ZendC_Json_Server_Request_Http extends Zend_Json_Server_Request_Http
{
    protected $_isParseError = false;
     
    public function __construct()
    {
        try {
            parent::__construct();
        } catch (Zend_Json_Exception $e) {
            $this->_isParseError = true;
        }
        
    }
    
    public function isParseError()
    {
        return $this->_isParseError;
    } 
}