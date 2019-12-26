<?php

namespace System\Database;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use Exception;
use System\Core\Config;

class Database
{
    public static $connections = [];

    public static $registrar = [];

    private static $defaultQueryGrammar = '\System\Database\Query\Grammars\Grammar';

    /**
     * Ambil koneksi database (dari app/Config/Database.php).
     *
     * @param string $connection
     *
     * @return \System\Database\Connection
     */
    public static function connection($connection = null)
    {
        if (is_null($connection)) {
            $connection = Config::get('database.default');
        }

        if (!isset(static::$connections[$connection])) {
            $config = Config::get("database.connections.{$connection}");

            if (is_null($config)) {
                throw new Exception("Database connection is not defined for [$connection].");
            }

            static::$connections[$connection] = new Connection(static::connect($config), $config);
        }

        return static::$connections[$connection];
    }

    /**
     * Konek ke database via PDO menggunakan konfigurasi yang diberikan.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected static function connect(array $config)
    {
        return static::connector($config['driver'])->connect($config);
    }

    /**
     * Buat instance database connector baru.
     *
     * @param string $driver
     *
     * @return \System\Database\Connectors\Connector
     */
    protected static function connector($driver)
    {
        if (isset(static::$registrar[$driver])) {
            $resolver = static::$registrar[$driver]['connector'];

            return $resolver();
        }

        switch ($driver) {
            case 'sqlite': return new Connectors\SQLite();
            case 'mysql':  return new Connectors\MySQL();
            case 'pgsql':  return new Connectors\Postgres();
            case 'sqlsrv': return new Connectors\SQLServer();
            default:       throw new Exception("Database driver [$driver] is not supported.");
        }
    }

    /**
     * Mulai magic query ke database.
     *
     * @param string $table
     * @param string $connection
     *
     * @return \System\Database\Query
     */
    public static function table($table, $connection = null)
    {
        return static::connection($connection)->table($table);
    }

    /**
     * Buat instance database expression baru
     * Database expresion ini digunakan untuk meng-inject sql mentah ke magic query.
     *
     * @param string $value
     *
     * @return \System\Database\Expression
     */
    public static function raw($value)
    {
        return new Expression($value);
    }

    /**
     * Escape (quote) string untuk mencegah sql injection.
     *
     * @param string $value
     *
     * @return string
     */
    public static function escape($value)
    {
        return static::connection()->pdo->quote($value);
    }

    /**
     * Ambil profiling data untuk semua query.
     *
     * @return array
     */
    public static function profile()
    {
        return Connection::$queries;
    }

    /**
     * Ambil query yang terakhir dijalankan.
     * Jika belum ada query yang dijalankan, dia akan mereturn FALSE.
     *
     * @return string
     */
    public static function lastQuery()
    {
        return end(Connection::$queries);
    }

    /**
     * Daftarkan database connector dan grammar baru.
     *
     * @param string   $name
     * @param \Closure $connector
     * @param callable $query
     * @param \Closure $schema
     */
    public static function extend($name, Closure $connector, $query = null, $schema = null)
    {
        if (is_null($query)) {
            $query = static::$defaultQueryGrammar;
        }

        static::$registrar[$name] = compact('connector', 'query', 'schema');
    }

    /**
     * Magic method untuk memanggil method pada koneksi database default.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return call_user_func_array([static::connection(), $method], $parameters);
    }
}
