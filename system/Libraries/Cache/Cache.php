<?php

namespace System\Libraries\Cache;

defined('DS') or exit('No direct script access allowed.');

use System\Facades\Crypt;
use System\Facades\Storage;

class Cache
{
    protected $path;

    /**
     * Buat instance library cache.
     */
    public function __construct()
    {
        $this->path = storage_path('system/cache/');
        $this->ensureDirectoryExists($this->path);
    }

    /**
     * Ambil item dari cache.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $item = $this->retrieve($key);

        return is_null($item) ? value($default) : $item;
    }

    /**
     * Cek apakah item ada di cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return !is_null($this->get($key, null));
    }

    /**
     * Ambil sebuah item dari cache.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function retrieve($key)
    {
        $filepath = $this->path.$this->encode($key).'.cache';

        if (!Storage::isFile($filepath)) {
            return;
        }

        $cache = Storage::get($filepath);
        $cache = Crypt::decrypt($cache);

        if (time() >= substr($cache, 0, 10)) {
            return $this->forget($key);
        }

        return unserialize(substr($cache, 10));
    }

    /**
     * Simpan sebuah item ke cache.
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $seconds
     *
     * @return bool
     */
    public function put($key, $value, $seconds = null)
    {
        $filepath = $this->path.$this->encode($key).'.cache';
        $seconds = blank($seconds) ? 157680000 : $seconds;

        $value = $this->expiration($seconds).serialize($value);
        $value = Crypt::encrypt($value);

        return Storage::put($filepath, $value, true);
    }

    /**
     * Ambil item dari cache, atau simpan defalt value ke cache
     * dan kembalikan default valuenya.
     *
     * @param string $key
     * @param mixed  $default
     * @param int    $seconds
     *
     * @return mixed
     */
    public function remember($key, $default, $seconds = null)
    {
        $item = $this->get($key, null);

        if (!is_null($item)) {
            return $item;
        }

        $default = value($default);
        $this->put($key, $default, $seconds);

        return $default;
    }

    /**
     * Simpan item ke cache secara permanen / selamanya.
     * Selamanya disini maksudnya 5 tahun.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function forever($key, $value)
    {
        return $this->put($key, $value, 157680000);
    }

    /**
     * Ambil item dari cache, atau simpan defalt value ke cache selamanya
     * dan kembalikan default valuenya.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function sear($key, $default)
    {
        $item = $this->get($key, null);

        if (!is_null($item)) {
            return $item;
        }

        $default = value($default);
        $this->forever($key, $default);

        return $default;
    }

    /**
     * Hapus sebuah item cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function forget($key)
    {
        $filepath = $this->path.$this->encode($key).'.cache';

        if (is_file($filepath)) {
            return Storage::delete($filepath);
        }

        return true;
    }

    /**
     * Ambil lokasi direktori penyimpanan cache (absolut).
     *
     * @return string
     */
    public function path()
    {
        return $this->path;
    }

    protected function expiration($seconds)
    {
        return time() + $seconds;
    }

    /**
     * Encode nama file cache.
     *
     * @param string $string
     *
     * @return string
     */
    protected function encode($string)
    {
        return md5($string);
    }

    /**
     * Pastikan direktori penyimpanan ada,
     * Jika belum ada, buat!
     *
     * @param string $directory
     *
     * @return bool
     */
    protected function ensureDirectoryExists($directory)
    {
        if (!Storage::isDirectory($directory)) {
            return Storage::makeDirectory($directory, 0777, true, true);
        }

        return true;
    }
}
