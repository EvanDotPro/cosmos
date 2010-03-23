<?php
class Backend_IndexController extends Zend_Controller_Action
{
	public function init()
	{
		if (!Zend_Auth::getInstance()->hasIdentity()){
		    return $this->_helper->redirector('index','login','backend');
		}
	}
	
    public function indexAction()
    {
    }
}