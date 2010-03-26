<?php
class Backend_LoginController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->layout->setLayout('login');
    }
    
    public function indexAction()
    {
    }
    
    public function authenticateAction()
    {
        print_r($_POST);die();
    }
}