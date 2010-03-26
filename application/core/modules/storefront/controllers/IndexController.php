<?php
class Storefront_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        Cosmos_Profiler::start('calc.multiply');
        $this->view->result = Cosmos_Api::get()->calc->divide(10,2);
        Cosmos_Profiler::stop('calc.multiply');
    }
}