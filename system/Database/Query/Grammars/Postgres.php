<?php

namespace System\Database\Query\Grammars;

defined('DS') or exit('No direct script access allowed.');

use System\Database\Query;

class Postgres extends Grammar
{
    public function insertGetId(Query $query, $values, $column)
    {
        return $this->insert($query, $values)." RETURNING $column";
    }
}
