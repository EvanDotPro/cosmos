<?php

define('APPLICATION_PATH', '../application');

/*
 * Prepend the Zend Framework library/ and tests/ directories to the
 * include_path. This allows the tests to run out of the box and helps prevent
 * loading other copies of the framework code and tests that would supersede
 * this copy.
 */
$path = array(get_include_path(), '../application');
set_include_path(implode(PATH_SEPARATOR, $path));

require_once 'PHPUnit/Framework.php';
require_once 'application/core/services/1.0/AllTests.php';
require_once APPLICATION_PATH . '/core/services/1.0/Cosmos.php';

class AllTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('Cosmos');

		$suite->addTest(Application_Core_Services_1_0_AllTests::suite());

		return $suite;
	}
}