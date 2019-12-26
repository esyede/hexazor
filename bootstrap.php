<?php

require __DIR__.'/constants.php';
defined('DS') or exit('No direct script access allowed.');

ini_get('date.timezone') or date_default_timezone_set('UTC');

require __DIR__.'/system/Core/Config.php';
require __DIR__.'/system/Support/polyfill.php';
require __DIR__.'/system/Debugger/autoload.php';
require __DIR__.'/system/Core/Helpers.php';
require __DIR__.'/system/Loader/Autoloader.php';

use System\Core\Config;
use System\Debugger\Debugger;
use System\Loader\Autoloader;

Config::init();

$logs = storage_path('system/logs');

if (!is_dir($logs) && false === mkdir($logs, 0777, true)) {
    throw new RuntimeException('Unable to create logs directory: '.$logs);
}

Debugger::enable(Debugger::DETECT || is_cli(), $logs);

unset($logs);

Autoloader::register();
