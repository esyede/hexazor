<?php

namespace System\Support;

defined('DS') or exit('No direct script access allowed.');

use InvalidArgumentException;

class Env
{
    public $files = [];

    protected $lastFile;

    protected $overloader = false;

    private static $loader;

    /**
     * Muat file .env.
     *
     * @param string $file
     */
    public static function load($file = '.env')
    {
        self::getLoader()->innerLoad($file);
    }

    /**
     * Set variabel ke env.
     *
     * @param string $key
     * @param mixed  $val
     */
    public static function put($key, $val)
    {
        if (false == self::getLoader()->overloader) {
            if (self::get($key)) {
                return;
            }
        }

        putenv($key.'='.$val);

        $_ENV[$key] = $val;
        $_SERVER[$key] = $val;
    }

    /**
     * Ambil data dari env.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function get($key, $default = false)
    {
        switch (true) {
            case array_key_exists($key, $_ENV):
                return (null !== $_ENV[$key]) ? $_ENV[$key] : $default;

            case array_key_exists($key, $_SERVER):
                return (null !== $_SERVER[$key]) ? $_SERVER[$key] : $default;

            default:
                $value = getenv($key);

                return (false !== $value) ? $value : $default;
        }
    }

    /**
     * Timpa data env.
     *
     * @param string $file
     */
    public static function overload($file = '.env')
    {
        self::getLoader()->overloader = true;
        self::getLoader()->innerLoad($file);
    }

    /**
     * Ambil objek kelas ini.
     *
     * @return self
     */
    public static function getLoader()
    {
        if (!self::$loader instanceof self) {
            self::$loader = new self();
        }

        return self::$loader;
    }

    /**
     * Load konten env file.
     *
     * @param string $file
     */
    protected function innerLoad($file)
    {
        $this->lastFile = $file;

        if (!is_readable($this->lastFile) || !is_file($this->lastFile)) {
            $message = sprintf('Env: The [%s] file not found or not readable', $this->lastFile);

            throw new InvalidArgumentException($message);
        }

        $this->files[$file] = $file;

        $autoDetectLineEndings = ini_get('auto_detect_line_endings');
        $rows = file($this->lastFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

        ini_set('auto_detect_line_endings', '1');
        ini_set('auto_detect_line_endings', $autoDetectLineEndings);

        foreach ($rows as $row) {
            $data = $this->filter($row);
            if (!is_null($data)) {
                list($key, $val) = $data;
                $val = $this->handleSemanticsVariable($val);
                $this->put($key, $val);
            }
        }
    }

    /**
     * Petakan item di file env mwnjadi array.
     *
     * @param string $var
     *
     * @return array|null
     */
    protected function filter($var)
    {
        switch (true) {
            case 0 === strpos(trim($var), '#'):
                return;

            case false !== strpos($var, '='):
                list($key, $val) = array_map('trim', explode('=', $var, 2));

                return [$key, $val];

            default:
                return;
        }
    }

    /**
     * Tangani semantic variable.
     *
     * @param string $variable
     *
     * @return string
     */
    protected function handleSemanticsVariable($variable)
    {
        list($variable) = array_map('trim', explode('#', $variable));
        switch (true) {
            case '' === $variable:
                return $variable;

            case is_numeric($variable):
                return $variable;

            default:
                $begining = substr($variable, 0, 1);
                if (in_array($begining, ['\'', '"'])
                && $begining == substr($variable, -1, 1)) {
                    return substr($variable, 1, -1);
                }

                return $variable;
        }
    }
}
