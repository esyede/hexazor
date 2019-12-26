<?php

namespace System\Loader;

defined('DS') or exit('No direct script access allowed.');

use InvalidArgumentException;

class ClassLoader
{
    private $prefixLengthsPsr4 = [];

    private $prefixDirsPsr4 = [];

    private $fallbackDirsPsr4 = [];

    private $prefixesPsr0 = [];

    private $fallbackDirsPsr0 = [];

    private $useIncludePath = false;

    private $classMap = [];

    private $classMapAuthoritative = false;

    private $missingClasses = [];

    private $apcuPrefix;

    /**
     * Ambil prefix - prefix kelas (PSR-0).
     *
     * @return array
     */
    public function getPrefixes()
    {
        if (!empty($this->prefixesPsr0)) {
            return call_user_func_array('array_merge', $this->prefixesPsr0);
        }

        return [];
    }

    /**
     * Ambil prefix - prefix kelas (PSR-4).
     *
     * @return array
     */
    public function getPrefixesPsr4()
    {
        return $this->prefixDirsPsr4;
    }

    /**
     * Ambil direktori default (fallback) (PSR-0).
     *
     * @return string
     */
    public function getFallbackDirs()
    {
        return $this->fallbackDirsPsr0;
    }

    /**
     * Ambil direktori default (fallback) (PSR-4).
     *
     * @return string
     */
    public function getFallbackDirsPsr4()
    {
        return $this->fallbackDirsPsr4;
    }

    /**
     * Ambil mapping kelas.
     *
     * @return array
     */
    public function getClassMap()
    {
        return $this->classMap;
    }

    /**
     * Tambah mapping kelas.
     *
     * @param array $classMap
     */
    public function addClassMap(array $classMap)
    {
        if ($this->classMap) {
            $this->classMap = array_merge($this->classMap, $classMap);
        } else {
            $this->classMap = $classMap;
        }
    }

    /**
     * Daftarkan direktori sesuai prefixnya (PSR-0).
     *
     * @param string $prefix
     * @param string $paths
     * @param bool   $prepend
     */
    public function add($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirsPsr0 = array_merge(
                    (array) $paths,
                    $this->fallbackDirsPsr0
                );
            } else {
                $this->fallbackDirsPsr0 = array_merge(
                    $this->fallbackDirsPsr0,
                    (array) $paths
                );
            }

            return;
        }

        $first = $prefix[0];

        if (!isset($this->prefixesPsr0[$first][$prefix])) {
            $this->prefixesPsr0[$first][$prefix] = (array) $paths;

            return;
        }

        if ($prepend) {
            $this->prefixesPsr0[$first][$prefix] = array_merge(
                (array) $paths,
                $this->prefixesPsr0[$first][$prefix]
            );
        } else {
            $this->prefixesPsr0[$first][$prefix] = array_merge(
                $this->prefixesPsr0[$first][$prefix],
                (array) $paths
            );
        }
    }

    /**
     * Daftarkan direktori sesuai prefixnya (PSR-4).
     *
     * @param string $prefix
     * @param string $paths
     * @param bool   $prepend
     */
    public function addPsr4($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirsPsr4 = array_merge(
                    (array) $paths,
                    $this->fallbackDirsPsr4
                );
            } else {
                $this->fallbackDirsPsr4 = array_merge(
                    $this->fallbackDirsPsr4,
                    (array) $paths
                );
            }
        } elseif (!isset($this->prefixDirsPsr4[$prefix])) {
            $length = strlen($prefix);

            if ('\\' !== $prefix[$length - 1]) {
                $message = 'A non-empty PSR-4 prefix must end with a namespace separator.';

                throw new InvalidArgumentException($message);
            }

            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $this->prefixDirsPsr4[$prefix] = (array) $paths;
        } elseif ($prepend) {
            $this->prefixDirsPsr4[$prefix] = array_merge(
                (array) $paths,
                $this->prefixDirsPsr4[$prefix]
            );
        } else {
            $this->prefixDirsPsr4[$prefix] = array_merge(
                $this->prefixDirsPsr4[$prefix],
                (array) $paths
            );
        }
    }

    /**
     * Set (atau replace) direktori untuk namespace yang diberikan (PSR-0).
     *
     * @param string $prefix
     * @param string $paths
     */
    public function set($prefix, $paths)
    {
        if (!$prefix) {
            $this->fallbackDirsPsr0 = (array) $paths;
        } else {
            $this->prefixesPsr0[$prefix[0]][$prefix] = (array) $paths;
        }
    }

    /**
     * Set (atau replace) direktori untuk namespace yang diberikan (PSR-4).
     *
     * @param string $prefix
     * @param string $paths
     */
    public function setPsr4($prefix, $paths)
    {
        if (!$prefix) {
            $this->fallbackDirsPsr4 = (array) $paths;
        } else {
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                $message = 'A non-empty PSR-4 prefix must end with a namespace separator.';

                throw new InvalidArgumentException($message);
            }

            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $this->prefixDirsPsr4[$prefix] = (array) $paths;
        }
    }

    /**
     * Aktifkan pencarian path include untuk file kelas.
     *
     * @param bool $useIncludePath
     */
    public function setUseIncludePath($useIncludePath)
    {
        $this->useIncludePath = $useIncludePath;
    }

    /**
     * Presiksa apakah autoloader menggunakan include path untuk checking kelas.
     *
     * @return bool
     */
    public function getUseIncludePath()
    {
        return $this->useIncludePath;
    }

    /**
     * Mematikan pencarian direktori prefix dan fallback direktori
     * untuk kelas yang belum terdaftar dalam classmap.
     *
     * @param mixed $classMapAuthoritative
     *
     * @return bool
     */
    public function setClassMapAuthoritative($classMapAuthoritative)
    {
        $this->classMapAuthoritative = $classMapAuthoritative;
    }

    /**
     * Haruskah pencarian kelas digagalkan jika tidak ditemukan di classmap saat ini?
     *
     * @return bool
     */
    public function isClassMapAuthoritative()
    {
        return $this->classMapAuthoritative;
    }

    /**
     * Prefix APCu untuk meng-cache kelas yang
     * bisa / tidak bisa ditemukan (jika ekstensi APCu diaktifkan).
     *
     * @param mixed $apcuPrefix
     *
     * @return string|null
     */
    public function setApcuPrefix($apcuPrefix)
    {
        $this->apcuPrefix = function_exists('apcu_fetch')
            && filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN) ? $apcuPrefix : null;
    }

    /**
     * Prefix APCu yang sedang digunakan, atau NULL jika caching APCu tidak diaktifkan.
     *
     * @return string|null
     */
    public function getApcuPrefix()
    {
        return $this->apcuPrefix;
    }

    /**
     * Daftarkan method loadClass sebagai autoloader.
     *
     * @param bool $prepend
     */
    public function register($prepend = false)
    {
        spl_autoload_register([$this, 'loadClass'], true, $prepend);
    }

    /**
     *  Keluarkan method loadClass dari autoloader (unregister).
     */
    public function unregister()
    {
        spl_autoload_unregister([$this, 'loadClass']);
    }

    /**
     * Autoload kelas atau interface.
     *
     * @param string $class
     *
     * @return bool|null
     */
    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            _class_loader_include_file($file);

            return true;
        }
    }

    /**
     * Temukan path kelas untuk di-autoload.
     *
     * @param string $class
     *
     * @return string
     */
    public function findFile($class)
    {
        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }

        if ($this->classMapAuthoritative
        || isset($this->missingClasses[$class])) {
            return false;
        }

        if (null !== $this->apcuPrefix) {
            $file = apcu_fetch($this->apcuPrefix.$class, $hit);
            if ($hit) {
                return $file;
            }
        }

        $file = $this->findFileWithExtension($class, '.php');
        if (false === $file && defined('HHVM_VERSION')) {
            $file = $this->findFileWithExtension($class, '.hh');
        }

        if (null !== $this->apcuPrefix) {
            apcu_add($this->apcuPrefix.$class, $file);
        }

        if (false === $file) {
            $this->missingClasses[$class] = true;
        }

        return $file;
    }

    /**
     * Cari kelas berdasarkan ekstensinya.
     *
     * @param string $class
     * @param string $ext
     *
     * @return string|null
     */
    private function findFileWithExtension($class, $ext)
    {
        $logicalPathPsr4 = strtr($class, '\\', DS).$ext;
        $first = $class[0];

        if (isset($this->prefixLengthsPsr4[$first])) {
            $subPath = $class;

            while (false !== $lastPos = strrpos($subPath, '\\')) {
                $subPath = substr($subPath, 0, $lastPos);
                $search = $subPath.'\\';

                if (isset($this->prefixDirsPsr4[$search])) {
                    $pathEnd = DS.substr($logicalPathPsr4, $lastPos + 1);

                    foreach ($this->prefixDirsPsr4[$search] as $dir) {
                        if (file_exists($file = $dir.$pathEnd)) {
                            return $file;
                        }
                    }
                }
            }
        }

        foreach ($this->fallbackDirsPsr4 as $dir) {
            if (file_exists($file = $dir.DS.$logicalPathPsr4)) {
                return $file;
            }
        }

        if (false !== $pos = strrpos($class, '\\')) {
            $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1)
               .strtr(substr($logicalPathPsr4, $pos + 1), '_', DS);
        } else {
            $logicalPathPsr0 = strtr($class, '_', DS).$ext;
        }

        if (isset($this->prefixesPsr0[$first])) {
            foreach ($this->prefixesPsr0[$first] as $prefix => $dirs) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($dirs as $dir) {
                        if (file_exists($file = $dir.DS.$logicalPathPsr0)) {
                            return $file;
                        }
                    }
                }
            }
        }

        foreach ($this->fallbackDirsPsr0 as $dir) {
            if (file_exists($file = $dir.DS.$logicalPathPsr0)) {
                return $file;
            }
        }

        if ($this->useIncludePath && $file = stream_resolve_include_path($logicalPathPsr0)) {
            return $file;
        }

        return false;
    }
}

/**
 * Fungsi untuk include file. Sengaja di-sendirikan agar
 * kelas yang di-include tidak punya akses ke $this/self::.
 *
 * @param string $file
 */
function _class_loader_include_file($file)
{
    include $file;
}
