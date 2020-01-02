<?php

// misc.
define('FRAMEWORK_START', microtime(true));
define('DS', DIRECTORY_SEPARATOR);
define('VERSION', '0.9.2');

// paths
define('BASE_PATH', realpath(__DIR__).DS);
define('SYSTEM_PATH', BASE_PATH.'system'.DS);
define('APP_PATH', BASE_PATH.'app'.DS);
define('STORAGE_PATH', BASE_PATH.'storage'.DS);
define('DATABASE_PATH', BASE_PATH.'database'.DS);
define('RESOURCE_PATH', BASE_PATH.'resources'.DS);
define('CONTROLLER_PATH', APP_PATH.'Controllers'.DS);
define('VIEW_PATH', RESOURCE_PATH.'views'.DS);
define('UPLOADS_PATH', STORAGE_PATH.'app'.DS.'uploads'.DS);
