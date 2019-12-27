<?php

// misc.
defined('FRAMEWORK_START') or define('FRAMEWORK_START', microtime(true));
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('VERSION') or define('VERSION', '0.9.1');

// paths
defined('ROOT_PATH') or define('ROOT_PATH', realpath(__DIR__).DS);
defined('SYSTEM_PATH') or define('SYSTEM_PATH', ROOT_PATH.'system'.DS);
defined('APP_PATH') or define('APP_PATH', ROOT_PATH.'app'.DS);
defined('STORAGE_PATH') or define('STORAGE_PATH', ROOT_PATH.'storage'.DS);
defined('DATABASE_PATH') or define('DATABASE_PATH', ROOT_PATH.'database'.DS);
defined('RESOURCES_PATH') or define('RESOURCES_PATH', ROOT_PATH.'resources'.DS);
defined('CONTROLLER_PATH') or define('CONTROLLER_PATH', APP_PATH.'Controllers'.DS);
defined('MODEL_PATH') or define('MODEL_PATH', APP_PATH.'Models'.DS);
defined('VIEW_PATH') or define('VIEW_PATH', RESOURCES_PATH.'views'.DS);
defined('UPLOADS_PATH') or define('UPLOADS_PATH', STORAGE_PATH.'app'.DS.'uploads'.DS);

$basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1));
defined('BASE_PATH') or define('BASE_PATH', $basepath);
unset($basepath);
