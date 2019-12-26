<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;

class Serve extends Command
{
    protected $signature = 'serve {port?}';
    protected $description = 'Run the hexazor development server.';

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle($port = 8000)
    {
        $port = isset($port) ? $port : 8000;
        $port = (is_numeric($port) && $port >= 20 && $port <= 65535) ? $port : 8000;
        $command = escapeshellcmd('php -S localhost:'.$port.' -t .');

        $this->success("Hexazor development server started: <http://localhost:{$port}>");
        $this->success('Press Ctrl-C to quit.');
        $this->newline();

        passthru($command);
    }
}
