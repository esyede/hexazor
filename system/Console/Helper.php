<?php

namespace System\Console;

defined('DS') or exit('No direct script access allowed.');

class Helper
{
    const IS_COMMAND = 'IS_COMMAND';
    const IS_ARGUMENT = 'IS_ARGUMENT';
    const IS_OPTION = 'IS_OPTION';


    public static function getAllCommands()
    {
        $commands = [];
        $skipped = [
            'System\Console\Generators\GeneralFile',
            'System\Console\Generators\MigrationFile',
        ];
        
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, '\System\Console\Command')) {
                if (in_array($class, $skipped)) {
                    continue;
                }

                $object = new $class;
                $command = $object->getCommand();
                $commands[$command] = $class;
            }
        }

        return $commands;
    }


    public static function parseSignature($signature)
    {
        $signature = explode(' ', trim($signature));
        $command = trim($signature[0]);
        $arguments = [];
        $options = [];

        foreach ($signature as $word) {
            $type = self::determineTypeOfWord($word);

            if ($type == self::IS_OPTION) {
                list($key, $defaultValue) = self::parse($word);
                $options[$key] = $defaultValue;
            } elseif ($type == self::IS_ARGUMENT) {
                list($key, $defaultValue) = self::parse($word);
                $arguments[$key] = $defaultValue;
            }
        }

        return [$command, $arguments, $options];
    }


    public static function runCommand($command, array $arguments = null, array $options = null)
    {
        $command = new $command($arguments, $options);
        $command->update($arguments, $options);
        $command->handle();
    }


    public static function parse($word)
    {
        $word = ltrim(rtrim(trim($word), '}'), '{');

        if ($separatorPosition = strpos($word, '=')) {
            $key = substr($word, 0, $separatorPosition);
            $defaultValue = substr($word, $separatorPosition + 1);
            return [$key, $defaultValue];
        }

        return [$word, null];
    }


    public static function determineTypeOfWord($word)
    {
        $word = ltrim(rtrim(trim($word), '}'), '{');

        if (substr($word, 0, 2) == '--') {
            return self::IS_OPTION;
        } elseif (substr($word, 0, 1) == ':') {
            return self::IS_ARGUMENT;
        }

        return self::IS_COMMAND;
    }
}
