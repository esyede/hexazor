<?php

namespace System\Libraries\Session\Drivers;

defined('DS') or exit('No direct script access allowed.');

class MemoryDriver extends Driver
{
    public $session;

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
        return $this->session;
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
        // ...
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
        // ...
    }
}
