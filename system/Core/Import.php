<?php

namespace System\Core;

defined('DS') or exit('No direct script access allowed.');

use RuntimeException;

class Import
{
    /**
     * Impor file.
     *
     * @param string $path
     *
     * @return bool
     */
    public static function file($path)
    {
        $path = str_replace(['/', '\\'], [DS, DS], $path);
        $path = rtrim($path, '.php').'.php';

        if (!is_file(ROOT_PATH.$path)) {
            throw new RuntimeException('File not found: '.$path);
        }

        return require_once $path;
    }

    /**
     * Impor file config.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return bool
     */
    public static function config($name, $default = null)
    {
        return Config::get($name, $default);
    }

    /**
     * Impor file language.
     *
     * @param string $name
     *
     * @return array
     */
    public static function language($name)
    {
        $name = strtolower($name);
        $default = $config = static::config('app.default_language');
        $path = resources_path('lang/'.$default.'/'.$name.'.php');

        if (!is_file($path)) {
            throw new RuntimeException('Language file not found: '.$path);
        }

        return require_once $path;
    }
}
