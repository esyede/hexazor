<?php

namespace System\Support\Faker;

defined('DS') or exit('No direct script access allowed.');

use Exception;
use System\Core\Config;
use System\Facades\Storage;
use System\Support\Str;
use InvalidArgumentException;

class Module
{
    protected function loadData($name, $locale = null)
    {
        $variableName = Str::camel($name);

        if (null == $this::$$variableName) {
            if (null == $locale) {
                $locale = setlocale(LC_ALL, null);
            }

            try {
                $defaultLang = Config::get('app.default_language', 'en');
                $path = resources_path('lang/'.$defaultLang.'/faker/'.$name.'.php');
                $config = Storage::getRequire($path);

                if (is_null($config)) {
                    throw new Exception('Pop goes default locale');
                }

                $this::$$variableName = $config;

                if (0 == count($this::$$variableName)) {
                    throw new Exception('Empty locale data file.');
                }
            } catch (Exception $e) {
                if (Config::get('app.default_language') == $locale) {
                    throw new Exception('Could not find locale data file: '.$locale);
                } else {
                    $this::loadData($name, $defaultLang);
                }
            }
        }
    }

    protected function pickOne($name)
    {
        $variableName = Str::camel($name);
        $this::loadData($name);
        $block = &$this::$$variableName;

        return $block[array_rand($block)];
    }

    protected static function numberize($format, $min = 0, $max = 9)
    {
        $results = [];
        for ($i = substr_count($format, '%d'); $i > 0; --$i) {
            $results[] = mt_rand($min, $max);
        }

        return vsprintf($format, $results);
    }

    public function __get($name)
    {
        $name = Str::camel($name);

        if (!method_exists($this, $name)) {
            throw new InvalidArgumentException('Undefined faker property: '.$name);
        }

        return $this->$name();
    }
}
