<?php

namespace System\Database\Connectors;

defined('DS') or exit('No direct script access allowed.');

use PDO;

class SQLServer extends Connector
{
    protected $options = [
        PDO::ATTR_CASE              => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * Bangun koneksi ke database via PDO.
     *
     * @param array $config
     *
     * @return \PDO
     */
    public function connect($config)
    {
        extract($config);
        $port = isset($port) ? ','.$port : '';

        if (in_array('dblib', PDO::getAvailableDrivers())) {
            $dsn = 'dblib:host='.$host.$port.';dbname='.$database;
        } else {
            $dsn = 'sqlsrv:Server='.$host.$port.';Database='.$database;
        }

        return new PDO($dsn, $username, $password, $this->options($config));
    }
}
