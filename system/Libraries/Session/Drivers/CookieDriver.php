<?php

namespace System\Libraries\Session\Drivers;

defined('DS') or exit('No direct script access allowed.');

use System\Facades\Cookie;
use System\Facades\Crypt;

class CookieDriver extends Driver
{
    /**
     * Muat sebuah session dari internal storage berdasarkan ID.
     * Jika tidak ketemu, returnnya NULL.
     *
     * @param  string $id
     *
     * @return array
     */
    public function load($id)
    {
        if (Cookie::has('session_payload')) {
            return unserialize(Crypt::decrypt(Cookie::get('session_payload')));
        }
    }

    /**
     * Simpan item session ke internal storage.
     *
     * @param  array $session
     * @param  array $config
     * @param  bool  $exists
     *
     * @return void
     */
    public function save($session, $config, $exists)
    {
        extract($config, EXTR_SKIP);
        $payload = Crypt::encrypt(serialize($session));
        Cookie::put('session_payload', $payload, $lifetime, $path, $domain);
    }

    /**
     * Hapus sebuah session dari internal storage berdasarkan ID.
     *
     * @param  string $id
     *
     * @return void
     */
    public function delete($id)
    {
        Cookie::forget('session_payload');
    }
}
