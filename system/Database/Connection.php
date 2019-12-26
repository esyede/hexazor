<?php

namespace System\Database;

defined('DS') or exit('No direct script access allowed.');

use Exception;
use PDO;
use System\Core\Config;

class Connection
{
    public $pdo;

    public $config;

    protected $grammar;

    public static $queries = [];

    /**
     * Buat instance koneksi database baru.
     *
     * @param \PDO  $pdo
     * @param array $config
     */
    public function __construct(PDO $pdo, $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    /**
     * Set tabel untuk dioperasikan.
     *
     * @param string $table
     *
     * @return \System\Database\Query
     */
    public function table($table)
    {
        return new Query($this, $this->grammar(), $table);
    }

    /**
     * Buat query grammar baru untuk koneksi ini.
     *
     * @return \System\Database\Query\Grammars\Grammar
     */
    protected function grammar()
    {
        if (isset($this->grammar)) {
            return $this->grammar;
        }

        if (isset(Database::$registrar[$this->driver()])) {
            return $this->grammar = Database::$registrar[$this->driver()]['query']();
        }

        switch ($this->driver()) {
            case 'mysql':  return $this->grammar = new Query\Grammars\MySQL($this);
            case 'sqlite': return $this->grammar = new Query\Grammars\SQLite($this);
            case 'sqlsrv': return $this->grammar = new Query\Grammars\SQLServer($this);
            case 'pgsql':  return $this->grammar = new Query\Grammars\Postgres($this);
            default:       return $this->grammar = new Query\Grammars\Grammar($this);
        }
    }

    /**
     * Eksekusi callback yang dibungkus didalam transaksi database.
     *
     * @param callable $callback
     *
     * @return bool
     */
    public function transaction(callable $callback)
    {
        $this->pdo->beginTransaction();

        try {
            call_user_func($callback);
        } catch (Exception $e) {
            $this->pdo->rollBack();

            throw new Exception($e->getMessage());
        }

        return $this->pdo->commit();
    }

    /**
     * Jalankan query SQL dan return satu hasil kolom.
     *
     * @param string $sql
     * @param array  $bindings
     *
     * @return mixed
     */
    public function only($sql, $bindings = [])
    {
        $results = (array) $this->first($sql, $bindings);

        return reset($results);
    }

    /**
     * Jalankan query SQL dan return hasil pertama.
     *
     * @param string $sql
     * @param array  $bindings
     *
     * @return object
     */
    public function first($sql, $bindings = [])
    {
        if (count($results = $this->query($sql, $bindings)) > 0) {
            return $results[0];
        }
    }

    /**
     * Jalankan query SQL dan return array berisi objek-objek stdClass.
     *
     * @param string $sql
     * @param array  $bindings
     *
     * @return array
     */
    public function query($sql, $bindings = [])
    {
        $sql = trim($sql);
        list($statement, $result) = $this->execute($sql, $bindings);

        if (0 === stripos($sql, 'select') || 0 === stripos($sql, 'show')) {
            return $this->fetch($statement, Config::get('database.fetch'));
        } elseif (0 === stripos($sql, 'update') || 0 === stripos($sql, 'delete')) {
            return $statement->rowCount();
        } elseif (0 === stripos($sql, 'insert') && false !== stripos($sql, 'returning')) {
            return $this->fetch($statement, Config::get('database.fetch'));
        }

        return $result;
    }

    /**
     * Jalankan query SQL.
     *
     * @param string $sql
     * @param array  $bindings
     *
     * @return array
     */
    protected function execute($sql, $bindings = [])
    {
        $bindings = (array) $bindings;
        $bindings = array_filter($bindings, function ($binding) {
            return !($binding instanceof Expression);
        });

        $bindings = array_values($bindings);
        $bindingsCount = count($bindings);
        $sql = $this->grammar()->shortcut($sql, $bindings);
        $datetime = $this->grammar()->datetime;

        for ($i = 0; $i < $bindingsCount; ++$i) {
            if ($bindings[$i] instanceof \DateTime) {
                $bindings[$i] = $bindings[$i]->format($datetime);
            }
        }

        try {
            $statement = $this->pdo->prepare($sql);
            $start = microtime(true);
            $result = $statement->execute($bindings);
        } catch (Exception $exception) {
            throw new DBException($sql, $bindings, $exception);
        }

        if (Config::get('database.profile')) {
            $this->log($sql, $bindings, $start);
        }

        return [$statement, $result];
    }

    /**
     * Ambil semua baris untuk statement yang diberikan.
     *
     * @param \PDOStatement $statement
     * @param int           $style
     *
     * @return array
     */
    protected function fetch($statement, $style)
    {
        if (PDO::FETCH_CLASS === $style) {
            return $statement->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        }

        return $statement->fetchAll($style);
    }

    /**
     * Catat query dan invoke event untuk menangani logging query database.
     *
     * @param string $sql
     * @param array  $bindings
     * @param int    $start
     */
    protected function log($sql, $bindings, $start)
    {
        $time = number_format((microtime(true) - $start) * 1000, 2);
        static::$queries[] = compact('sql', 'bindings', 'time');
    }

    /**
     * Ambil nama driver koneksi saat ini.
     *
     * @return string
     */
    public function driver()
    {
        return $this->config['driver'];
    }

    /**
     * Magic method untuk secara dinamis memulai query pada tabel database.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return \System\Database\Query
     */
    public function __call($method, $parameters)
    {
        return $this->table($method);
    }
}
