<?php

namespace System\Loader;

defined('DS') or exit('No direct script access allowed.');

class Autoloader
{
    private static $loader;

    /**
     * Load kelas ClassLoader.
     *
     * @param string $class
     *
     * @return bool
     */
    public static function loadClassLoader($class)
    {
        if (__NAMESPACE__.'\\ClassLoader' === $class) {
            require __DIR__.DS.'ClassLoader.php';
        }
    }

    /**
     * Daftarkan objek ClassLoader.
     *
     * @return object ClassLoader object
     */
    public static function register()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        if (!class_exists(__NAMESPACE__.'\\ClassLoader')) {
            require __DIR__.DS.'ClassLoader.php';
        }

        spl_autoload_register('self::loadClassLoader', true, true);

        $loader = new ClassLoader();
        self::$loader = $loader;

        spl_autoload_unregister('self::loadClassLoader');

        $useStaticInitiator = PHP_VERSION_ID >= 50600
            && !defined('HHVM_VERSION')
            && (!function_exists('zend_loader_file_encoded')
            || !zend_loader_file_encoded());

        if ($useStaticInitiator) {
            if (!class_exists('\System\Loader\StaticInitiator')) {
                require_once __DIR__.'/StaticInitiator.php';
            }

            call_user_func(\System\Loader\StaticInitiator::init($loader));
        } else {
            $mappings = [
                'System\\' => [ROOT_PATH.'system'],
                'App\\' => [ROOT_PATH.'app'],
            ];

            foreach ($mappings as $namespace => $path) {
                $loader->setPsr4($namespace, $path);
            }
        }

        $loader->register(true);

        return $loader;
    }

    /**
     * Ambil instance kelas ClassLoader.
     *
     * @return \System\Loader\ClassLoader
     */
    public static function getLoader()
    {
        return static::$loader;
    }
}
