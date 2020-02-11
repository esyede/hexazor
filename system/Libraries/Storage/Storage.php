<?php

namespace System\Libraries\Storage;

defined('DS') or exit('No direct script access allowed.');

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Storage
{
    /**
     * Cek apakah file ada atau tidak.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists($path)
    {
        return file_exists($path);
    }

    /**
     * Ambil konten file.
     *
     * @param string $path
     *
     * @return string
     */
    public function get($path)
    {
        if ($this->isFile($path)) {
            return file_get_contents($path);
        }

        throw new Exception("File does not exist at path {$path}");
    }

    /**
     * Ambil return value dari file yang di-require.
     *
     * @param string $path
     *
     * @return mixed
     */
    public function getRequire($path)
    {
        if ($this->isFile($path)) {
            return require $path;
        }

        throw new Exception("File does not exist at path {$path}");
    }

    /**
     * Require once.
     *
     * @param string $file
     *
     * @return mixed
     */
    public function requireOnce($file)
    {
        require_once $file;
    }

    /**
     * Tulis konten ke file.
     *
     * @param string $path
     * @param string $contents
     * @param bool   $lock
     *
     * @return int
     */
    public function put($path, $contents, $lock = false)
    {
        return false !== file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Tambahkan konten ke awal file.
     *
     * @param string $path
     * @param string $data
     *
     * @return int
     */
    public function prepend($path, $data)
    {
        if ($this->exists($path)) {
            return $this->put($path, $data.$this->get($path));
        }

        return $this->put($path, $data);
    }

    /**
     * Tambahkan konten ke akhir file.
     *
     * @param string $path
     * @param string $data
     *
     * @return int
     */
    public function append($path, $data)
    {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    /**
     * Hapus file berdasarkan path - path yang diberikan.
     *
     * @param string|array $paths
     *
     * @return bool
     */
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();
        $success = true;

        foreach ($paths as $path) {
            if (!@unlink($path)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Pindahkan file.
     *
     * @param string $path
     * @param string $target
     *
     * @return bool
     */
    public function move($path, $target)
    {
        return rename($path, $target);
    }

    /**
     * Salin file.
     *
     * @param string $path
     * @param string $target
     *
     * @return bool
     */
    public function copy($path, $target)
    {
        return copy($path, $target);
    }

    /**
     * Ambil hanya nama file pada path yang diberikan.
     *
     * @param string $path
     *
     * @return string
     */
    public function name($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Ambil hanya ekstensi file pada path yang diberikan.
     *
     * @param string $path
     *
     * @return string
     */
    public function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Ambil hanya tipe file pada path yang diberikan.
     *
     * @param string $path
     *
     * @return string
     */
    public function type($path)
    {
        return filetype($path);
    }

    /**
     * Ambil hanya ukuran file pada path yang diberikan.
     *
     * @param string $path
     *
     * @return int
     */
    public function size($path)
    {
        return filesize($path);
    }

    /**
     * Ambil waktu terakhir file dimodifikasi.
     *
     * @param string $path
     *
     * @return int
     */
    public function lastModified($path)
    {
        return filemtime($path);
    }

    /**
     * Cek apakah path yang diberikan merupakan sebuah direktori atau bukan.
     *
     * @param string $directory
     *
     * @return bool
     */
    public function isDirectory($directory)
    {
        return is_dir($directory);
    }

    /**
     * Cek apakah path yang diberikan bisa ditulisi atau tidak.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isWritable($path)
    {
        return is_writable($path);
    }

    /**
     * Cek apakah path yang diberikan merupakan sebuah file atau bukan.
     *
     * @param string $file
     *
     * @return bool
     */
    public function isFile($file)
    {
        return is_file($file);
    }

    /**
     * Lakukan pencarian direktori menggunakan glob.
     *
     * @param string $pattern
     * @param int    $flags
     *
     * @return array
     */
    public function glob($pattern, $flags = 0)
    {
        return glob($pattern, $flags);
    }

    /**
     * Ambil seluruh path file pada suatu direktori.
     *
     * @param string $directory
     *
     * @return array
     */
    public function files($directory)
    {
        $directory = str_replace(['/', '\\'], [DS, DS], $directory);
        $glob = glob($directory.DS.'*');

        if (false === $glob) {
            return [];
        }

        return array_filter($glob, function ($file) {
            return 'file' === filetype($file);
        });
    }

    /**
     * Ambil seluruh path file pada suatu direktori (rekursif).
     *
     * @param string $directory
     *
     * @return array
     */
    public function allFiles($directory)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        $files = [];

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        return $files;
    }

    /**
     * Ambil seluruh path direktori pada suatu direktori.
     *
     * @param string $directory
     *
     * @return array
     */
    public function directories($directory)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        $directories = [];

        foreach ($iterator as $item) {
            if ($item->isFile() || '.' === $item || '..' === $item) {
                continue;
            }

            $directories[] = $item->getPathname();
        }

        return $directories;
    }

    /**
     * Buat sebuah direktori.
     *
     * @param string $path
     * @param int    $mode
     * @param bool   $recursive
     *
     * @return bool
     */
    public function makeDirectory($path, $mode = 0755, $recursive = false)
    {
        return false !== @mkdir($path, $mode, $recursive);
    }

    /**
     * Salin direktori.
     *
     * @param string $directory
     * @param string $destination
     * @param int    $options
     *
     * @return bool
     */
    public function copyDirectory($directory, $destination, $options = null)
    {
        if (!$this->isDirectory($directory)) {
            return false;
        }

        $options = $options ?: FilesystemIterator::SKIP_DOTS;

        if (!$this->isDirectory($destination)) {
            $this->makeDirectory($destination, 0777, true);
        }

        $items = new FilesystemIterator($directory, $options);

        foreach ($items as $item) {
            $target = $destination.DS.$item->getBasename();

            if ($item->isDir()) {
                $path = $item->getPathname();
                if (!$this->copyDirectory($path, $target, $options)) {
                    return false;
                }
            } else {
                if (!$this->copy($item->getPathname(), $target)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Hapus direktori secara rekursif
     * Direktori induknya bisa dipertahankan ataupun sealian dihapus.
     *
     * @param string $directory
     * @param bool   $preserve
     *
     * @return bool
     */
    public function deleteDirectory($directory, $preserve = false)
    {
        if (!$this->isDirectory($directory)) {
            return false;
        }

        $items = new FilesystemIterator($directory);

        foreach ($items as $item) {
            if ($item->isDir()) {
                $this->deleteDirectory($item->getPathname());
            } else {
                $this->delete($item->getPathname());
            }
        }

        if (!$preserve) {
            @rmdir($directory);
        }

        return true;
    }

    /**
     * Kosongkan direktori dari file maupun folder.
     *
     * @param string $directory
     *
     * @return bool
     */
    public function cleanDirectory($directory)
    {
        return $this->deleteDirectory($directory, true);
    }
}
