<?php
class ServerBootstrap extends Cosmos_Bootstrap
{
    protected function _initDatabase()
    {
        $this->initDatabase();
    }
    
	protected function _initApiServer()
	{
	    $server = new Cosmos_Api_Server(new Cosmos_Api_Request_Http());
	    var_dump($server->handle()); die('done');
	}
    
    protected function _initApiSession()
    {
        $this->initSession();
    }
    
	protected function _initLogger()
	{
		$this->initLog();
	}
}