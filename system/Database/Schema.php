<?php

namespace System\Database;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use Exception;
use PDOException;
use System\Core\Config;
use System\Support\Magic;

class Schema
{
    /**
     * Mulai operasi schema pada tabel.
     *
     * @param string   $table
     * @param \Closure $callback
     */
    public static function table($table, Closure $callback)
    {
        call_user_func($callback, $table = new Schema\Table($table));

        return static::execute($table);
    }

    /**
     * Buat skema tabel baru.
     *
     * @param string   $table
     * @param \Closure $callback
     */
    public static function create($table, Closure $callback)
    {
        $tableString = $table;
        $table = new Schema\Table($table);
        $table->create();

        call_user_func($callback, $table);

        return static::execute($table);
    }

    /**
     * Buat skema tabel baru jika tabel belum ada.
     *
     * @param string   $table
     * @param \Closure $callback
     */
    public static function createIfNotExists($table, Closure $callback)
    {
        if (!static::hasTable($table)) {
            static::create($table, $callback);
        }
    }

    /**
     * Rename tabel didalam skema.
     *
     * @param string $table
     * @param string $newName
     */
    public static function rename($table, $newName)
    {
        $table = new Schema\Table($table);
        $table->rename($newName);

        return static::execute($table);
    }

    /**
     * Drop tabel dari skema.
     *
     * @param string $table
     * @param string $connection
     */
    public static function drop($table, $connection = null)
    {
        $table = new Schema\Table($table);
        $table->on($connection);
        $table->drop();

        static::execute($table);
    }

    /**
     * Drop tabel dari skema (cek dulu apakah tabelnya ada di skema).
     *
     * @param string $table
     * @param string $connection
     */
    public static function dropIfExists($table, $connection = null)
    {
        if (static::hasTable($table, $connection)) {
            static::drop($table, $connection);
        }
    }

    /**
     * Eksekusi operasi skema.
     *
     * @param \System\Database\Schema\Table $table
     */
    public static function execute($table)
    {
        static::implications($table);

        foreach ($table->commands as $command) {
            $connection = Database::connection($table->connection);
            $grammar = static::grammar($connection);

            if (method_exists($grammar, $method = $command->type)) {
                $statements = $grammar->$method($table, $command);
                $statements = (array) $statements;

                foreach ($statements as $statement) {
                    $connection->query($statement);
                }
            }
        }
    }

    /**
     * Tambahkan perintah implisit apapun ke operasi skema.
     *
     * @param \System\Database\Schema\Table $table
     */
    protected static function implications($table)
    {
        if (count($table->columns) > 0 && !$table->creating()) {
            $command = new Magic(['type' => 'add']);
            array_unshift($table->commands, $command);
        }

        $keys = ['primary', 'unique', 'fulltext', 'index'];

        foreach ($table->columns as $column) {
            foreach ($keys as $key) {
                if (isset($column->$key)) {
                    if ($column->$key === true) {
                        $table->$key($column->name);
                    } else {
                        $table->$key($column->name, $column->$key);
                    }
                }
            }
        }
    }

    /**
     * Tentukan grammar skema yang sesuai untuk driver saat ini.
     *
     * @param \System\Database\Connection $connection
     *
     * @return object
     */
    public static function grammar(Connection $connection)
    {
        $driver = $connection->driver();

        if (isset(Database::$registrar[$driver])) {
            return Database::$registrar[$driver]['schema']();
        }

        switch ($driver) {
            case 'mysql':  return new Schema\Grammars\MySQL($connection);
            case 'pgsql':  return new Schema\Grammars\Postgres($connection);
            case 'sqlsrv': return new Schema\Grammars\SQLServer($connection);
            case 'sqlite': return new Schema\Grammars\SQLite($connection);
        }

        throw new Exception("Schema operations not supported for [$driver] driver.");
    }

    /**
     * Cek apakah tabel ada di database.
     *
     * @param string $table
     *
     * @return bool
     */
    public static function hasTable($table, $connection = null)
    {
        $driver = Database::connection()->driver();

        if (filled($connection) && is_string($connection)) {
            $driver = $connection;
        }

        $db = Config::get("database.connections.{$driver}.database");
        $db = Database::escape($db);

        $table = Database::escape($table);

        $query = '';

        switch ($driver) {
            case 'mysql':
                $query = 'select * from information_schema.tables '.
                    "where table_schema = {$db} and table_name = {$table}";
                break;

            case 'pgsql':
                $query = "select * from information_schema.tables where table_name = {$table}";
                break;

            case 'sqlite':
                $query = "select * from sqlite_master where type = 'table' and name = {$table}";
                break;

            case 'sqlsrv':
                $query = "select * from sysobjects where type = 'U' and name = {$table}";
                break;

            default:
                throw new Exception("Schema operations not supported for [$driver] driver.");
                break;
        }

        return null !== Database::first($query);
    }

    /**
     * Cek apakah kolom ada di suatu tabel.
     *
     * @param string $table
     * @param string $column
     *
     * @return bool
     */
    public static function hasColumn($table, $column)
    {
        $driver = Database::connection()->driver();

        $db = Config::get("database.connections.{$driver}.database");
        $db = Database::escape($db);

        $table = Database::escape($table);

        $query = '';

        switch ($driver) {
            case 'mysql':
                $query = 'select column_name from information_schema.columns '.
                    "where table_schema = {$db} and column_name = {$column}";
                break;

            case 'pgsql':
                $query = 'select column_name from information_schema.columns '.
                    "where table_name = {$table} and column_name = {$column}";
                break;

            case 'sqlite':
                // Terpaksa langsung return, belum nemu cara yang lebih sederhana :(
                try {
                    $query = 'pragma table_info('.str_replace('.', '__', $table).')';
                    $stmt = Database::connection()->pdo->prepare($query);
                    $stmt->execute();
                    // Listing semua kolom di dalam tabel
                    $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    $columns = array_values(array_map(function ($col) {
                        return isset($col['name']) ? $col['name'] : [];
                    }, $columns));

                    return in_array($column, $columns);
                } catch (PDOException $e) {
                    // Return false saja, sembunyikan exception.
                    return false;
                }
                break;

            case 'sqlsrv':
                $query = 'select col.name from sys.columns as col '.
                    'join sys.objects as obj on col.object_id = obj.object_id '.
                    "where obj.type = 'U' and obj.name = {$table} and col.name = {$column}";
                break;

            default:
                throw new Exception("Schema operations not supported for [$driver] driver.");
                break;
        }

        return null !== Database::first($query);
    }

    /**
     * Hidupkan foreign key constraint checking.
     *
     * @param string $table
     *
     * @return bool
     */
    public static function enableForeignKeyChecks($table)
    {
        $table = Database::escape($table);
        $driver = Database::connection()->driver();

        switch ($driver) {
            case 'mysql':  $query = 'SET FOREIGN_KEY_CHECKS=1;'; break;
            case 'pqsql':  $query = 'SET CONSTRAINTS ALL IMMEDIATE;'; break;
            case 'sqlite': $query = 'PRAGMA foreign_keys = ON;'; break;
            case 'sqlsrv':
                $query = 'EXEC sp_msforeachtable @command1="print \'{$table}\'", '.
                    '@command2="ALTER TABLE {$table} WITH CHECK CHECK CONSTRAINT all";';
                break;
            default: throw new Exception("Schema operations not supported for [$driver] driver.");
        }

        try {
            return false !== Database::connection()->pdo->exec($query);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Matikan foreign key constraint checking.
     *
     * @param string $table
     *
     * @return bool
     */
    public static function disableForeignKeyChecks($table)
    {
        $table = Database::escape($table);
        $driver = Database::connection()->driver();

        switch ($driver) {
            case 'mysql':  $query = 'SET FOREIGN_KEY_CHECKS=0;'; break;
            case 'pqsql':  $query = 'SET CONSTRAINTS ALL DEFERRED;'; break;
            case 'sqlite': $query = 'PRAGMA foreign_keys = OFF;'; break;
            case 'sqlsrv':
                $query = 'EXEC sp_msforeachtable "ALTER TABLE {$table} NOCHECK CONSTRAINT all";';
                break;
            default: throw new Exception("Schema operations not supported for [$driver] driver.");
        }

        try {
            return false !== Database::connection()->pdo->exec($query);
        } catch (PDOException $e) {
            return false;
        }
    }
}
