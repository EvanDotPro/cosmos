<?php
/**
 * Cosmos Commerce
 *
 * LICENSE
 *
 * This source file is dual licensed under the MIT or GPL Version 2
 * licenses that are bundled with this package in the files 
 * GPL-LICENSE.txt and MIT-LICENSE.txt.
 * A copy is also available through the world-wide-web at this URL:
 * http://cosmoscommerce.org/license
 * If you did not receive a copy of these licenses and are unable
 * to obtain a copy through the world-wide-web, please send an email
 * to license@cosmoscommerce.org so we can send you a copy immediately.
 *
 * @category   Cosmos
 * @package    Cosmos_Bootstrap
 * @copyright  Copyright (c) 2010 Cosmos Team (http://cosmoscommerce.org/)
 * @license    http://cosmoscommerce.org/license     Dual licensed under the MIT or GPL Version 2 licenses
 */

// Define path to application directory
defined('APPLICATION_PATH')
	|| define('APPLICATION_PATH',
			realpath(dirname(__FILE__) . '/../application'));

// Define path to library directory
defined('LIBRARY_PATH')
	|| define('LIBRARY_PATH', 
	        realpath(dirname(__FILE__) . '/../library'));

// Define application environment
defined('APPLICATION_ENV')
	|| define('APPLICATION_ENV',
			(getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV')
											: 'production'));
											
// Define application environment
defined('APPLICATION_MODE')
	|| define('APPLICATION_MODE',
			(getenv('APPLICATION_MODE') ? getenv('APPLICATION_MODE')
											: 'local'));
// Define the host name requested
defined('APPLICATION_HOST')
	|| define('APPLICATION_HOST',
			(getenv('APPLICATION_HOST') ? getenv('APPLICATION_HOST')
											: $_SERVER['HTTP_HOST']));

define('REQUEST_MICROTIME', microtime(true));

// Set the include path
set_include_path(implode(PATH_SEPARATOR, array('.',LIBRARY_PATH)));

// Start up the profiler
require_once 'Cosmos/Profiler.php';
Cosmos_Profiler::enable();
Cosmos_Profiler::start('app');
    
// Bootsrap
Cosmos_Profiler::start('bootstrap');
try {
    require_once 'ZendC/Application.php';
    $application = new ZendC_Application(
    	APPLICATION_ENV,
    	APPLICATION_PATH . '/configs/all.ini'
    );
    $application->bootstrap();
} catch(Zend_Config_Exception $e){
    if($e instanceof Zend_Config_Exception){
        // This is a bit sloppy, but the front controller won't catch these otherwise
        $exceptionMessage = strtolower($e->getMessage());
        if(strpos($exceptionMessage, 'syntax error') !== false){
            $message = 'Configuration Error - Invalid Syntax';
        } elseif(strpos($exceptionMessage, 'no such file') !== false){
            $message = 'Configuration Error - Invalid Host';
        } else {
            $message = 'Configuration Error - Unknown Error';
        }
    } else {
        var_dump($e);
        $message = 'hello';
    }
    die($message);
}
Cosmos_Profiler::stop('bootstrap');

// Run
Cosmos_Profiler::start('run');
$response = $application->run();
Cosmos_Profiler::stop('run');

// Stop the profiler and send output to browser
Cosmos_Profiler::stop('app');

if(APPLICATION_MODE != 'server'){
    Cosmos_Profiler::_toFirebug();
    $response->sendResponse();
}