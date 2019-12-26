<?php

namespace System\Libraries\Cookie;

defined('DS') or exit('No direct script access allowed.');

use Exception;
use System\Core\Config;
use System\Facades\Request;

class Cookie
{
    public static $jar = [];

    /**
     * Cek apakah cookie ada atau tidak.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function has($name)
    {
        return !is_null(static::get($name, null));
    }

    /**
     * Ambil sebuah item cookie.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        if (isset(static::$jar[$name])) {
            return static::parse(static::$jar[$name]['value']);
        }

        $value = Request::cookie($name, null);

        if (!is_null($value)) {
            return static::parse($value);
        }

        return value($default);
    }

    /**
     * Simpan sebuah item cookie.
     *
     * @param string $name
     * @param mixed  $value
     * @param int    $expiration
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     */
    public static function put($name, $value, $expiration = 0, $path = '/', $domain = null, $secure = false)
    {
        if (0 !== $expiration) {
            $expiration = time() + ($expiration * 60);
        }

        $value = static::hash($value).'+'.$value;

        if ($secure && !Request::isSecure()) {
            throw new Exception('Attempting to set secure cookie over HTTP.');
        }

        static::$jar[$name] = compact('name', 'value', 'expiration', 'path', 'domain', 'secure');
    }

    /**
     * Simpan item cookie secara permanen / selamanya.
     * Selamanya disini maksudnya 5 tahun.
     *
     * @param string $name
     * @param mixed  $value
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     */
    public static function forever($name, $value, $path = '/', $domain = null, $secure = false)
    {
        return static::put($name, $value, 157680000, $path, $domain, $secure);
    }

    /**
     * Hapus sebuah item cookie.
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     */
    public static function forget($name, $path = '/', $domain = null, $secure = false)
    {
        if (isset(static::$jar[$name])) {
            return static::put($name, null, -157680000, $path, $domain, $secure);
        }
    }

    /**
     * Hash value cookie yang diberikan.
     *
     * @param string $value
     *
     * @return string
     */
    public static function hash($value)
    {
        return hash_hmac('sha1', $value, Config::get('app.application_key'));
    }

    /**
     * Parse value cookie yang di-hash.
     *
     * @param string $value
     *
     * @return string
     */
    protected static function parse($value)
    {
        $segments = explode('+', $value);

        if (count($segments) < 2) {
            return;
        }

        $value = implode('+', array_slice($segments, 1));

        if ($segments[0] == static::hash($value)) {
            return $value;
        }
    }
}
