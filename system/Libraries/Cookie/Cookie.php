<?php

namespace System\Libraries\Cookie;

defined('DS') or exit('No direct script access allowed.');

use Exception;
use System\Core\Config;
use System\Facades\Request;

class Cookie
{
    protected $jar = [];

    /**
     * Cek apakah cookie ada atau tidak.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return !is_null($this->get($name, null));
    }

    /**
     * Ambil sebuah item cookie.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (isset($this->jar[$name])) {
            return $this->parse($this->jar[$name]['value']);
        }

        $value = Request::cookie($name, null);

        if (!is_null($value)) {
            return $this->parse($value);
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
    public function put($name, $value, $expiration = 0, $path = '/', $domain = null, $secure = false)
    {
        if (0 !== $expiration) {
            $expiration = time() + ($expiration * 60);
        }

        $value = $this->hash($value).'+'.$value;

        if ($secure && !Request::isSecure()) {
            throw new Exception('Attempting to set secure cookie over HTTP.');
        }

        $this->jar[$name] = compact('name', 'value', 'expiration', 'path', 'domain', 'secure');
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
    public function forever($name, $value, $path = '/', $domain = null, $secure = false)
    {
        return $this->put($name, $value, 2628000, $path, $domain, $secure);
    }

    /**
     * Lihat isi cookie jar.
     *
     * @return array
     */
    public function jar()
    {
        return $this->jar;
    }

    /**
     * Hapus sebuah item cookie.
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     */
    public function forget($name, $path = '/', $domain = null, $secure = false)
    {
        if (isset($this->jar[$name])) {
            return $this->put($name, null, -2000, $path, $domain, $secure);
        }
    }

    /**
     * Hash value cookie yang diberikan.
     *
     * @param string $value
     *
     * @return string
     */
    public function hash($value)
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
    protected function parse($value)
    {
        $segments = explode('+', $value);

        if (count($segments) < 2) {
            return;
        }

        $value = implode('+', array_slice($segments, 1));

        if ($segments[0] === $this->hash($value)) {
            return $value;
        }
    }
}
