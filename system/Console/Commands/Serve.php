<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Console\Console;
use System\Core\Application;

class Serve extends Command
{
    protected $signature = 'serve {--port=8000}';
    protected $description = 'Run the hexazor development server';

    public function handle()
    {
        $port = (int) $this->getOption('port');
        $port = (is_numeric($port) && $port >= 20 && $port <= 65535) ? $port : 8000;
        $command = escapeshellcmd('php -S localhost:'.$port.' -t .');

        $this->writeline("Hexazor development server started: <http://localhost:{$port}>");
        $this->writeline('Press Ctrl-C to quit.');

        passthru($command);
    }
}
