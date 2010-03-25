<?php
class Storefront_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        Cosmos_Profiler::start('calc.multiply');
        $this->view->result = Zend_Registry::get('api')->calc->divide(10,2);
        Cosmos_Profiler::stop('calc.multiply');
    }
}