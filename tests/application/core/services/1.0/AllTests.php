<?php
require_once 'PHPUnit/Framework.php';
require_once 'CosmosTest.php';
 
class Application_Core_Services_1_0_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Application_Core_Services_1_0');
 
        $suite->addTestSuite('CosmosTest');
 
        return $suite;
    }
}
?>