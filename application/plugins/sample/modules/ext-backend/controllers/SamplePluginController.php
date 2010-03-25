<?php
class Backend_SamplePluginController extends Zend_Controller_Action
{
	public function init()
	{
//		if (!Zend_Auth::getInstance()->hasIdentity()){
//		    return $this->_helper->redirector('index','login','backend');
//		}
	    $this->_filename = APPLICATION_PATH . '/data/content.txt';
	}
	
    public function indexAction()
    {
        $this->view->content = file_get_contents($this->_filename);
    }
    
    public function updateContentAction()
    {
        file_put_contents($this->_filename, $_POST['content']);
        return $this->_helper->redirector('index','sample-plugin','backend');
    }
}