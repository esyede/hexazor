<?php

namespace System\Database\Connectors;

defined('DS') or exit('No direct script access allowed.');

use PDO;
use RuntimeException;
use System\Libraries\Storage\Storage;

class SQLite extends Connector
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
        $options = $this->options($config);

        if (':memory:' === $config['database']) {
            return new PDO('sqlite::memory:', null, null, $options);
        }

        $dbname = $config['database'];
        $path = database_path('sqlite/');
        $storage = new Storage();

        if (!$storage->isDirectory($path)) {
            if (!$storage->makeDirectory($path, 0777, true)) {
                throw new RuntimeException(
                    'Unable to create sqlite storage folder: storage'.DS.'sqlite'.DS
                );
            }
        }

        unset($storage);

        $path .= $dbname.'.sqlite';

        return new PDO('sqlite:'.$path, null, null, $options);
    }
}
