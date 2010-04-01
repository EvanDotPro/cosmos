<?php

$config['phpSettings']['date']['timezone'] = 'America/Phoenix';

$config['autoloadernamespaces'][] = 'Cosmos_';
$config['autoloadernamespaces'][] = 'ZendC_';

$config['resources']['multidb']['master']['adapter']    = 'pdo_mysql';
$config['resources']['multidb']['master']['host']       = 'localdev';
$config['resources']['multidb']['master']['username']   = 'cosmos';
$config['resources']['multidb']['master']['password']   = 'cosmos';
$config['resources']['multidb']['master']['dbname']     = 'cosmos';
$config['resources']['multidb']['master']['default']    = true;
$config['resources']['multidb']['master']['isDefaultTableAdapter'] = true;

$config['resources']['multidb']['slave1']['adapter']    = 'pdo_mysql';
$config['resources']['multidb']['slave1']['host']       = 'localdev';
$config['resources']['multidb']['slave1']['username']   = 'cosmos';
$config['resources']['multidb']['slave1']['password']   = 'cosmos';
$config['resources']['multidb']['slave1']['dbname']     = 'cosmos';

$config['resources']['session']['name'] = 'cosmos';