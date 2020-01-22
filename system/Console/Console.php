<?php

namespace System\Console;

defined('DS') or exit('No direct script access allowed.');

use System\Core\Application;
use System\Core\Config;

class Console
{
    private static $commands;
    private static $initialized = false;
    private static $silentMode = false;
    private static $filename;


    private static function initialize()
    {
        if (!static::$initialized) {
            static::$filename = strtolower(Application::PACKAGE);
            static::$commands = Helper::getAllCommands();
        }
    }


    public static function listen($argv)
    {
        static::initialize();

        if (blank($argv)) {
            $argv = is_cli() ? $GLOBALS['argv'] : $_GET;
        }

        static::$filename = $argv[0];

        if (!Config::get('app.application_key', null)) {
            static::callSilent('key:generate');
        }

        if (isset($argv[1])) {
            $command = $argv[1];
        } else {
            $command = 'help';
        }

        if (!in_array($command, array_keys(static::$commands))) {
            $message = 'Command not found: '.$command.PHP_EOL;
            print is_cli() ? $message : '<pre>'.$message.'</pre>';
            return false;
        }

        $arguments = [];
        $options = [];

        for ($index = 2; $index < count($argv); $index++) {
            list($key, $value) = Helper::parse($argv[$index]);

            if (Helper::determineTypeOfWord($argv[$index]) == Helper::IS_OPTION) {
                if (!$value) {
                    $options[$key] = true;
                } else {
                    $options[$key] = $value;
                }
            } else {
                $arguments[] = $key;
            }
        }

        Helper::runCommand(static::$commands[$command], (array) $arguments, $options);
    }

    public static function register($directory)
    {
        static::initialize();

        $directory = rtrim(rtrim($directory, '/'), '\\').DS;
        $files = scandir($directory);

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            require_once $directory.$file;
        }
    }


    public static function getCommands()
    {
        return static::$commands;
    }


    public static function call($command, $arguments = null, array $options = null)
    {
        $arguments = is_array($arguments) ? $arguments : [$arguments];

        if (!isset(static::$commands[$command])) {
            $message = 'Command not found: '.$command.PHP_EOL;
            print is_cli() ? $message : '<pre>'.$message.'</pre>';
            return false;
        }

        Helper::runCommand(static::$commands[$command], $arguments, $options);
    }


    public static function callSilent($command, $arguments = null, array $options = null)
    {
        $silent = static::isSilentMode();
        static::setSilentMode(true);
        static::call($command, $arguments, $options);
        static::setSilentMode($silent);
    }


    public static function setSilentMode($silent = true)
    {
        static::$silentMode = (bool) $silent;
    }


    public static function isSilentMode()
    {
        return (true === static::$silentMode);
    }

    public static function isVerbose()
    {
        return !static::isSilentMode();
    }


    public static function getFileName()
    {
        return static::$filename;
    }
}
