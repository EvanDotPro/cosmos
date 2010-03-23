<?php
class Cosmos_Api_Server_JsonRpc extends Zend_Json_Server
{
    public function __construct()
    {
        $this->setAutoEmitResponse(false);
        parent::__construct();
    }
    
    public function handle($request = false)
    {
        if ($request->isParseError()) {
            $this->fault('Parse error', -32700);
        }
        return parent::handle($request);
    }

    
    public function cosmosHandle($request)
    {
        die('hehsdfghs');
        return $this->handle($request->getInternalRequest());
    }
}