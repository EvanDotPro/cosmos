<?php
require_once(APPLICATION_PATH . '/core/modules/backend/controllers/LoginController.php');
class Backend_LoginExtController extends Backend_LoginController
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