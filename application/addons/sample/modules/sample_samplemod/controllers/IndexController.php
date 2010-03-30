<?php
class Sample_Samplemod_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        Cosmos_Profiler::start('sampleIndex');
        $this->view->result = Cosmos_Addon::getInstance()->listEnabledAddons();
        Cosmos_Profiler::stop('sampleIndex');
    }
    
    public function testAction()
    {
        
    }
}