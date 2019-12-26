<?php

namespace System\Database;

defined('DS') or exit('No direct script access allowed.');

abstract class Grammar
{
    protected $wrapper = '"%s"';

    protected $connection;

    /**
     * Buat instance database grammar baru.
     *
     * @param \System\Database\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Wrap tabel di dalam keyword identifier.
     *
     * @param string $table
     *
     * @return string
     */
    public function wrapTable($table)
    {
        if ($table instanceof Expression) {
            return $this->wrap($table);
        }

        $prefix = '';

        if (isset($this->connection->config['prefix'])) {
            $prefix = $this->connection->config['prefix'];
        }

        return $this->wrap($prefix.$table);
    }

    /**
     * Wrap value di dalam keyword identifier.
     *
     * @param string $value
     *
     * @return string
     */
    public function wrap($value)
    {
        if ($value instanceof Expression) {
            return $value->get();
        }

        if (false !== strpos(strtolower($value), ' as ')) {
            $segments = explode(' ', $value);

            return sprintf('%s AS %s', $this->wrap($segments[0]), $this->wrap($segments[2]));
        }

        $segments = explode('.', $value);

        foreach ($segments as $key => $value) {
            if (0 == $key && count($segments) > 1) {
                $wrapped[] = $this->wrapTable($value);
            } else {
                $wrapped[] = $this->wrapValue($value);
            }
        }

        return implode('.', $wrapped);
    }

    /**
     * Wrap string value tunggal di dalam keyword identifier.
     *
     * @param string $value
     *
     * @return string
     */
    protected function wrapValue($value)
    {
        return ('*' !== $value) ? sprintf($this->wrapper, $value) : $value;
    }

    /**
     * Buat parameter query dari array value (bindParam ?, ?, ...).
     *
     * @param array $values
     *
     * @return string
     */
    final public function parameterize($values)
    {
        return implode(', ', array_map([$this, 'parameter'], $values));
    }

    /**
     * Ambil string parameter query yang sesuai untuk suatu nilai.
     *
     * @param mixed $value
     *
     * @return string
     */
    final public function parameter($value)
    {
        return ($value instanceof Expression) ? $value->get() : '?';
    }

    /**
     * Buat daftar nama kolom yang telah di-wrap, dan dibatasi dengan koma.
     *
     * @param array $columns
     *
     * @return string
     */
    final public function columnize($columns)
    {
        return implode(', ', array_map([$this, 'wrap'], $columns));
    }
}
