<?php

namespace System\Debugger\Interfaces;

defined('DS') or exit('No direct script access allowed.');

interface LoggerInterface
{
    const DEBUG = 'debug';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';
    const EXCEPTION = 'exception';
    const CRITICAL = 'critical';

    public function log($value, $level = self::INFO);
}
