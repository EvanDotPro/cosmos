<?php
class Backend_SampleContentController extends Zend_Controller_Action
{
	public function init()
	{
//		if (!Zend_Auth::getInstance()->hasIdentity()){
//		    return $this->_helper->redirector('index','login','backend');
//		}
	}
	
    public function indexAction()
    {
        $this->view->content = Cosmos_Api::get()->content->read();
    }
    
    public function updateContentAction()
    {
        Cosmos_Api::get()->content->write($_POST['content']);
        return $this->_helper->redirector('index','sample-plugin','backend');
    }
}