<?php

namespace System\Database;

defined('DS') or exit('No direct script access allowed.');

use Exception;

class DBException extends Exception
{
    protected $inner;

    /**
     * Buat instance DBException baru.
     *
     * @param string     $sql
     * @param array      $bindings
     * @param \Exception $inner
     */
    public function __construct($sql, $bindings, Exception $inner)
    {
        $this->inner = $inner;
        $this->setMessage($sql, $bindings);
        $this->code = $inner->getCode();
    }

    /**
     * Ambil data dari kelas \Exception.
     *
     * @return \Exception
     */
    public function getInner()
    {
        return $this->inner;
    }

    /**
     * Set exception message untuk meng-include perintah sql dan bindingnya.
     *
     * @param string $sql
     * @param array  $bindings
     */
    protected function setMessage($sql, $bindings)
    {
        $this->message = $this->inner->getMessage();
        $bindings = var_export($bindings, true);

        if (is_array($bindings)) {
            $bindings = implode(', ', $bindings);
        }

        $this->message .= PHP_EOL.PHP_EOL.'SQL: '.$sql.' '.PHP_EOL.'Bindings: '.$bindings;
    }
}
