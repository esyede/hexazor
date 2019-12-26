<?php

namespace System\Database\Query\Grammars;

defined('DS') or exit('No direct script access allowed.');

class MySQL extends Grammar
{
    protected $wrapper = '`%s`';
}
