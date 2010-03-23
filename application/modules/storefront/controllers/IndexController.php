<?php
class Storefront_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        
        Cosmos_Profiler::start('calc.multiply');
//        $this->view->result = Zend_Registry::get('api')->calc->divide(array('y'=>2,'x'=>10));
//        $this->view->result = Zend_Registry::get('api')->calc->divide(2,10);
        $this->view->result = Zend_Registry::get('api')->calc->divide(10,2);
//        $this->view->result = Zend_Registry::get('api')->calc->divide('hello',2,3);
        Cosmos_Profiler::stop('calc.multiply');
    }
}