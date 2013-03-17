<?php 

define('ROOT_DIR', realpath(dirname(__FILE__).'/../').'/');
define('CONTENT_DIR', ROOT_DIR .'content/');
define('LIB_DIR', ROOT_DIR .'api/lib/');

// Enable for debugging
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
