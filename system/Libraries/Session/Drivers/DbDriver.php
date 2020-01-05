<?php

namespace System\Libraries\Session\Drivers;

defined('DS') or exit('No direct script access allowed.');

use System\Core\Config;
use System\Database\Connection;

class DbDriver extends Driver implements Sweeper
{
    protected $connection;

    /**
     * Buat driver session database baru.
     *
     * @param  \System\Database\Connection $connection
     * @return void
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
        $session = $this->table()->find($id);

        if (!is_null($session)) {
            return [
                'id' => $session->id,
                'last_activity' => $session->last_activity,
                'data' => unserialize($session->data),
            ];
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
        if ($exists) {
            $this->table()->where('id', '=', $session['id'])->update([
                'last_activity' => $session['last_activity'],
                'data' => serialize($session['data']),
            ]);
        } else {
            $this->table()->insert([
                'id' => $session['id'],
                'last_activity' => $session['last_activity'],
                'data' => serialize($session['data']),
            ]);
        }
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
        $this->table()->delete($id);
    }

    /**
     * Hapus seluruh session yang kadaluwarsa dari internal storage.
     *
     * @param  int  $expiration
     * @return void
     */
    public function sweep($expiration)
    {
        $this->table()->where('last_activity', '<', $expiration)->delete();
    }

    /**
     * Ambil nama tabel session.
     *
     * @return \System\Database\Query
     */
    private function table()
    {
        return $this->connection->table(Config::get('session.table'));
    }
}
