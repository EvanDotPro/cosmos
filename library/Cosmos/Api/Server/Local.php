<?php
class Cosmos_Api_Server_Local extends Zend_Server_Abstract
{
    
    public function setClass($class, $namespace = '', $argv = null)
    {
        if (is_string($class) && !class_exists($class)) {
            throw new Cosmos_Api_Server_Exception('Invalid method class', 610);
        }

        $argv = null;
        if (2 < func_num_args()) {
            $argv = func_get_args();
            $argv = array_slice($argv, 2);
        }

        $dispatchable = Zend_Server_Reflection::reflectClass($class, $argv, $namespace);
        foreach ($dispatchable->getMethods() as $reflection) {
            $this->_buildSignature($reflection, $class);
        }
    }
    /**
     * Not implemented.
     * 
     * @param string $function
     * @param string $namespace
     */
    public function addFunction($function, $namespace = '')
    {
        return false;
    }
    
    protected function _fixType($type)
    {
        if (isset($this->_typeMap[$type])) {
            return $this->_typeMap[$type];
        }
        return $type;
    }
    
    public function fault($fault = null, $code = 404)
    {
        if (!$fault instanceof Exception) {
            $fault = (string) $fault;
            if (empty($fault)) {
                $fault = 'Unknown Error';
            }
            // require_once 'Zend/XmlRpc/Server/Exception.php';
            $fault = new Zend_XmlRpc_Server_Exception($fault, $code);
        }

        return Zend_XmlRpc_Server_Fault::getInstance($fault);
    }
    
    public function handle($request = false)
    {
        $callback = $this->getFunctions()->getMethod($request->getMethod())->getCallback();
        $className = $callback->getClass();
        $methodName = $callback->getMethod();
        return call_user_func_array(array($className, $methodName), $request->getParams());
    }
    
    public function cosmosHandle($request)
    {
        return $this->handle($request);
    }
    
    public function setPersistence($mode)
    {
    }
    
    public function loadFunctions($definition)
    {
        
    }
}