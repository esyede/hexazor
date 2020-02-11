<?php

namespace System\Database\Connectors;

defined('DS') or exit('No direct script access allowed.');

use PDO;

class Postgres extends Connector
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
        $host_dsn = isset($host) ? 'host='.$host.';' : '';
        $dsn = 'pgsql:'.$host_dsn.'dbname='.$database;

        if (isset($config['port'])) {
            $dsn .= ';port='.$config['port'];
        }

        $connection = new PDO($dsn, $username, $password, $this->options($config));

        if (isset($config['charset'])) {
            $connection->prepare("SET NAMES '".$config['charset']."'")->execute();
        }

        if (isset($config['schema'])) {
            $connection->prepare('SET search_path TO '.$config['schema'])->execute();
        }

        return $connection;
    }
}
