<?php

namespace System\Libraries\Session\Drivers;

defined('DS') or exit('No direct script access allowed.');

use System\Core\Config;
use System\Support\Str;

abstract class Driver
{
    /**
     * Muat sebuah session dari internal storage berdasarkan ID.
     * Jika tidak ketemu, returnnya NULL.
     *
     * @param  string $id
     *
     * @return array
     */
    abstract public function load($id);

    /**
     * Simpan item session ke internal storage.
     *
     * @param  array $session
     * @param  array $config
     * @param  bool  $exists
     *
     * @return void
     */
    abstract public function save($session, $config, $exists);

    /**
     * Hapus sebuah session dari internal storage berdasarkan ID.
     *
     * @param  string $id
     *
     * @return void
     */
    abstract public function delete($id);

    /**
     * Buat array session baru dengan unique ID.
     *
     * @return array
     */
    public function fresh()
    {
        return [
            'id' => $this->id(),
            'data' => [
                ':new:' => [],
                ':old:' => [],
            ]
        ];
    }

    /**
     * Buat session ID baru yang unique.
     *
     * @return string
     */
    public function id()
    {
        $session = [];

        if ($this instanceof CookieDriver) {
            return Str::random(40);
        }

        do {
            $session = $this->load($id = Str::random(40));
        } while (!is_null($session));

        return $id;
    }
}
