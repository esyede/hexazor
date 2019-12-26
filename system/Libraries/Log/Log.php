<?php

namespace System\Libraries\Log;

defined('DS') or exit('No direct script access allowed.');

use System\Support\Str;

class Log
{
    /**
     * Buat emergency log.
     *
     * @param string $message
     */
    public static function emergency($message)
    {
        static::write('emergency', $message);
    }

    /**
     * Buat alert log.
     *
     * @param string $message
     */
    public static function alert($message)
    {
        static::write('alert', $message);
    }

    /**
     * Buat critical log.
     *
     * @param string $message
     */
    public static function critical($message)
    {
        static::write('critical', $message);
    }

    /**
     * Buat error log.
     *
     * @param string $message
     */
    public static function error($message)
    {
        static::write('error', $message);
    }

    /**
     * Buat warning log.
     *
     * @param string $message
     */
    public static function warning($message)
    {
        static::write('warning', $message);
    }

    /**
     * Buat notice log.
     *
     * @param string $message
     */
    public static function notice($message)
    {
        static::write('notice', $message);
    }

    /**
     * Buat info log.
     *
     * @param string $message
     */
    public static function info($message)
    {
        static::write('info', $message);
    }

    /**
     * Buat debug log.
     *
     * @param string $message
     */
    public static function debug($message)
    {
        static::write('debug', $message);
    }

    /**
     * Bersihkan folder log.
     */
    public static function clear()
    {
        $files = glob(STORAGE_PATH.'logs'.DS.'*');
        foreach ($files as $file) {
            if (Str::endsWith($file, 'index.html')
            || Str::endsWith($file, '.gitkeep')) {
                continue;
            }

            @unlink($file);
        }
    }

    /**
     * Tulis log.
     *
     * @param string $level
     * @param string $message
     */
    protected static function write($level, $message)
    {
        if (is_array($message)) {
            $message = serialize($message);
        }

        $text = '['.date('Y-m-d H:i:s').'] - ['.$level.'] --> '.$message.PHP_EOL;
        static::save($text);
    }

    /**
     * Simpan log ke fili.
     *
     * @param string $text
     *
     * @return bool
     */
    protected static function save($text)
    {
        $path = STORAGE_PATH.'logs'.DS;
        $path .= 'log_'.date('Y-m-d').'.log';

        $written = file_put_contents($path, $text, LOCK_EX | (is_file($path) ? FILE_APPEND : 0));

        return (false !== $written) ? true : false;
    }
}
