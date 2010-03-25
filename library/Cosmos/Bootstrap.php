<?php
class Cosmos_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function initDatabase()
    {
        $resource = $this->getPluginResource('multidb');
        if($resource == NULL){
            return;
        }
        $this->bootstrap('multidb');
        
        $masterDb = $resource->getDefaultDb(true);
        Zend_Registry::set('dbw',$masterDb);
    }
    
    public function initLog()
    {
        $options = $this->getOptions();
        
        if(isset($options['cosmos']['logToFirebug']) && $options['cosmos']['logToFirebug'] == true){
            $writer = new Zend_Log_Writer_Firebug();
            $logger = new Zend_Log($writer);
            $writer->setPriorityStyle(8, 'TABLE');
        } else {
            if($this->getPluginResource('log')){
                $logger = $this->getPluginResource('log')->getLog();
            }
        }
        $logger->addPriority('TABLE', 8);
        Zend_Registry::set('log', $logger);
    }
    
    public function initServerSession()
    {
        $this->bootstrap('session');
        
        $options = $this->getOptions();
        $groupID = $options['cosmos']['groupID'];
        
        $registry = Zend_Registry::getInstance();
        $registry['s.cart'] = new Zend_Session_Namespace("cart_{$groupID}");
        
        Zend_Registry::set('asession', new Zend_Session_Namespace('apisession'));
        Zend_Registry::set('csession', new Zend_Session_Namespace('clientsession'));
    }
}