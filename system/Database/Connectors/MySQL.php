<?php

namespace System\Database\Connectors;

defined('DS') or exit('No direct script access allowed.');

use PDO;

class MySQL extends Connector
{
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
        $dsn = "mysql:host={$host};dbname={$database}";

        if (isset($config['port'])) {
            $dsn .= ";port={$config['port']}";
        }

        if (isset($config['unix_socket'])) {
            $dsn .= ";unix_socket={$config['unix_socket']}";
        }

        $connection = new PDO($dsn, $username, $password, $this->options($config));

        if (isset($config['charset'])) {
            $connection->prepare("SET NAMES '{$config['charset']}'")->execute();
        }

        return $connection;
    }
}
