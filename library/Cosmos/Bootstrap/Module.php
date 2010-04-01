<?php
class Cosmos_Bootstrap_Module extends Zend_Application_Module_Bootstrap
{
    public function __construct($application)
    {
        parent::__construct($application);
        if(isset($this->_file)) {
            // load config file
            $configFile = dirname($this->_file) . '/configs/config.php';
            if (Zend_Loader::isReadable($configFile)) {
                // Set this bootstrap options
                $this->setOptions(include $configFile);
            }
        }
    }
}