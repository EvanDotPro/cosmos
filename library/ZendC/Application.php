<?php
require_once 'Zend/Application.php';
class ZendC_Application extends Zend_Application
{
	/**
	 * This is because the ZF's run() doesn't return the response
	 * @see library/Zend/Zend_Application#run()
	 */
	public function run()
	{
	    switch (APPLICATION_MODE)
	    {
	        case 'client':
        		return $this->getBootstrap()->run();
	            break;
	        case 'server':
	            echo 'yay run api method!';
	            break;
	    }
	}
}