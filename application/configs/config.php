<?php
$config = array();
$file = dirname(__FILE__) . '/all.base.config.php';
include $file;

$file = dirname(__FILE__) . '/all.' . APPLICATION_ENV . '.config.php';
include $file;

$file = dirname(__FILE__) . '/' . APPLICATION_MODE . '.base.config.php';
include $file;


$file = dirname(__FILE__) . '/' . APPLICATION_MODE . '.' . APPLICATION_ENV . '.config.php';
include $file;

$file = dirname(__FILE__) . '/sites/' . APPLICATION_HOST . '.base.config.php';
include $file;

$file = dirname(__FILE__) . '/sites/' . APPLICATION_HOST . '.' . APPLICATION_ENV . '.config.php';
include $file;

return $config;