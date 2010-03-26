<?php
class Cosmos_Bootstrap_Module extends Zend_Application_Module_Bootstrap
{
    public function __construct($application)
    {
        parent::__construct($application);
        // load ini file
        $iniFile = dirname($this->_file) . '/configs/module.ini';
        if (Zend_Loader::isReadable($iniFile)) {
            $iniOptions = new Zend_Config_Ini($iniFile);
            // Set this bootstrap options
            $this->setOptions($iniOptions->toArray());
        }
    }
}