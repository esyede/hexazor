<?php

namespace System\Database;

defined('DS') or exit('No direct script access allowed.');

class Expression
{
    protected $value;

    /**
     * Buat instance database expression baru.
     *
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Ambil nilai string dari database expression.
     *
     * @return string
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Ambil nilai string dari database expression.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }
}
