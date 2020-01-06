<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Core\Application;

class Search extends Command
{
    protected $signature = 'search {keyword?}';
    protected $description = 'Search for command';

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle($keyword)
    {
        $this->success('Welcome to Hexazor Console v'.Application::VERSION);
        $this->newline();

        if ($keyword) {
            $commands = $this->getCommandsLike($keyword);
            $this->info("This is similar commands found for '$keyword': ");
        } else {
            $commands = $this->getRegisteredCommands();
            $this->info('Available commands:');
        }

        $count = 0;
        $maxLen = 0;
        $names = array_keys($commands);
        foreach ($names as $name) {
            $currLen = strlen($name);
            if ($currLen > $maxLen) {
                $maxLen = $currLen;
            }
        }

        $pad = $maxLen + 3;

        if (blank($commands)) {
            $this->error('Hmm, No suitable commands.');
            $this->quit();
        } else {
            ksort($commands);

            foreach ($commands as $name => $command) {
                $no = ++$count.') ';
                $this->write(str_repeat(' ', 4 - strlen($no)).$no);
                $this->write($name.str_repeat(' ', $pad - strlen($name)), 'green');
                $this->write($command['description'], 'white');
                $this->newline();
            }
        }

        $this->newline();
        $this->plain("Type 'php ".$this->getFilename()." <command> --help' for help");
        $this->newline();
    }
}
