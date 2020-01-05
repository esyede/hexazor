<?php

namespace System\Libraries\Session\Drivers;

defined('DS') or exit('No direct script access allowed.');

class FileDriver extends Driver implements Sweeper
{
    private $path;

    /**
     * Buat driver session file baru.
     *
     * @param  string $path
     * @return void
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

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
        if (file_exists($path = $this->path.$id)) {
            return unserialize(file_get_contents($path));
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
        file_put_contents($this->path.$session['id'], serialize($session), LOCK_EX);
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
        if (file_exists($this->path.$id)) {
            @unlink($this->path.$id);
        }
    }

    /**
     * Hapus seluruh session yang kadaluwarsa dari internal storage.
     *
     * @param  int  $expiration
     * @return void
     */
    public function sweep($expiration)
    {
        $files = glob($this->path.'*');

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if (filetype($file) === 'file' && filemtime($file) < $expiration) {
                @unlink($file);
            }
        }
    }
}
