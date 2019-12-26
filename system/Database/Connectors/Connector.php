<?php

namespace System\Database\Connectors;

defined('DS') or exit('No direct script access allowed.');

use PDO;

abstract class Connector
{
    protected $options = [
        PDO::ATTR_CASE              => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false,
    ];

    /**
     * Bangun koneksi ke database via PDO.
     *
     * @param array $config
     *
     * @return \PDO
     */
    abstract public function connect($config);

    /**
     * Override opsi koneksi default.
     *
     * @param array $config
     *
     * @return array
     */
    protected function options($config)
    {
        $options = (isset($config['options'])) ? $config['options'] : [];

        return $options + $this->options;
    }
}
