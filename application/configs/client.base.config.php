<?php

$config['bootstrap']['path']    = APPLICATION_PATH . '/ClientBootstrap.php';
$config['bootstrap']['class']   = 'ClientBootstrap';

$config['resources']['layout']  = array();
$config['resources']['modules'] = array();

$config['resources']['frontController']['moduleDirectory'] = APPLICATION_PATH . '/core/modules';
$config['resources']['frontController']['defaultModule'] = 'storefront';
$config['resources']['frontController']['throwExceptions'] = false;
$config['resources']['frontController']['returnResponse'] = true;
$config['resources']['frontController']['prefixDefaultModule'] = true;
$config['resources']['frontController']['plugins'][] = 'ZendC_Controller_Plugin_CosmosAddonLoader';

$config['resources']['session']['save_path'] = APPLICATION_PATH . '/data/sessions';

$config['resources']['log']['stream']['writerName'] = 'Stream';
$config['resources']['log']['stream']['writerParams']['stream'] = APPLICATION_PATH . '/data/logs/client.log';
$config['resources']['log']['stream']['writerParams']['mode'] = 'a';
$config['resources']['log']['stream']['filterName'] = 'Priority';
$config['resources']['log']['stream']['filterParams']['priority'] = 4;

$config['cosmos']['namespace'] = 'cosmos';
$config['cosmos']['theme'] = 'default';

$config['cosmos']['sharedsession']['enabled'] = true;
$config['cosmos']['sharedsession']['host'] = 'cosmos';
$config['cosmos']['sharedsession']['ssl'] = true;

$config['cosmos']['api']['version'] = '1.0';

$config['cosmos']['api']['requestFormat'] = 'local';
